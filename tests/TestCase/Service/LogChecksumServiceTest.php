<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\LogChecksumService;
use Cake\TestSuite\TestCase;

class LogChecksumServiceTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logFile = 'test_checksum.log';
        file_put_contents(LOGS . $this->logFile, "initial line\n");
    }

    protected function tearDown(): void
    {
        @unlink(LOGS . $this->logFile);
        // remove generated checksum files
        foreach (glob(TMP . 'checksums' . DIRECTORY_SEPARATOR . $this->logFile . '.*.txt') ?: [] as $f) {
            @unlink($f);
        }
        parent::tearDown();
    }

    public function testGenerateAndVerifyChecksums(): void
    {
        $svc = new LogChecksumService();
        $results = $svc->generateChecksums(['sha256', 'md5']);

        $this->assertArrayHasKey($this->logFile, $results);
        $this->assertArrayHasKey('sha256', $results[$this->logFile]);
        $this->assertArrayHasKey('md5', $results[$this->logFile]);

        $verify = $svc->verifyChecksums(['sha256', 'md5']);
        $this->assertArrayHasKey('verified', $verify);
        $this->assertArrayHasKey($this->logFile, $verify['verified']);
    }

    public function testDetectsModificationAfterChecksum(): void
    {
        $svc = new LogChecksumService();
        $svc->generateChecksums(['sha256']);

        // Modify the log file
        file_put_contents(LOGS . $this->logFile, "changed line\n", FILE_APPEND);

        $verify = $svc->verifyChecksums(['sha256']);
        $this->assertArrayHasKey('failed', $verify);
        $this->assertArrayHasKey($this->logFile, $verify['failed']);
    }
}
