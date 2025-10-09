<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Database\Connection;
// Keep for potential direct connection use if needed elsewhere
use Cake\ORM\Exception\MissingTableClassException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Exception; // General exceptions

/**
 * DefaultDataImportCommand
 *
 * This command allows importing data from JSON files in the specified input directory
 * into database tables. It can import data for a specific table, all tables found
 * as .json files, or allow interactive selection.
 * For each table, existing data is deleted before new data is inserted, wrapped in a transaction.
 * Can optionally disable MySQL foreign key checks during import.
 */
class DefaultDataImportCommand extends Command
{
    /**
     * Configures the option parser for the command.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser to configure.
     * @return \Cake\Console\ConsoleOptionParser The configured option parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Imports default data from JSON files into specified tables (MySQL focused).')
            ->addArgument('table', [
                'help' => 'The specific table (PascalCase, e.g., Users) to 
                    import data for. The file name is inferred (e.g., users.json).',
                'required' => false,
            ])
            ->addOption('input', [
                'short' => 'i',
                'help' => 'Input directory containing the JSON files.',
                'default' => ROOT . DS . 'default_data',
            ])
            ->addOption('all', [
                'short' => 'a',
                'help' => 'Import data from all JSON files found in the input directory.',
                'boolean' => true,
            ])
            ->addOption('disable-fk-checks', [
                'help' => 'Temporarily disable MySQL foreign key checks during import.',
                'boolean' => true,
                'default' => false,
            ]);

        return $parser;
    }

    /**
     * Executes the command to import data.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io object.
     * @return int The exit code of the command.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $inputDir = (string)$args->getOption('input');
        $specificTableArg = $args->getArgument('table');
        $importAll = (bool)$args->getOption('all');
        $disableFkChecks = (bool)$args->getOption('disable-fk-checks');

        if (!is_dir($inputDir)) {
            if (!mkdir($inputDir, 0755, true) && !is_dir($inputDir)) {
                $io->error(sprintf('Input directory "%s" does not exist and could not be created.', $inputDir));

                return Command::CODE_ERROR;
            }
            $io->info(sprintf('Input directory "%s" created.', $inputDir));
        }

        $filesToProcess = [];

        if ($specificTableArg !== null) {
            $baseFileName = Inflector::underscore($specificTableArg);
            $filePath = $inputDir . DS . $baseFileName . '.json';

            if (!file_exists($filePath)) {
                $io->error(sprintf('No JSON file found for table "%s" (expected: %s).', $specificTableArg, $filePath));

                return Command::CODE_ERROR;
            }
            $filesToProcess[] = $filePath;
        } else {
            $jsonFilesInDir = glob($inputDir . DS . '*.json');
            if ($jsonFilesInDir === false || empty($jsonFilesInDir)) {
                $io->warning(sprintf('No JSON files found in the directory: %s', $inputDir));

                return Command::CODE_SUCCESS;
            }

            if ($importAll) {
                $filesToProcess = $jsonFilesInDir;
            } else {
                $io->out('Available data files:');
                foreach ($jsonFilesInDir as $index => $file) {
                    $io->out(sprintf('[%d] %s', $index + 1, basename($file)));
                }

                $choiceStr = $io->ask('Choose a file to import by number:');
                if (!ctype_digit($choiceStr)) {
                    $io->error('Invalid input. Please enter a number. Exiting.');

                    return Command::CODE_ERROR;
                }
                $choiceIndex = (int)$choiceStr - 1;

                if (!isset($jsonFilesInDir[$choiceIndex])) {
                    $io->error('Invalid choice. Exiting.');

                    return Command::CODE_ERROR;
                }
                $filesToProcess[] = $jsonFilesInDir[$choiceIndex];
            }
        }

        if (empty($filesToProcess)) {
            $io->info('No files selected or found for import.');

            return Command::CODE_SUCCESS;
        }

        $successfulImports = 0;
        $failedImports = 0;

        foreach ($filesToProcess as $filePath) {
            $baseFileName = basename($filePath, '.json');
            if ($this->importTableData($baseFileName, $inputDir, $io, $disableFkChecks)) {
                $successfulImports++;
            } else {
                $failedImports++;
            }
        }

        if ($successfulImports > 0) {
            $io->success(sprintf('Successfully imported data from %d file(s).', $successfulImports));
        }
        if ($failedImports > 0) {
            $io->error(sprintf('Failed to import data from %d file(s). Check messages above.', $failedImports));

            return Command::CODE_ERROR;
        }

        if ($successfulImports === 0 && $failedImports === 0) {
            $io->info('No data was imported.');
        }

        return Command::CODE_SUCCESS;
    }

    /**
     * Imports data from a JSON file into the specified table.
     *
     * @param string $baseFileName The base name of the JSON file (e.g., 'users', 'user_permissions').
     * @param string $inputDir The directory containing the JSON files.
     * @param \Cake\Console\ConsoleIo $io The console io object.
     * @param bool $disableFkChecks Whether to temporarily disable MySQL foreign key checks.
     * @return bool True on success, false on failure.
     */
    protected function importTableData(
        string $baseFileName,
        string $inputDir,
        ConsoleIo $io,
        bool $disableFkChecks = false,
    ): bool {
        $tableAlias = Inflector::camelize($baseFileName);
        $inputFile = $inputDir . DS . $baseFileName . '.json';

        $io->out(sprintf('Processing import for table "%s" from file "%s"...', $tableAlias, $baseFileName . '.json'));

        if (!file_exists($inputFile)) {
            $io->error(sprintf('Input file not found: %s', $inputFile));

            return false;
        }

        try {
            $table = TableRegistry::getTableLocator()->get($tableAlias);
        } catch (MissingTableClassException $e) {
            $io->error(
                sprintf(
                    'Table class for "%s" (derived from file "%s.json") could not be found. Error: %s',
                    $tableAlias,
                    $baseFileName,
                    $e->getMessage(),
                ),
            );

            return false;
        }

        $jsonContent = file_get_contents($inputFile);
        if ($jsonContent === false) {
            $io->error(sprintf('Could not read file content from: %s', $inputFile));

            return false;
        }

        $data = json_decode($jsonContent, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $io->error(sprintf('Failed to decode JSON from file: %s. Error: %s', $inputFile, json_last_error_msg()));

            return false;
        }

        if (empty($data) && $jsonContent !== '[]' && $jsonContent !== '{}') {
            $io->warning(
                sprintf(
                    'No data to import from %s, or JSON content was not a valid array of records.',
                    $inputFile,
                ),
            );

            return true;
        }
        if (empty($data) && ($jsonContent === '[]' || $jsonContent === '{}')) {
            $io->info(
                sprintf(
                    'File %s contains an empty JSON array/object. No records to import for table %s.',
                    $inputFile,
                    $tableAlias,
                ),
            );

            return true;
        }

        $connection = $table->getConnection();

        try {
            if ($disableFkChecks) {
                $this->toggleMySqlForeignKeyChecks($connection, false, $io);
            }

            $connection->begin();
            $io->info(sprintf('Attempting to delete existing records from table: %s', $tableAlias));
            $deleteResult = $table->deleteAll([]);
            if ($deleteResult === false) {
                throw new Exception(
                    sprintf(
                        'Failed to delete existing records from table: %s. An error occurred.',
                        $tableAlias,
                    ),
                );
            }
            $io->out(sprintf('Deleted %d existing record(s) from table: %s', $deleteResult, $tableAlias));

            $importedCount = 0;
            foreach ($data as $rowIndex => $row) {
                $entity = $table->newEntity($row);

                if ($tableAlias === 'Settings' && isset($entity->value_type) && property_exists($entity, 'value')) {
                    switch ($entity->value_type) {
                        case 'text':
                        case 'string':
                            $entity->value = (string)$entity->value;
                            break;
                        case 'numeric':
                        case 'integer':
                            $entity->value = (int)$entity->value;
                            break;
                        case 'bool':
                        case 'boolean':
                            if (is_string($entity->value) && strtolower($entity->value) === 'false') {
                                $entity->value = false;
                            } elseif (is_string($entity->value) && $entity->value === '0') {
                                $entity->value = false;
                            } else {
                                $entity->value = (bool)$entity->value;
                            }
                            break;
                        case 'float':
                            $entity->value = (float)$entity->value;
                            break;
                    }
                }

                // Optionally skip ORM-level rule checks if data is known to be valid
                if (!$table->save($entity, ['checkRules' => false, 'checkExisting' => false])) {
                    throw new Exception(sprintf(
                        'Failed to save entity for table %s (row %d). Errors: %s',
                        $tableAlias,
                        $rowIndex,
                        json_encode($entity->getErrors()),
                    ));
                }
                $importedCount++;
            }

            $connection->commit();
            $io->success(sprintf('Successfully imported %d record(s) into table: %s', $importedCount, $tableAlias));

            return true;
        } catch (Exception $e) {
            if ($connection->inTransaction()) {
                $connection->rollback();
            }
            $io->error(sprintf(
                'Error importing data for table %s: %s. Transaction rolled back.',
                $tableAlias,
                $e->getMessage(),
            ));

            return false;
        } finally {
            // ALWAYS re-enable FK checks if they were disabled
            if ($disableFkChecks) {
                $this->toggleMySqlForeignKeyChecks($connection, true, $io);
            }
        }
    }

    /**
     * Helper method to toggle MySQL foreign key checks.
     *
     * @param \Cake\Database\Connection $connection The database connection.
     * @param bool $enable True to enable, false to disable.
     * @param \Cake\Console\ConsoleIo $io The console io object.
     * @return void
     */
    protected function toggleMySqlForeignKeyChecks(
        Connection $connection,
        bool $enable,
        ConsoleIo $io,
    ): void {
        $action = $enable ? 'Enabling' : 'Disabling';
        $mode = $enable ? '1' : '0';
        $io->info(sprintf('%s MySQL foreign key checks...', $action));
        try {
            $connection->execute('SET FOREIGN_KEY_CHECKS=' . $mode . ';');
            $io->info(sprintf('MySQL foreign key checks %s.', ($enable ? 'enabled' : 'disabled')));
        } catch (Exception $e) {
            $io->error(sprintf('Failed to %s MySQL foreign key checks: %s', strtolower($action), $e->getMessage()));
            // Attempt to revert to enabled state if disabling failed, or notify if enabling failed.
            if (!$enable) {
                $io->warning('Attempting to re-enable MySQL foreign key checks due to previous error...');
                try {
                    $connection->execute('SET FOREIGN_KEY_CHECKS=1;');
                    $io->info('MySQL foreign key checks re-enabled.');
                } catch (Exception $reEnableEx) {
                    $io->error('Critical: Failed to re-enable MySQL foreign key checks: ' . $reEnableEx->getMessage());
                }
            }
        }
    }
}
