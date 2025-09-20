<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Class LoadDefault18nCommand
 *
 * This command processes teh default.pot file and updates the database with translation records per supported locale.
 */
class LoadDefault18nCommand extends Command
{
    /**
     * Executes the command to update internationalisation records.
     *
     * @param \Cake\Console\Arguments $args The command line arguments.
     * @param \Cake\Console\ConsoleIo $io The console input/output.
     * @return void
     */
    public function execute(Arguments $args, ConsoleIo $io): void
    {
        $potFile = 'resources/locales/default.pot';
        $translations = $this->parsePoFile($potFile);

        // Assuming you have an InternationalisationsTable to handle translation records
        $internationalisationsTable = TableRegistry::getTableLocator()->get('Internationalisations');

        // Define the locales you want to support
        $locales = [
            'de_DE', // German (Germany)
            'fr_FR', // French (France)
            'es_ES', // Spanish (Spain)
            'it_IT', // Italian (Italy)
            'pt_PT', // Portuguese (Portugal)
            'nl_NL', // Dutch (Netherlands)
            'pl_PL', // Polish (Poland)
            'ru_RU', // Russian (Russia)
            'sv_SE', // Swedish (Sweden)
            'da_DK', // Danish (Denmark)
            'fi_FI', // Finnish (Finland)
            'no_NO', // Norwegian (Norway)
            'el_GR', // Greek (Greece)
            'tr_TR', // Turkish (Turkey)
            'cs_CZ', // Czech (Czech Republic)
            'hu_HU', // Hungarian (Hungary)
            'ro_RO', // Romanian (Romania)
            'sk_SK', // Slovak (Slovakia)
            'sl_SI', // Slovenian (Slovenia)
            'bg_BG', // Bulgarian (Bulgaria)
            'hr_HR', // Croatian (Croatia)
            'et_EE', // Estonian (Estonia)
            'lv_LV', // Latvian (Latvia)
            'lt_LT', // Lithuanian (Lithuania)
            'uk_UA', // Ukrainian (Ukraine)
        ];

        foreach ($translations as $messageId => $messageStr) {
            foreach ($locales as $locale) {
                // Check if the message ID exists in the database for the given locale
                $internationalisation = $internationalisationsTable->find()
                    ->where(['message_id' => $messageId, 'locale' => $locale])
                    ->first();

                if (!$internationalisation) {
                    // If not, add it with an empty translation for each locale
                    $internationalisation = $internationalisationsTable->newEntity([
                        'message_id' => $messageId,
                        'locale' => $locale,
                        'message_str' => '',
                    ]);
                    $internationalisationsTable->save($internationalisation);
                }
            }
        }

        $io->success('Internationalisations updated successfully.');
    }

    /**
     * Parses a .po file to extract translations.
     *
     * @param string $file The path to the .po file.
     * @return array An associative array of message IDs and their corresponding translations.
     * @throws \Exception If the file does not exist.
     */
    private function parsePoFile(string $file): array
    {
        if (!file_exists($file)) {
            throw new Exception("The file {$file} does not exist.");
        }

        $translations = [];
        $currentMsgId = null;
        $currentMsgStr = '';

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, 'msgid "') === 0) {
                if ($currentMsgId !== null) {
                    $translations[$currentMsgId] = $currentMsgStr;
                    $currentMsgStr = '';
                }
                $currentMsgId = substr($line, 7, -1);
            } elseif (strpos($line, 'msgstr "') === 0) {
                $currentMsgStr = substr($line, 8, -1);
            } elseif ($currentMsgId !== null && strpos($line, '"') === 0) {
                if (strpos($line, 'msgid') === false && strpos($line, 'msgstr') === false) {
                    $currentMsgStr .= substr($line, 1, -1);
                }
            }
        }

        // Add the last translation
        if ($currentMsgId !== null) {
            $translations[$currentMsgId] = $currentMsgStr;
        }

        return $translations;
    }
}
