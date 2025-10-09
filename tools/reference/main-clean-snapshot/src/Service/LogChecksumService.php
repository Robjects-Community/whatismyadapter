<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Log\Log;
use DirectoryIterator;
use Exception;

/**
 * LogChecksumService
 *
 * Provides comprehensive checksum verification for log files using multiple
 * hash algorithms (SHA256, MD5, SHA1) to ensure log file integrity.
 */
class LogChecksumService
{
    private const HASH_ALGORITHMS = ['sha256', 'md5', 'sha1'];
    private const CHECKSUM_DIR = TMP . 'checksums' . DS;
    private const LOG_DIR = LOGS;

    /**
     * Generate checksums for all log files
     *
     * @param array $algorithms Hash algorithms to use (default: all supported)
     * @return array Results of checksum generation
     */
    public function generateChecksums(array $algorithms = self::HASH_ALGORITHMS): array
    {
        $this->ensureChecksumDirectory();
        $results = [];

        foreach ($this->getLogFiles() as $logFile) {
            $results[$logFile] = $this->generateChecksumsForFile($logFile, $algorithms);
        }

        // Generate master checksum file
        $this->generateMasterChecksumFile($results);

        Log::info('Log checksums generated', ['results' => $results]);

        return $results;
    }

    /**
     * Verify checksums for all log files
     *
     * @param array $algorithms Hash algorithms to verify (default: all supported)
     * @return array Verification results
     */
    public function verifyChecksums(array $algorithms = self::HASH_ALGORITHMS): array
    {
        $results = [
            'verified' => [],
            'failed' => [],
            'missing' => [],
            'corrupted' => [],
        ];

        foreach ($this->getLogFiles() as $logFile) {
            $verification = $this->verifyChecksumsForFile($logFile, $algorithms);

            if ($verification['status'] === 'verified') {
                $results['verified'][$logFile] = $verification;
            } elseif ($verification['status'] === 'failed') {
                $results['failed'][$logFile] = $verification;
            } elseif ($verification['status'] === 'missing') {
                $results['missing'][$logFile] = $verification;
            } elseif ($verification['status'] === 'corrupted') {
                $results['corrupted'][$logFile] = $verification;
            }
        }

        Log::info('Log checksum verification completed', [
            'verified_count' => count($results['verified']),
            'failed_count' => count($results['failed']),
            'missing_count' => count($results['missing']),
            'corrupted_count' => count($results['corrupted']),
        ]);

        return $results;
    }

    /**
     * Get integrity report for all log files
     *
     * @return array Comprehensive integrity report
     */
    public function getIntegrityReport(): array
    {
        $verification = $this->verifyChecksums();

        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_logs' => count($this->getLogFiles()),
            'summary' => [
                'verified' => count($verification['verified']),
                'failed' => count($verification['failed']),
                'missing_checksums' => count($verification['missing']),
                'corrupted' => count($verification['corrupted']),
            ],
            'details' => $verification,
            'overall_status' => $this->getOverallStatus($verification),
        ];
    }

    /**
     * Create backup of log files with checksums
     *
     * @param string $backupDir Directory to store backup
     * @return array Backup results
     */
    public function createVerifiedBackup(?string $backupDir = null): array
    {
        if ($backupDir === null) {
            $backupDir = TMP . 'log_backups' . DS . date('Y-m-d_H-i-s') . DS;
        }

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $results = [];
        $this->generateChecksums();

        foreach ($this->getLogFiles() as $logFile) {
            $sourcePath = self::LOG_DIR . $logFile;
            $backupPath = $backupDir . $logFile;

            if (copy($sourcePath, $backupPath)) {
                // Verify backup integrity
                $backupChecksum = hash_file('sha256', $backupPath);
                $originalChecksum = hash_file('sha256', $sourcePath);

                $results[$logFile] = [
                    'backed_up' => true,
                    'integrity_verified' => ($backupChecksum === $originalChecksum),
                    'size' => filesize($backupPath),
                    'checksum' => $backupChecksum,
                ];
            } else {
                $results[$logFile] = [
                    'backed_up' => false,
                    'error' => 'Failed to copy file',
                ];
            }
        }

        // Copy checksum files to backup
        $checksumBackupDir = $backupDir . 'checksums' . DS;
        mkdir($checksumBackupDir, 0755, true);

        foreach (glob(self::CHECKSUM_DIR . '*.txt') as $checksumFile) {
            copy($checksumFile, $checksumBackupDir . basename($checksumFile));
        }

        Log::info('Verified backup created', ['backup_dir' => $backupDir, 'results' => $results]);

        return $results;
    }

    /**
     * Generate checksums for a specific file
     *
     * @param string $logFile Log file name
     * @param array $algorithms Hash algorithms to use
     * @return array Checksum results
     */
    private function generateChecksumsForFile(string $logFile, array $algorithms): array
    {
        $filePath = self::LOG_DIR . $logFile;
        $results = [];

        if (!file_exists($filePath)) {
            return ['error' => 'File not found'];
        }

        $fileStats = [
            'size' => filesize($filePath),
            'modified' => filemtime($filePath),
            'permissions' => substr(sprintf('%o', fileperms($filePath)), -4),
        ];

        foreach ($algorithms as $algorithm) {
            if (in_array($algorithm, hash_algos())) {
                $hash = hash_file($algorithm, $filePath);
                $results[$algorithm] = $hash;

                // Save individual checksum file
                $checksumFile = self::CHECKSUM_DIR . $logFile . '.' . $algorithm . '.txt';
                $checksumData = [
                    'file' => $logFile,
                    'algorithm' => $algorithm,
                    'hash' => $hash,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'file_stats' => $fileStats,
                ];

                file_put_contents($checksumFile, json_encode($checksumData, JSON_PRETTY_PRINT));
            }
        }

        return array_merge($results, ['file_stats' => $fileStats]);
    }

    /**
     * Verify checksums for a specific file
     *
     * @param string $logFile Log file name
     * @param array $algorithms Hash algorithms to verify
     * @return array Verification results
     */
    private function verifyChecksumsForFile(string $logFile, array $algorithms): array
    {
        $filePath = self::LOG_DIR . $logFile;
        $results = [
            'file' => $logFile,
            'status' => 'verified',
            'algorithms' => [],
            'errors' => [],
        ];

        if (!file_exists($filePath)) {
            return [
                'file' => $logFile,
                'status' => 'missing',
                'error' => 'Log file not found',
            ];
        }

        foreach ($algorithms as $algorithm) {
            $checksumFile = self::CHECKSUM_DIR . $logFile . '.' . $algorithm . '.txt';

            if (!file_exists($checksumFile)) {
                $results['algorithms'][$algorithm] = 'no_checksum';
                $results['status'] = 'missing';
                continue;
            }

            try {
                $checksumData = json_decode(file_get_contents($checksumFile), true);
                $storedHash = $checksumData['hash'];
                $currentHash = hash_file($algorithm, $filePath);

                if ($storedHash === $currentHash) {
                    $results['algorithms'][$algorithm] = 'verified';
                } else {
                    $results['algorithms'][$algorithm] = 'failed';
                    $results['status'] = 'failed';
                    $results['errors'][] = "Hash mismatch for {$algorithm}: expected {$storedHash}, got {$currentHash}";
                }

                // Check if file has been modified since checksum
                $checksumTime = strtotime($checksumData['timestamp']);
                $fileTime = filemtime($filePath);

                if ($fileTime > $checksumTime) {
                    $results['file_modified_since_checksum'] = true;
                    $results['checksum_age'] = $fileTime - $checksumTime;
                }
            } catch (Exception $e) {
                $results['algorithms'][$algorithm] = 'error';
                $results['status'] = 'corrupted';
                $results['errors'][] = 'Error reading checksum: ' . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Generate master checksum file containing all checksums
     *
     * @param array $results Checksum generation results
     * @return void
     */
    private function generateMasterChecksumFile(array $results): void
    {
        $masterFile = self::CHECKSUM_DIR . 'master_checksums_' . date('Y-m-d_H-i-s') . '.json';
        $masterData = [
            'generated_at' => date('Y-m-d H:i:s'),
            'log_directory' => self::LOG_DIR,
            'algorithms_used' => self::HASH_ALGORITHMS,
            'total_files' => count($results),
            'checksums' => $results,
        ];

        file_put_contents($masterFile, json_encode($masterData, JSON_PRETTY_PRINT));

        // Create a simple text format for easy verification
        $masterTextFile = self::CHECKSUM_DIR . 'master_checksums.txt';
        $textContent = '# Log File Checksums - Generated: ' . date('Y-m-d H:i:s') . "\n";

        foreach ($results as $file => $checksums) {
            if (isset($checksums['sha256'])) {
                $textContent .= "{$checksums['sha256']}  {$file}\n";
            }
        }

        file_put_contents($masterTextFile, $textContent);
    }

    /**
     * Get all log files
     *
     * @return array List of log file names
     */
    private function getLogFiles(): array
    {
        $logFiles = [];

        if (!is_dir(self::LOG_DIR)) {
            return $logFiles;
        }

        $iterator = new DirectoryIterator(self::LOG_DIR);
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'log') {
                $logFiles[] = $file->getFilename();
            }
        }

        return $logFiles;
    }

    /**
     * Ensure checksum directory exists
     *
     * @return void
     */
    private function ensureChecksumDirectory(): void
    {
        if (!is_dir(self::CHECKSUM_DIR)) {
            mkdir(self::CHECKSUM_DIR, 0755, true);
        }
    }

    /**
     * Get overall status from verification results
     *
     * @param array $verification Verification results
     * @return string Overall status
     */
    private function getOverallStatus(array $verification): string
    {
        if (!empty($verification['corrupted'])) {
            return 'CRITICAL';
        } elseif (!empty($verification['failed'])) {
            return 'WARNING';
        } elseif (!empty($verification['missing'])) {
            return 'INFO';
        } else {
            return 'OK';
        }
    }
}
