<?php
namespace App\Test\TestCase;

use Cake\TestSuite\TestCase;

/**
 * Log Checksum Verification Test
 * 
 * Tests for log file integrity verification using checksums
 * as specifically requested by the user.
 */
class LogChecksumVerificationTest extends TestCase
{
    /**
     * Test log files directory
     *
     * @var string
     */
    private string $logDir = '/var/www/html/logs';

    /**
     * Test that log files exist
     *
     * @return void
     */
    public function testLogFilesExist(): void
    {
        $expectedLogFiles = ['error.log', 'debug.log', 'database_log_errors.log'];
        
        foreach ($expectedLogFiles as $logFile) {
            $filePath = $this->logDir . '/' . $logFile;
            $this->assertFileExists($filePath, "Log file {$logFile} should exist");
        }
    }

    /**
     * Test SHA256 checksum generation for log files
     *
     * @return void
     */
    public function testSha256ChecksumGeneration(): void
    {
        $logFiles = glob($this->logDir . '/*.log');
        
        foreach ($logFiles as $logFile) {
            $checksumFile = $logFile . '.sha256';
            $this->assertFileExists($checksumFile, "SHA256 checksum file should exist for " . basename($logFile));
            
            // Verify checksum file is not empty
            $checksumContent = file_get_contents($checksumFile);
            $this->assertNotEmpty($checksumContent, "SHA256 checksum file should not be empty");
            
            // Verify checksum format (64 hex chars + 2 spaces + filename)
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}\s+/', $checksumContent, "SHA256 checksum should be valid format");
        }
    }

    /**
     * Test MD5 checksum generation for log files
     *
     * @return void
     */
    public function testMd5ChecksumGeneration(): void
    {
        $logFiles = glob($this->logDir . '/*.log');
        
        foreach ($logFiles as $logFile) {
            $checksumFile = $logFile . '.md5';
            $this->assertFileExists($checksumFile, "MD5 checksum file should exist for " . basename($logFile));
            
            // Verify checksum file is not empty
            $checksumContent = file_get_contents($checksumFile);
            $this->assertNotEmpty($checksumContent, "MD5 checksum file should not be empty");
            
            // Verify checksum format (32 hex chars + 2 spaces + filename)
            $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\s+/', $checksumContent, "MD5 checksum should be valid format");
        }
    }

    /**
     * Test SHA256 checksum verification integrity
     *
     * @return void
     */
    public function testSha256ChecksumVerification(): void
    {
        $logFiles = glob($this->logDir . '/*.log');
        
        foreach ($logFiles as $logFile) {
            $checksumFile = $logFile . '.sha256';
            
            if (file_exists($checksumFile)) {
                // Read stored checksum
                $storedChecksum = trim(explode(' ', file_get_contents($checksumFile))[0]);
                
                // Calculate current checksum
                $currentChecksum = hash_file('sha256', $logFile);
                
                $this->assertEquals($storedChecksum, $currentChecksum, 
                    "SHA256 checksum should match for " . basename($logFile));
            }
        }
    }

    /**
     * Test MD5 checksum verification integrity
     *
     * @return void
     */
    public function testMd5ChecksumVerification(): void
    {
        $logFiles = glob($this->logDir . '/*.log');
        
        foreach ($logFiles as $logFile) {
            $checksumFile = $logFile . '.md5';
            
            if (file_exists($checksumFile)) {
                // Read stored checksum
                $storedChecksum = trim(explode(' ', file_get_contents($checksumFile))[0]);
                
                // Calculate current checksum
                $currentChecksum = hash_file('md5', $logFile);
                
                $this->assertEquals($storedChecksum, $currentChecksum, 
                    "MD5 checksum should match for " . basename($logFile));
            }
        }
    }

    /**
     * Test log file modification detection through checksum changes
     *
     * @return void
     */
    public function testLogModificationDetection(): void
    {
        $testLogFile = $this->logDir . '/test_modification.log';
        $testChecksumFile = $testLogFile . '.sha256';
        
        // Create a test log file
        file_put_contents($testLogFile, "Initial content\n");
        
        // Generate initial checksum
        $initialChecksum = hash_file('sha256', $testLogFile);
        file_put_contents($testChecksumFile, $initialChecksum . "  " . basename($testLogFile));
        
        // Verify initial state
        $this->assertEquals($initialChecksum, hash_file('sha256', $testLogFile));
        
        // Modify the log file
        file_put_contents($testLogFile, "Modified content\n");
        
        // Verify checksum changed
        $newChecksum = hash_file('sha256', $testLogFile);
        $this->assertNotEquals($initialChecksum, $newChecksum, 
            "Checksum should change when log file is modified");
        
        // Clean up test files
        unlink($testLogFile);
        unlink($testChecksumFile);
    }

    /**
     * Test batch checksum verification functionality
     *
     * @return void
     */
    public function testBatchChecksumVerification(): void
    {
        $logFiles = glob($this->logDir . '/*.log');
        $verifiedCount = 0;
        $failedVerifications = [];
        
        foreach ($logFiles as $logFile) {
            $sha256File = $logFile . '.sha256';
            $md5File = $logFile . '.md5';
            
            if (file_exists($sha256File)) {
                $storedSha256 = trim(explode(' ', file_get_contents($sha256File))[0]);
                $currentSha256 = hash_file('sha256', $logFile);
                
                if ($storedSha256 === $currentSha256) {
                    $verifiedCount++;
                } else {
                    $failedVerifications[] = basename($logFile) . ' (SHA256)';
                }
            }
            
            if (file_exists($md5File)) {
                $storedMd5 = trim(explode(' ', file_get_contents($md5File))[0]);
                $currentMd5 = hash_file('md5', $logFile);
                
                if ($storedMd5 === $currentMd5) {
                    $verifiedCount++;
                } else {
                    $failedVerifications[] = basename($logFile) . ' (MD5)';
                }
            }
        }
        
        $this->assertGreaterThan(0, $verifiedCount, "At least one checksum should be verified");
        $this->assertEmpty($failedVerifications, "No checksum verifications should fail: " . implode(', ', $failedVerifications));
    }

    /**
     * Test checksum file format compliance
     *
     * @return void
     */
    public function testChecksumFileFormatCompliance(): void
    {
        $checksumFiles = array_merge(
            glob($this->logDir . '/*.sha256'),
            glob($this->logDir . '/*.md5')
        );
        
        foreach ($checksumFiles as $checksumFile) {
            $content = file_get_contents($checksumFile);
            
            // Should contain checksum, spaces, and filename
            $parts = preg_split('/\s+/', trim($content));
            $this->assertGreaterThanOrEqual(2, count($parts), 
                "Checksum file should contain checksum and filename: " . basename($checksumFile));
            
            // First part should be hex checksum
            $checksum = $parts[0];
            $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $checksum, 
                "Checksum should be valid hex: " . basename($checksumFile));
            
            // Verify checksum length
            if (str_ends_with($checksumFile, '.sha256')) {
                $this->assertEquals(64, strlen($checksum), "SHA256 checksum should be 64 characters");
            } elseif (str_ends_with($checksumFile, '.md5')) {
                $this->assertEquals(32, strlen($checksum), "MD5 checksum should be 32 characters");
            }
        }
    }
}