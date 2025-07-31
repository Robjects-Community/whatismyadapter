<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductsTranslation Entity
 *
 * @property string $id
 * @property string $locale
 * @property string|null $title
 * @property string|null $lede
 * @property string|null $body
 * @property string|null $summary
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $facebook_description
 * @property string|null $linkedin_description
 * @property string|null $instagram_description
 * @property string|null $twitter_description
 *
 * @property \App\Model\Entity\Product $product
 */
class ProductsTranslation extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'locale' => true,
        'title' => true,
        'lede' => true,
        'body' => true,
        'summary' => true,
        'meta_title' => true,
        'meta_description' => true,
        'meta_keywords' => true,
        'facebook_description' => true,
        'linkedin_description' => true,
        'instagram_description' => true,
        'twitter_description' => true,
        'product' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [];

    /**
     * Virtual fields for calculated properties
     *
     * @var array<string>
     */
    protected array $_virtual = ['display_locale'];

    /**
     * Get human-readable locale display name
     *
     * @return string
     */
    protected function _getDisplayLocale(): string
    {
        $localeMap = [
            'en_GB' => 'English (UK)',
            'fr_FR' => 'Français (France)',
            'de_DE' => 'Deutsch (Deutschland)',
            'es_ES' => 'Español (España)',
            'it_IT' => 'Italiano (Italia)',
            'pt_PT' => 'Português (Portugal)',
            'nl_NL' => 'Nederlands (Nederland)',
            'pl_PL' => 'Polski (Polska)',
            'ru_RU' => 'Русский (Россия)',
            'sv_SE' => 'Svenska (Sverige)',
            'da_DK' => 'Dansk (Danmark)',
            'fi_FI' => 'Suomi (Suomi)',
            'no_NO' => 'Norsk (Norge)',
            'el_GR' => 'Ελληνικά (Ελλάδα)',
            'tr_TR' => 'Türkçe (Türkiye)',
            'cs_CZ' => 'Čeština (Česko)',
            'hu_HU' => 'Magyar (Magyarország)',
            'ro_RO' => 'Română (România)',
            'sk_SK' => 'Slovenčina (Slovensko)',
            'sl_SI' => 'Slovenščina (Slovenija)',
            'bg_BG' => 'Български (България)',
            'hr_HR' => 'Hrvatski (Hrvatska)',
            'et_EE' => 'Eesti (Eesti)',
            'lv_LV' => 'Latviešu (Latvija)',
            'lt_LT' => 'Lietuvių (Lietuva)',
            'uk_UA' => 'Українська (Україна)',
        ];

        return $localeMap[$this->locale] ?? $this->locale;
    }

    /**
     * Check if translation has complete content
     *
     * @return bool
     */
    public function isComplete(): bool
    {
        return !empty($this->title) && !empty($this->body);
    }

    /**
     * Check if translation has SEO metadata
     *
     * @return bool
     */
    public function hasSeoMetadata(): bool
    {
        return !empty($this->meta_title) && !empty($this->meta_description);
    }

    /**
     * Get social media descriptions as array
     *
     * @return array
     */
    public function getSocialDescriptions(): array
    {
        return [
            'facebook' => $this->facebook_description,
            'linkedin' => $this->linkedin_description,
            'instagram' => $this->instagram_description,
            'twitter' => $this->twitter_description,
        ];
    }
}
