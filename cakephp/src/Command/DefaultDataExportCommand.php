<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Exception;

/**
 * DefaultDataExportCommand
 *
 * This command allows exporting data from a selected database table (or all tables)
 * to a JSON file. By default, it excludes common timestamp columns and generic 'id' columns
 * (unless 'id' is part of a composite primary key).
 * An option is provided to include all columns.
 */
class DefaultDataExportCommand extends Command
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
            ->setDescription('Exports data from specified table(s) to JSON files.')
            ->addOption('output', [
                'short' => 'o',
                'help' => 'Output directory for the exported JSON file(s).',
                'default' => ROOT . DS . 'default_data',
            ])
            ->addOption('all', [
                'short' => 'a',
                'help' => 'Export data from all tables. Overrides interactive selection.',
                'boolean' => true,
            ])
            ->addOption('include-all-columns', [
                'short' => 'c',
                'help' => 'Include ALL columns in the export, overriding default 
                    exclusions (id, created, modified unless PK).',
                'boolean' => true,
                'default' => false,
            ]);

        return $parser;
    }

    /**
     * Executes the command to export data.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io object.
     * @return int The exit code of the command.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $outputDir = (string)$args->getOption('output');
        $includeAllColumns = (bool)$args->getOption('include-all-columns');

        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
                 $io->error(sprintf('Output directory "%s" could not be created.', $outputDir));

                 return Command::CODE_ERROR;
            }
            $io->info(sprintf('Output directory "%s" created.', $outputDir));
        }

        $connection = ConnectionManager::get('default');
        $allTables = $connection->getSchemaCollection()->listTables();

        $exportAllByFlag = (bool)$args->getOption('all');

        if ($exportAllByFlag) {
            if (empty($allTables)) {
                $io->info('No tables found in the database to export via --all flag.');

                return Command::CODE_SUCCESS;
            }

            return $this->_exportAllTables($outputDir, $io, $allTables, $includeAllColumns);
        } else {
            if (empty($allTables)) {
                $io->warning('No tables found in the database for interactive export.');

                return Command::CODE_SUCCESS;
            }

            $io->out('Available actions:');
            $io->out('[0] Export All Tables');
            foreach ($allTables as $index => $table) {
                $io->out(sprintf('[%d] %s', $index + 1, $table));
            }

            $choiceStr = $io->ask('Please select an option by number:');

            if (!ctype_digit($choiceStr)) {
                $io->error('Invalid input. Please enter a number. Exiting.');

                return Command::CODE_ERROR;
            }
            $choice = (int)$choiceStr;

            if ($choice === 0) {
                return $this->_exportAllTables($outputDir, $io, $allTables, $includeAllColumns);
            } else {
                $tableIndex = $choice - 1;
                if (!isset($allTables[$tableIndex])) {
                    $io->error('Invalid table selection. Exiting.');

                    return Command::CODE_ERROR;
                }
                $tableName = $allTables[$tableIndex];
                if ($this->exportTableData($tableName, $outputDir, $io, $includeAllColumns)) {
                    return Command::CODE_SUCCESS;
                } else {
                    return Command::CODE_ERROR;
                }
            }
        }

        return Command::CODE_SUCCESS;
    }

    /**
     * Helper method to export all tables.
     *
     * @param string $outputDir The output directory.
     * @param \Cake\Console\ConsoleIo $io The console IO object.
     * @param array $allTables List of all table names.
     * @param bool $includeAllColumns Whether to include all columns.
     * @return int Command exit code (CODE_SUCCESS or CODE_ERROR).
     */
    private function _exportAllTables(string $outputDir, ConsoleIo $io, array $allTables, bool $includeAllColumns): int
    {
        $io->out(sprintf('Exporting all tables to %s...', $outputDir));
        if ($includeAllColumns) {
            $io->info('Including ALL columns in export.');
        }
        $exportedCount = 0;
        $failedCount = 0;

        foreach ($allTables as $tableName) {
            if ($this->exportTableData($tableName, $outputDir, $io, $includeAllColumns)) {
                $exportedCount++;
            } else {
                $failedCount++;
            }
        }

        if ($exportedCount > 0) {
            $io->success(sprintf('Successfully exported data from %d table(s).', $exportedCount));
        }
        if ($failedCount > 0) {
            $io->error(sprintf('Failed to export data from %d table(s). Check logs above.', $failedCount));

            return Command::CODE_ERROR;
        }
        if ($exportedCount === 0 && $failedCount === 0) {
            $io->info('No tables were available or specified to export.');
        }

        return Command::CODE_SUCCESS;
    }

    /**
     * Exports data from a single table to a JSON file.
     *
     * @param string $tableName The name of the table to export.
     * @param string $outputDir The directory to save the JSON file.
     * @param \Cake\Console\ConsoleIo $io The console io object.
     * @param bool $includeAllColumns Whether to include all columns.
     * @return bool True on success, false on failure.
     */
    private function exportTableData(string $tableName, string $outputDir, ConsoleIo $io, bool $includeAllColumns): bool
    {
        $io->out(sprintf('Processing table: %s...', $tableName));
        try {
            $table = TableRegistry::getTableLocator()->get($tableName);
            $query = $table->find();
            $schema = $table->getSchema();
            $allSchemaColumns = $schema->columns();

            $selectedColumns = [];

            if ($includeAllColumns) {
                $io->out(sprintf('Table %s: Including all columns due to --include-all-columns flag.', $tableName));
                $selectedColumns = $allSchemaColumns;
            } else {
                $primaryKey = (array)$schema->getPrimaryKey();
                $columnsToPotentiallyExclude = ['id', 'created', 'modified'];

                $selectedColumns = $allSchemaColumns;
                foreach ($columnsToPotentiallyExclude as $col) {
                    if (in_array($col, $selectedColumns) && !in_array($col, $primaryKey)) {
                        $selectedColumns = array_diff($selectedColumns, [$col]);
                    }
                }
                // $io->out(sprintf('Table %s: Exporting columns: %s', $tableName, implode(', ', $selectedColumns)));
            }

            if (empty($selectedColumns)) {
                $io->info(sprintf(
                    'Table "%s" has no columns to export after considering exclusions/inclusions. Skipping.',
                    $tableName,
                ));

                return true;
            }

            $query->select(array_values($selectedColumns));
            $data = $query->disableHydration()->all()->toArray();

            $outputFile = $outputDir . DS . Inflector::underscore($tableName) . '.json';
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($json === false) {
                $io->error(sprintf('Failed to encode JSON for table %s. Error: %s', $tableName, json_last_error_msg()));

                return false;
            }

            if (file_put_contents($outputFile, $json) === false) {
                $io->error(sprintf('Failed to write data for table %s to %s.', $tableName, $outputFile));

                return false;
            }

            $io->success(sprintf('Data for table "%s" exported to %s', $tableName, $outputFile));

            return true;
        } catch (Exception $e) {
            $io->error(sprintf('Error exporting table "%s": %s', $tableName, $e->getMessage()));

            return false;
        }
    }
}
