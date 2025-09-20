<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Core\Configure;
use Cake\I18n\I18n;

/**
 * Class I18nManager
 *
 * This utility class provides internationalization helper functions.
 * It uses the SettingsManager class to retrieve language settings.
 */
class I18nManager
{
    /**
     * @var array $locales
     *
     * An associative array mapping language codes to their respective locale identifiers.
     */
    public static array $locales = [
        'de' => 'de_DE', // German (Germany)
        'fr' => 'fr_FR', // French (France)
        'es' => 'es_ES', // Spanish (Spain)
        'it' => 'it_IT', // Italian (Italy)
        'pt' => 'pt_PT', // Portuguese (Portugal)
        'nl' => 'nl_NL', // Dutch (Netherlands)
        'pl' => 'pl_PL', // Polish (Poland)
        'ru' => 'ru_RU', // Russian (Russia)
        'sv' => 'sv_SE', // Swedish (Sweden)
        'da' => 'da_DK', // Danish (Denmark)
        'fi' => 'fi_FI', // Finnish (Finland)
        'no' => 'no_NO', // Norwegian (Norway)
        'el' => 'el_GR', // Greek (Greece)
        'tr' => 'tr_TR', // Turkish (Turkey)
        'cs' => 'cs_CZ', // Czech (Czech Republic)
        'hu' => 'hu_HU', // Hungarian (Hungary)
        'ro' => 'ro_RO', // Romanian (Romania)
        'sk' => 'sk_SK', // Slovak (Slovakia)
        'sl' => 'sl_SI', // Slovenian (Slovenia)
        'bg' => 'bg_BG', // Bulgarian (Bulgaria)
        'hr' => 'hr_HR', // Croatian (Croatia)
        'et' => 'et_EE', // Estonian (Estonia)
        'lv' => 'lv_LV', // Latvian (Latvia)
        'lt' => 'lt_LT', // Lithuanian (Lithuania)
        'uk' => 'uk_UA', // Ukrainian (Ukraine)
    ];

    /**
     * @var array $languages
     *
     * An associative array mapping language codes to their respective language names.
     */
    public static array $languages = [
        'de' => 'German',
        'fr' => 'French',
        'es' => 'Spanish',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'nl' => 'Dutch',
        'pl' => 'Polish',
        'ru' => 'Russian',
        'sv' => 'Swedish',
        'da' => 'Danish',
        'fi' => 'Finnish',
        'no' => 'Norwegian',
        'el' => 'Greek',
        'tr' => 'Turkish',
        'cs' => 'Czech',
        'hu' => 'Hungarian',
        'ro' => 'Romanian',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'bg' => 'Bulgarian',
        'hr' => 'Croatian',
        'et' => 'Estonian',
        'lv' => 'Latvian',
        'lt' => 'Lithuanian',
        'uk' => 'Ukrainian',
    ];

    /**
     * Set the enabled languages in the application configuration.
     *
     * This method checks if the 'settings' table exists in the database to ensure
     * that the necessary tables have been loaded, which is crucial during the early
     * stages of a fresh installation when the database might not yet be fully set up.
     * If the table exists, it retrieves the enabled locales using the `getEnabledLocales()`
     * method, extracts the language codes, merges them with the default languages,
     * and sets the merged languages in the application configuration. If the table
     * does not exist, it defaults to setting English ('en') as the only enabled language.
     *
     * This check is necessary because `I18nManager::setEnabledLanguages()` is called
     * in the bootstrap of Application.php, and at this point, the database may not
     * have had the default tables loaded.
     *
     * @return void
     */
    public static function setEnabledLanguages(): void
    {
        if (DatabaseUtility::tableExists('settings')) {
            $defaultLanguages = ['en'];
            $enabledLanguages = array_keys(self::getEnabledLocales());
            $mergedLanguages = array_merge($defaultLanguages, $enabledLanguages);
            Configure::write('I18n.languages', $mergedLanguages);
        } else {
            Configure::write('I18n.languages', ['en']);
            I18n::setLocale('en_GB');
        }
    }

    /**
     * Set the locale based on the provided language code.
     *
     * This method retrieves the enabled locales using the `getEnabledLocales()`
     * method, finds the matching locale for the provided language code, and sets
     * the locale using `I18n::setLocale()`. If no matching locale is found, it
     * sets the locale to 'en_GB'.
     *
     * @param string $language The language code to set the locale for.
     * @return void
     */
    public static function setLocaleForLanguage(string $language): void
    {
        $locales = self::getEnabledLocales();

        $matchedLocale = null;
        foreach ($locales as $lang => $locale) {
            if ($language == $lang) {
                $matchedLocale = $locale;
                break;
            }
        }

        if (!empty($matchedLocale)) {
            I18n::setLocale($matchedLocale);
        } else {
            I18n::setLocale('en_GB');
        }
    }

    /**
     * Set the locale for the admin area based on the settings.
     *
     * This method retrieves the admin locale from the settings using the `SettingsManager`
     * class. If an admin locale is found, it sets the locale using `I18n::setLocale()`.
     *
     * @return void
     */
    public static function setLocalForAdminArea(): void
    {
        $adminLocale = SettingsManager::read('i18n.locale', null);
        if (!empty($adminLocale)) {
            I18n::setLocale($adminLocale);
        }
    }

    /**
     * Get the enabled locales from the settings.
     *
     * This method retrieves the translation settings using the `SettingsManager`
     * class, filters the enabled locales, and returns an array where the keys are
     * the language codes and the values are the corresponding locales.
     *
     * @return array An array of enabled locales, with language codes as keys and locales as values.
     */
    public static function getEnabledLocales(): array
    {
        $locales = SettingsManager::read('Translations', []);
        $enabledLocales = [];
        foreach ($locales as $locale => $enabled) {
            if ($enabled) {
                $enabledLocales[substr($locale, 0, 2)] = $locale;
            }
        }

        return $enabledLocales;
    }

    /**
     * Get the enabled languages based on the enabled locales.
     *
     * This method uses the enabled locales to determine which languages are enabled
     * and returns an array of these languages.
     *
     * @return array An array of enabled languages.
     */
    public static function getEnabledLanguages(): array
    {
        $locales = self::getEnabledLocales();
        $languages = array_intersect_key(self::$languages, $locales);

        return $languages;
    }
}
