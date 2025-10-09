<?php
declare(strict_types=1);

namespace App\Command;

use App\Utility\I18nManager;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;
use Cake\Utility\Filesystem;

/**
 * Class GeneratePoFilesCommand
 *
 * This command generates default.po files for each locale based on the translations in the database.
 */
class GeneratePoFilesCommand extends Command
{
    /**
     * Executes the command to generate default.po files.
     *
     * @param \Cake\Console\Arguments $args The command line arguments.
     * @param \Cake\Console\ConsoleIo $io The console input/output.
     * @return int The exit code.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $locales = I18nManager::$locales;

        // Fetch the I18n table
        $i18nTable = TableRegistry::getTableLocator()->get('internationalisations');

        $filesystem = new Filesystem();

        foreach ($locales as $locale) {
            $translations = $i18nTable->find()
                ->where(['locale' => $locale, 'message_str !=' => ''])
                ->all();

            // Check if there are any translations for the locale
            if ($translations->isEmpty()) {
                $io->out(sprintf('No translations found for locale: %s. Skipping...', $locale));
                continue;
            }

            $poContent = $this->generatePoContent($locale, $translations);

            $filePath = ROOT . DS . 'resources' . DS . 'locales' . DS . $locale . DS . 'default.po';
            $dirPath = dirname($filePath);

            if (!is_dir($dirPath)) {
                $filesystem->mkdir($dirPath, 0755, true);
            }

            $filesystem->dumpFile($filePath, $poContent);
            $io->out(sprintf('Generated default.po for locale: %s', $locale));
        }

        return Command::CODE_SUCCESS;
    }

    /**
     * Generates the content for a .po file.
     *
     * @param string $locale The locale for which the .po file is generated.
     * @param \Cake\ORM\ResultSet $translations The translations to include in the .po file.
     * @return string The content of the .po file.
     */
    protected function generatePoContent(string $locale, ResultSet $translations): string
    {
        $header = <<<EOT
# LANGUAGE translation of CakePHP Application
# Copyright YEAR NAME <EMAIL@ADDRESS>
#
#, fuzzy
msgid ""
msgstr ""
"Project-Id-Version: PROJECT VERSION"
"POT-Creation-Date: 2024-10-23 23:47+0100"
"PO-Revision-Date: YYYY-mm-DD HH:MM+ZZZZ"
"Last-Translator: NAME <EMAIL@ADDRESS>"
"Language-Team: LANGUAGE <EMAIL@ADDRESS>"
"MIME-Version: 1.0"
"Content-Type: text/plain; charset=utf-8"
"Content-Transfer-Encoding: 8bit"
"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;"

EOT;

        $body = '';
        foreach ($translations as $translation) {
            $body .= "#: {$translation->context}\n";
            $body .= "msgid \"{$translation->message_id}\"\n";
            $body .= "msgstr \"{$translation->message_str}\"\n\n";
        }

        return $header . $body;
    }
}
