<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\LogChecksumService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * LogChecksumCommand
 *
 * Command for generating, verifying, and managing checksums of log files
 * to ensure log integrity and detect tampering or corruption.
 *
 * Usage examples:
 * - bin/cake log_checksum generate
 * - bin/cake log_checksum verify
 * - bin/cake log_checksum report
 * - bin/cake log_checksum backup
 */
class LogChecksumCommand extends Command
{
    /**
     * @var \App\Service\LogChecksumService
     */
    private LogChecksumService $checksumService;

    /**
     * Initialize the command
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->checksumService = new LogChecksumService();
    }

    /**
     * Build the option parser
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to build
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Generate, verify, and manage checksums for log files')
            ->addArgument('action', [
                'help' => 'Action to perform (generate, verify, report, backup)',
                'required' => true,
                'choices' => ['generate', 'verify', 'report', 'backup'],
            ])
            ->addOption('algorithms', [
                'short' => 'a',
                'help' => 'Comma-separated list of hash algorithms to use (sha256,md5,sha1)',
                'default' => 'sha256,md5,sha1',
            ])
            ->addOption('format', [
                'short' => 'f',
                'help' => 'Output format (table, json, detailed)',
                'default' => 'table',
                'choices' => ['table', 'json', 'detailed'],
            ])
            ->addOption('backup-dir', [
                'help' => 'Directory to store backup (for backup command)',
                'default' => null,
            ]);

        return $parser;
    }

    /**
     * Execute the command
     *
     * @param \Cake\Console\Arguments $args Command arguments
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @return int Exit code
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $action = $args->getArgument('action');
        $algorithms = $this->parseAlgorithms($args->getOption('algorithms'));
        $format = $args->getOption('format');

        switch ($action) {
            case 'generate':
                return $this->generateChecksums($io, $algorithms, $format);

            case 'verify':
                return $this->verifyChecksums($io, $algorithms, $format);

            case 'report':
                return $this->generateReport($io, $format);

            case 'backup':
                return $this->createBackup($io, $args->getOption('backup-dir'));

            default:
                $io->error('Invalid action. Use: generate, verify, report, or backup');

                return static::CODE_ERROR;
        }
    }

    /**
     * Generate checksums for all log files
     *
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @param array $algorithms Hash algorithms to use
     * @param string $format Output format
     * @return int Exit code
     */
    private function generateChecksums(ConsoleIo $io, array $algorithms, string $format): int
    {
        $io->out('<info>Generating checksums for log files...</info>');
        $io->out('Algorithms: ' . implode(', ', $algorithms));

        $results = $this->checksumService->generateChecksums($algorithms);

        if (empty($results)) {
            $io->warning('No log files found to process.');

            return static::CODE_SUCCESS;
        }

        $this->displayResults($io, $results, $format, 'Checksum Generation Results');

        $io->success(sprintf('Checksums generated for %d log files.', count($results)));

        return static::CODE_SUCCESS;
    }

    /**
     * Verify checksums for all log files
     *
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @param array $algorithms Hash algorithms to verify
     * @param string $format Output format
     * @return int Exit code
     */
    private function verifyChecksums(ConsoleIo $io, array $algorithms, string $format): int
    {
        $io->out('<info>Verifying log file checksums...</info>');
        $io->out('Algorithms: ' . implode(', ', $algorithms));

        $results = $this->checksumService->verifyChecksums($algorithms);

        // Display summary
        $io->out('');
        $io->out('<info>Verification Summary:</info>');
        $io->out(sprintf('  Verified:           %d', count($results['verified'])));
        $io->out(sprintf('  Failed:             %d', count($results['failed'])));
        $io->out(sprintf('  Missing checksums:  %d', count($results['missing'])));
        $io->out(sprintf('  Corrupted:          %d', count($results['corrupted'])));
        $io->out('');

        // Display detailed results based on format
        if ($format === 'detailed') {
            $this->displayDetailedVerificationResults($io, $results);
        } elseif ($format === 'json') {
            $io->out(json_encode($results, JSON_PRETTY_PRINT));
        } else {
            $this->displayVerificationTable($io, $results);
        }

        // Determine exit code based on results
        if (!empty($results['corrupted'])) {
            $io->error('CRITICAL: Some log files are corrupted!');

            return static::CODE_ERROR;
        } elseif (!empty($results['failed'])) {
            $io->warning('WARNING: Some log file checksums failed verification!');

            return static::CODE_ERROR;
        } elseif (!empty($results['missing'])) {
            $io->info('INFO: Some log files are missing checksums.');
        }

        $io->success('Log file verification completed.');

        return static::CODE_SUCCESS;
    }

    /**
     * Generate comprehensive integrity report
     *
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @param string $format Output format
     * @return int Exit code
     */
    private function generateReport(ConsoleIo $io, string $format): int
    {
        $io->out('<info>Generating log integrity report...</info>');

        $report = $this->checksumService->getIntegrityReport();

        if ($format === 'json') {
            $io->out(json_encode($report, JSON_PRETTY_PRINT));
        } else {
            $this->displayIntegrityReport($io, $report);
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Create verified backup of log files
     *
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @param string|null $backupDir Backup directory
     * @return int Exit code
     */
    private function createBackup(ConsoleIo $io, ?string $backupDir): int
    {
        $io->out('<info>Creating verified backup of log files...</info>');

        if ($backupDir) {
            $io->out("Backup directory: {$backupDir}");
        }

        $results = $this->checksumService->createVerifiedBackup($backupDir);

        $io->out('');
        $io->out('<info>Backup Results:</info>');

        $successCount = 0;
        $failureCount = 0;

        foreach ($results as $file => $result) {
            if ($result['backed_up']) {
                $status = $result['integrity_verified'] ? '<success>✓</success>' : '<warning>⚠</warning>';
                $io->out("  {$status} {$file} (" . $this->formatFileSize($result['size']) . ')');
                $successCount++;
            } else {
                $io->out("  <error>✗</error> {$file} - {$result['error']}");
                $failureCount++;
            }
        }

        $io->out('');
        $io->out(sprintf('Backup completed: %d successful, %d failed', $successCount, $failureCount));

        return $failureCount > 0 ? static::CODE_ERROR : static::CODE_SUCCESS;
    }

    /**
     * Parse algorithms option
     *
     * @param string $algorithmsOption Comma-separated algorithms
     * @return array Parsed algorithms
     */
    private function parseAlgorithms(string $algorithmsOption): array
    {
        return array_map('trim', explode(',', $algorithmsOption));
    }

    /**
     * Display results in various formats
     *
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @param array $results Results to display
     * @param string $format Display format
     * @param string $title Table title
     * @return void
     */
    private function displayResults(ConsoleIo $io, array $results, string $format, string $title): void
    {
        if ($format === 'json') {
            $io->out(json_encode($results, JSON_PRETTY_PRINT));

            return;
        }

        $io->out('');
        $io->out("<info>{$title}</info>");
        $io->hr();

        foreach ($results as $file => $data) {
            if (isset($data['error'])) {
                $io->out("<error>✗ {$file}: {$data['error']}</error>");
                continue;
            }

            $io->out("<success>✓ {$file}</success>");

            if ($format === 'detailed') {
                foreach (['sha256', 'md5', 'sha1'] as $algo) {
                    if (isset($data[$algo])) {
                        $io->out("  {$algo}: {$data[$algo]}");
                    }
                }
                if (isset($data['file_stats'])) {
                    $stats = $data['file_stats'];
                    $io->out('  Size: ' . $this->formatFileSize($stats['size']));
                    $io->out('  Modified: ' . date('Y-m-d H:i:s', $stats['modified']));
                    $io->out("  Permissions: {$stats['permissions']}");
                }
                $io->out('');
            }
        }
    }

    /**
     * Display verification results in table format
     *
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @param array $results Verification results
     * @return void
     */
    private function displayVerificationTable(ConsoleIo $io, array $results): void
    {
        $allFiles = array_merge(
            $results['verified'],
            $results['failed'],
            $results['missing'],
            $results['corrupted'],
        );

        if (empty($allFiles)) {
            $io->out('No files to display.');

            return;
        }

        $headers = ['File', 'Status', 'SHA256', 'MD5', 'SHA1', 'Issues'];
        $rows = [];

        foreach ($allFiles as $file => $data) {
            $status = $this->getStatusDisplayText($data['status']);
            $sha256 = isset($data['algorithms']['sha256']) ? $this->getStatusDisplayText($data['algorithms']['sha256']) : 'N/A';
            $md5 = isset($data['algorithms']['md5']) ? $this->getStatusDisplayText($data['algorithms']['md5']) : 'N/A';
            $sha1 = isset($data['algorithms']['sha1']) ? $this->getStatusDisplayText($data['algorithms']['sha1']) : 'N/A';
            $issues = empty($data['errors']) ? '' : implode(', ', $data['errors']);

            $rows[] = [$file, $status, $sha256, $md5, $sha1, $issues];
        }

        $io->helper('Table')->output($headers, $rows);
    }

    /**
     * Display detailed verification results
     *
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @param array $results Verification results
     * @return void
     */
    private function displayDetailedVerificationResults(ConsoleIo $io, array $results): void
    {
        foreach (['verified', 'failed', 'missing', 'corrupted'] as $category) {
            if (empty($results[$category])) {
                continue;
            }

            $io->out('');
            $io->out('<info>' . ucfirst($category) . ' Files:</info>');
            $io->hr();

            foreach ($results[$category] as $file => $data) {
                $status = $this->getStatusIcon($data['status']);
                $io->out("{$status} {$file}");

                if (isset($data['algorithms'])) {
                    foreach ($data['algorithms'] as $algo => $result) {
                        $resultIcon = $this->getAlgorithmResultIcon($result);
                        $io->out("  {$algo}: {$resultIcon} {$result}");
                    }
                }

                if (!empty($data['errors'])) {
                    foreach ($data['errors'] as $error) {
                        $io->out("  <error>Error: {$error}</error>");
                    }
                }

                if (isset($data['file_modified_since_checksum'])) {
                    $age = $this->formatDuration($data['checksum_age']);
                    $io->out("  <warning>Warning: File modified {$age} after checksum</warning>");
                }

                $io->out('');
            }
        }
    }

    /**
     * Display integrity report
     *
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @param array $report Integrity report data
     * @return void
     */
    private function displayIntegrityReport(ConsoleIo $io, array $report): void
    {
        $io->out('');
        $io->out('<info>Log File Integrity Report</info>');
        $io->hr();

        $io->out("Generated: {$report['timestamp']}");
        $io->out("Total Log Files: {$report['total_logs']}");
        $io->out('Overall Status: ' . $this->getOverallStatusDisplay($report['overall_status']));
        $io->out('');

        $io->out('<info>Summary:</info>');
        $summary = $report['summary'];
        $io->out("  Verified:           {$summary['verified']}");
        $io->out("  Failed:             {$summary['failed']}");
        $io->out("  Missing Checksums:  {$summary['missing_checksums']}");
        $io->out("  Corrupted:          {$summary['corrupted']}");

        // Display recommendations based on status
        $io->out('');
        $io->out('<info>Recommendations:</info>');

        if ($report['overall_status'] === 'OK') {
            $io->out('  <success>✓ All log files have verified integrity.</success>');
        } else {
            if ($summary['missing_checksums'] > 0) {
                $io->out('  <warning>• Run "log_checksum generate" to create missing checksums.</warning>');
            }
            if ($summary['failed'] > 0) {
                $io->out('  <error>• Investigate failed files - they may have been modified or corrupted.</error>');
            }
            if ($summary['corrupted'] > 0) {
                $io->out('  <error>• URGENT: Corrupted files detected - immediate investigation required!</error>');
            }
        }
    }

    /**
     * Get status icon for display
     *
     * @param string $status Status string
     * @return string Status icon
     */
    private function getStatusIcon(string $status): string
    {
        return match ($status) {
            'verified' => '<success>✓</success>',
            'failed' => '<error>✗</error>',
            'missing' => '<warning>?</warning>',
            'corrupted' => '<error>⚠</error>',
            default => '<info>•</info>'
        };
    }

    /**
     * Get status text for table display (without HTML tags)
     *
     * @param string $status Status string
     * @return string Status text
     */
    private function getStatusDisplayText(string $status): string
    {
        return match ($status) {
            'verified' => 'OK',
            'failed' => 'FAILED',
            'missing' => 'MISSING',
            'corrupted' => 'CORRUPT',
            'no_checksum' => 'NO CHECKSUM',
            'error' => 'ERROR',
            default => strtoupper($status)
        };
    }

    /**
     * Get algorithm result icon
     *
     * @param string $result Result string
     * @return string Result icon
     */
    private function getAlgorithmResultIcon(string $result): string
    {
        return match ($result) {
            'verified' => '<success>✓</success>',
            'failed' => '<error>✗</error>',
            'no_checksum' => '<warning>?</warning>',
            'error' => '<error>⚠</error>',
            default => '<info>•</info>'
        };
    }

    /**
     * Get overall status display
     *
     * @param string $status Overall status
     * @return string Formatted status
     */
    private function getOverallStatusDisplay(string $status): string
    {
        return match ($status) {
            'OK' => '<success>OK</success>',
            'INFO' => '<info>INFO</info>',
            'WARNING' => '<warning>WARNING</warning>',
            'CRITICAL' => '<error>CRITICAL</error>',
            default => $status
        };
    }

    /**
     * Format file size for display
     *
     * @param int $size Size in bytes
     * @return string Formatted size
     */
    private function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;
        $maxUnit = count($units) - 1;

        while ($size >= 1024 && $unit < $maxUnit) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Format duration for display
     *
     * @param int $seconds Duration in seconds
     * @return string Formatted duration
     */
    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} seconds";
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . ' minutes';
        } elseif ($seconds < 86400) {
            return round($seconds / 3600) . ' hours';
        } else {
            return round($seconds / 86400) . ' days';
        }
    }
}
