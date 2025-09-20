<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\LogTrait;

/**
 * Command for verifying bulk action logs with checksum verification.
 */
class VerifyBulkLogsCommand extends Command
{
    use LogTrait;

    /**
     * Builds the option parser for the command.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The console option parser.
     * @return \Cake\Console\ConsoleOptionParser The configured console option parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser
            ->setDescription('Verifies the integrity of bulk action logs using checksum verification.')
            ->addOption('file', [
                'short' => 'f',
                'help' => 'Path to the bulk actions log file. Defaults to logs/bulk_actions.log',
                'default' => LOGS . 'bulk_actions.log',
            ])
            ->addOption('verbose', [
                'short' => 'v',
                'help' => 'Show detailed output for each verified entry.',
                'boolean' => true,
                'default' => false,
            ])
            ->addOption('repair', [
                'short' => 'r',
                'help' => 'Attempt to repair corrupted entries by removing them.',
                'boolean' => true,
                'default' => false,
            ]);

        return $parser;
    }

    /**
     * Executes the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console I/O.
     * @return int The exit code.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $logFile = $args->getOption('file');
        $verbose = $args->getOption('verbose');
        $repair = $args->getOption('repair');

        $io->info("Verifying bulk actions log: {$logFile}");

        if (!file_exists($logFile)) {
            $io->warning("Log file does not exist: {$logFile}");

            return static::CODE_SUCCESS; // Not an error if file doesn't exist yet
        }

        if (!is_readable($logFile)) {
            $io->error("Cannot read log file: {$logFile}");

            return static::CODE_ERROR;
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            $io->error("Failed to read log file: {$logFile}");

            return static::CODE_ERROR;
        }

        $totalLines = count($lines);
        $validLines = 0;
        $invalidLines = 0;
        $corruptedLines = [];

        $io->out("Checking {$totalLines} log entries...");

        foreach ($lines as $lineNumber => $line) {
            $realLineNumber = $lineNumber + 1;

            if ($this->verifyLogLine($line, $realLineNumber, $io, $verbose)) {
                $validLines++;
            } else {
                $invalidLines++;
                $corruptedLines[] = $realLineNumber;
            }
        }

        // Summary
        $io->out('');
        $io->out('=== VERIFICATION SUMMARY ===');
        $io->out("Total entries: {$totalLines}");
        $io->success("Valid entries: {$validLines}");

        if ($invalidLines > 0) {
            $io->error("Corrupted entries: {$invalidLines}");
            $io->out('Corrupted line numbers: ' . implode(', ', $corruptedLines));

            if ($repair) {
                $io->out('');
                $io->question('Attempting to repair corrupted entries...');

                if ($this->repairLogFile($logFile, $corruptedLines, $io)) {
                    $io->success("Repaired log file by removing {$invalidLines} corrupted entries.");
                } else {
                    $io->error('Failed to repair log file.');

                    return static::CODE_ERROR;
                }
            } else {
                $io->out('');
                $io->info('Use --repair option to remove corrupted entries.');
            }

            return static::CODE_ERROR;
        } else {
            $io->success('All entries are valid. Log integrity verified!');

            return static::CODE_SUCCESS;
        }
    }

    /**
     * Verify a single log line's checksum
     *
     * @param string $line The log line to verify
     * @param int $lineNumber The line number for error reporting
     * @param \Cake\Console\ConsoleIo $io Console IO for output
     * @param bool $verbose Whether to show detailed output
     * @return bool True if line is valid, false otherwise
     */
    private function verifyLogLine(string $line, int $lineNumber, ConsoleIo $io, bool $verbose): bool
    {
        $line = trim($line);

        if (empty($line)) {
            if ($verbose) {
                $io->verbose("Line {$lineNumber}: Empty line (skipped)");
            }

            return true; // Empty lines are okay
        }

        // Check if line starts with our checksum format
        if (!preg_match('/^\[sha256=([a-f0-9]{64})\] (.+)$/', $line, $matches)) {
            $io->warning("Line {$lineNumber}: Missing or invalid checksum format");

            return false;
        }

        $expectedChecksum = $matches[1];
        $jsonData = $matches[2];

        // Verify the checksum
        $actualChecksum = hash('sha256', $jsonData);

        if ($expectedChecksum !== $actualChecksum) {
            $io->error("Line {$lineNumber}: Checksum mismatch");
            $io->error("  Expected: {$expectedChecksum}");
            $io->error("  Actual:   {$actualChecksum}");

            return false;
        }

        // Verify JSON is valid
        $data = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error("Line {$lineNumber}: Invalid JSON data - " . json_last_error_msg());

            return false;
        }

        // Verify required fields exist
        $requiredFields = ['timestamp', 'user_id', 'action', 'ids'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $io->error("Line {$lineNumber}: Missing required field: {$field}");

                return false;
            }
        }

        if ($verbose) {
            $io->success("Line {$lineNumber}: Valid - {$data['action']} by {$data['user_id']} on " . count($data['ids']) . ' items');
        }

        return true;
    }

    /**
     * Repair the log file by removing corrupted entries
     *
     * @param string $logFile Path to the log file
     * @param array $corruptedLines Array of line numbers to remove (1-based)
     * @param \Cake\Console\ConsoleIo $io Console IO for output
     * @return bool True if repair was successful, false otherwise
     */
    private function repairLogFile(string $logFile, array $corruptedLines, ConsoleIo $io): bool
    {
        // Create backup first
        $backupFile = $logFile . '.backup.' . date('Y-m-d_H-i-s');
        if (!copy($logFile, $backupFile)) {
            $io->error("Failed to create backup file: {$backupFile}");

            return false;
        }
        $io->info("Created backup: {$backupFile}");

        $lines = file($logFile, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            $io->error('Failed to read log file for repair');

            return false;
        }

        // Remove corrupted lines (convert to 0-based indexing)
        $linesToRemove = array_map(fn($line) => $line - 1, $corruptedLines);
        $repairedLines = [];

        foreach ($lines as $index => $line) {
            if (!in_array($index, $linesToRemove)) {
                $repairedLines[] = $line;
            }
        }

        // Write repaired content back
        $result = file_put_contents($logFile, implode("\n", $repairedLines) . "\n");

        if ($result === false) {
            $io->error('Failed to write repaired log file');

            return false;
        }

        $io->success('Successfully wrote ' . count($repairedLines) . ' valid entries back to log file');

        return true;
    }
}
