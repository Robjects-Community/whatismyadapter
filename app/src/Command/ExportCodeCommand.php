<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use DirectoryIterator;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * ExportCodeCommand
 *
 * This command exports custom code (app and plugins) with relative paths to a text file or separate files.
 * This is useful if you want to have AI work with the source code.
 */
class ExportCodeCommand extends Command
{
    private const OUTPUT_FILENAME_BASE = 'willow_cms_code';
    // Updated default extensions to include ctp for views by default easily
    private const DEFAULT_EXTENSIONS = 'php,css,js,ctp';
    // Added Webroot to default directories
    private const DEFAULT_DIRECTORIES = 'Models,Controllers,Commands,Components,Views,Webroot';

    /**
     * Defines the standard mappings for application and plugin code directories.
     * 'app' path is relative to ROOT.
     * 'plugin' path is relative to the ROOT of a specific plugin (e.g., plugins/MyPlugin/).
     *
     * @return array<string, array<string, string>>
     */
    protected function getDirectoryTypeMappings(): array
    {
        return [
            'Models' => ['app' => 'src' . DS . 'Model', 'plugin' => 'src' . DS . 'Model'],
            'Views' => ['app' => 'templates', 'plugin' => 'templates'], // For .ctp or .php view files
            'Controllers' => ['app' => 'src' . DS . 'Controller', 'plugin' => 'src' . DS . 'Controller'],
            'Components' => ['app' => 'src' . DS . 'Controller' . DS . 'Component',
            'plugin' => 'src' . DS . 'Controller' . DS . 'Component'],
            'Commands' => ['app' => 'src' . DS . 'Command', 'plugin' => 'src' . DS . 'Command'],
            'Jobs' => ['app' => 'src' . DS . 'Job', 'plugin' => 'src' . DS . 'Job'],
            'Services' => ['app' => 'src' . DS . 'Service', 'plugin' => 'src' . DS . 'Service'],
            'Utilities' => ['app' => 'src' . DS . 'Utility', 'plugin' => 'src' . DS . 'Utility'],
            'Logs' => ['app' => 'src' . DS . 'Log', 'plugin' => 'src' . DS . 'Log'],
            'Tests' => ['app' => 'tests', 'plugin' => 'tests'],
            'Webroot' => ['app' => 'webroot', 'plugin' => 'webroot'], // For JS, CSS etc. in webroot
            // Add other types like 'Config', etc. if needed\
            'Config' => ['app' => 'config', 'plugin' => 'config'],
            'Assets' => ['app' => 'assets', 'plugin' => 'assets'],
            'Migrations' => ['app' => 'config' . DS . 'Migrations', 'plugin' => 'config' . DS . 'Migrations'],
            'Templates' => ['app' => 'templates', 'plugin' => 'templates'],
            //AdminTheme is a custom directory type for themes
            // Adjust the paths as needed for your application structure

            'AdminTheme' => ['app' => 'templates' . DS . 'AdminTheme', 'plugin' => 'templates' . DS . 'AdminTheme'],
        ];
    }

    /**
     * Build the option parser for the command.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser to be modified.
     * @return \Cake\Console\ConsoleOptionParser The modified option parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $dirMappings = $this->getDirectoryTypeMappings();
        $availableDirKeys = implode(', ', array_keys($dirMappings));

        $parser
            ->setDescription('Exports custom app and plugin code with relative paths.')
            ->addOption('separate', [
                'help' => 'Create a separate file for each specified directory type.',
                'boolean' => true,
            ])
            ->addOption('directories', [
                'help' => 'Comma-separated list of directory types to export. Available: '
                    . $availableDirKeys . '. Defaults to: ' . self::DEFAULT_DIRECTORIES,
                'short' => 'd',
                'default' => self::DEFAULT_DIRECTORIES,
            ])
            ->addOption('extensions', [
                'help' => 'Comma-separated list of file extensions to include (e.g., php,ctp,js,css). Defaults to: '
                    . self::DEFAULT_EXTENSIONS,
                'short' => 'e',
                'default' => self::DEFAULT_EXTENSIONS,
            ]);

        return $parser;
    }

    /**
     * Execute the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io.
     * @return int The exit code.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $rootDir = ROOT;
        $outputBaseFilename = $rootDir . DS . self::OUTPUT_FILENAME_BASE;

        $directoryTypeMappings = $this->getDirectoryTypeMappings();

        $selectedDirKeysInput = $args->getOption('directories');
        $selectedDirKeys = $selectedDirKeysInput ? array_map(
            'trim',
            explode(',', $selectedDirKeysInput),
        ) : array_keys($directoryTypeMappings);

        $allowedExtensionsInput = $args->getOption('extensions');
        $allowedExtensions = array_map('trim', explode(',', strtolower($allowedExtensionsInput)));

        $separateFiles = (bool)$args->getOption('separate');
        $mainHandle = null;

        if (!$separateFiles) {
            $mainOutputFile = $outputBaseFilename . '.txt';
            $mainHandle = fopen($mainOutputFile, 'w');
            if (!$mainHandle) {
                $io->error(sprintf('Failed to open main output file for writing: %s', $mainOutputFile));

                return static::CODE_ERROR;
            }
            $this->writeMetadata($mainHandle);
        }

        foreach ($selectedDirKeys as $dirKey) {
            if (!isset($directoryTypeMappings[$dirKey])) {
                $io->warning(sprintf("Unknown directory type '%s' skipped.", $dirKey));
                continue;
            }

            $mapping = $directoryTypeMappings[$dirKey];
            $pathsToScan = [];

            // 1. Add application path
            $appPath = $rootDir . DS . $mapping['app'];
            if (is_dir($appPath)) {
                $pathsToScan[] = $appPath;
            } else {
                $io->verbose(sprintf("Application path for '%s' not found: %s", $dirKey, $appPath));
            }

            // 2. Add plugin paths
            $pluginsRootDir = $rootDir . DS . 'plugins';
            if (is_dir($pluginsRootDir) && !empty($mapping['plugin'])) { // Ensure plugin mapping exists
                try {
                    $pluginIterator = new DirectoryIterator($pluginsRootDir);
                    foreach ($pluginIterator as $pluginDirInfo) {
                        if ($pluginDirInfo->isDir() && !$pluginDirInfo->isDot()) {
                            $pluginName = $pluginDirInfo->getFilename();
                            $pluginSpecificPath = $pluginsRootDir . DS . $pluginName . DS . $mapping['plugin'];
                            if (is_dir($pluginSpecificPath)) {
                                $pathsToScan[] = $pluginSpecificPath;
                            } else {
                                 $io->verbose(sprintf(
                                     "Plugin path for '%s' in plugin '%s' not found: %s",
                                     $dirKey,
                                     $pluginName,
                                     $pluginSpecificPath,
                                 ));
                            }
                        }
                    }
                } catch (Exception $e) {
                    $io->warning(sprintf(
                        "Could not iterate plugins directory '%s': %s",
                        $pluginsRootDir,
                        $e->getMessage(),
                    ));
                }
            }

            if (empty($pathsToScan)) {
                $io->info(sprintf("No valid source directories found for type '%s'. Skipping.", $dirKey));
                continue;
            }

            $currentHandle = $mainHandle;
            if ($separateFiles) {
                $separateOutputFile = $outputBaseFilename . '_' . str_replace(DS, '_', $dirKey) . '.txt';
                $currentHandle = fopen($separateOutputFile, 'w');
                if (!$currentHandle) {
                    $io->error(sprintf('Failed to open separate output file for writing: %s', $separateOutputFile));
                    continue;
                }
                $this->writeMetadata($currentHandle);
            }

            if (!$currentHandle) {
                $io->error("No valid file handle for writing. This shouldn't happen.");

                return static::CODE_ERROR;
            }

            $this->writeSectionHeader($currentHandle, $dirKey);
            $filesExportedForThisKey = 0;

            foreach ($pathsToScan as $scanPath) {
                $io->verbose(sprintf("Scanning directory: %s for type '%s'", str_replace($rootDir .
                DS, '', $scanPath), $dirKey));
                try {
                    $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($scanPath, RecursiveDirectoryIterator::SKIP_DOTS |
                        RecursiveDirectoryIterator::FOLLOW_SYMLINKS),
                        RecursiveIteratorIterator::SELF_FIRST,
                    );

                    foreach ($iterator as $file) {
                        /** @var \SplFileInfo $file */
                        if ($file->isFile()) {
                            $fileExtension = strtolower($file->getExtension());
                            if (!in_array($fileExtension, $allowedExtensions, true)) {
                                continue;
                            }

                           // Skip minified JS/CSS files (check basename without extension)
                            if (
                                in_array($fileExtension, ['js', 'css'], true) && preg_match(
                                    '/\.min$/i',
                                    $file->getBasename('.' . $fileExtension),
                                )
                            ) {
                                $io->verbose(sprintf('Skipping minified file: %s', $file->getPathname()));
                                continue;
                            }

                           // Skip vendor directories within webroot (e.g., webroot/vendor/some_lib)
                           // or generally any path containing '/vendor/' if not desired
                            if (strpos($file->getPathname(), DS . 'vendor' . DS) !== false) {
                                $io->verbose(sprintf('Skipping vendor file: %s', $file->getPathname()));
                                continue;
                            }

                            $this->writeFileContent($currentHandle, $file, $rootDir);
                            $filesExportedForThisKey++;
                        }
                    }
                } catch (Exception $e) {
                    $io->warning(sprintf("Error scanning directory '%s': %s", $scanPath, $e->getMessage()));
                }
            }

            if ($filesExportedForThisKey === 0) {
                fwrite($currentHandle, "No files found matching criteria for this section.\n");
            }

            $this->writeSectionFooter($currentHandle, $dirKey);

            if ($separateFiles && $currentHandle) {
                fclose($currentHandle);
                $io->info(sprintf("Exported '%s' to separate file.", $dirKey));
            }
        }

        if ($mainHandle) {
            fclose($mainHandle);
            $io->success(sprintf('Code exported successfully to %s', $outputBaseFilename . '.txt'));
        } elseif ($separateFiles) {
            $io->success('Code exported successfully to separate files in the project root, prefixed with ' .
            self::OUTPUT_FILENAME_BASE . '_');
        } else {
            $io->warning('No code was exported. Check configurations or verbose output if no types were selected.');
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Write metadata to the output file.
     *
     * @param resource $handle The file handle to write to.
     * @return void
     */
    protected function writeMetadata($handle): void
    {
        if (!$handle) {
            return;
        }
        $metadata = [
            'Export Date' => date('Y-m-d H:i:s'),
            'CakePHP Version' => Configure::version(),
            'PHP Version' => phpversion(),
        ];

        fwrite($handle, "METADATA:\n");
        foreach ($metadata as $key => $value) {
            fwrite($handle, "{$key}: {$value}\n");
        }
        fwrite($handle, "\n" . str_repeat('=', 80) . "\n\n");
    }

    /**
     * Write a section header to the output file.
     *
     * @param resource $handle The file handle to write to.
     * @param string $sectionName The name of the section.
     * @return void
     */
    protected function writeSectionHeader($handle, string $sectionName): void
    {
        if (!$handle) {
            return;
        }
        fwrite($handle, "\n\n" . str_repeat('=', 80) . "\n");
        fwrite($handle, "BEGIN SECTION: {$sectionName}\n");
        fwrite($handle, str_repeat('=', 80) . "\n\n");
    }

    /**
     * Write a section footer to the output file.
     *
     * @param resource $handle The file handle to write to.
     * @param string $sectionName The name of the section.
     * @return void
     */
    protected function writeSectionFooter($handle, string $sectionName): void
    {
        if (!$handle) {
            return;
        }
        fwrite($handle, "\n\n" . str_repeat('=', 80) . "\n");
        fwrite($handle, "END SECTION: {$sectionName}\n");
        fwrite($handle, str_repeat('=', 80) . "\n\n");
    }

    /**
     * Write the content of a file to the output file.
     *
     * @param resource $handle The file handle to write to.
     * @param \SplFileInfo $file The file information.
     * @param string $rootDir The root directory path.
     * @return void
     */
    protected function writeFileContent($handle, SplFileInfo $file, string $rootDir): void
    {
        if (!$handle) {
            return;
        }
        $relativePath = str_replace($rootDir . DS, '', $file->getPathname());
        $content = file_get_contents($file->getPathname());
        if ($content === false) {
            $io = new ConsoleIo(); // Temporary IO for error, not ideal but better than nothing
            $io->warning(sprintf('Could not read content of file: %s', $file->getPathname()));
            $content = "[Error: Could not read file content for {$relativePath}]";
        }

        fwrite($handle, "FILE: {$relativePath}\n");
        fwrite($handle, 'LAST MODIFIED: ' . date('Y-m-d H:i:s', (int)$file->getMTime()) . "\n");
        fwrite($handle, 'SIZE: ' . $file->getSize() . " bytes\n");
        fwrite($handle, "CONTENT:\n");
        fwrite($handle, $content);
        fwrite($handle, "\n\n// ----- END FILE: {$relativePath} -----\n\n");
    }
}
