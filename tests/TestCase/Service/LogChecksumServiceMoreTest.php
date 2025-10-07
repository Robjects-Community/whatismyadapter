<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\LogChecksumService;
use Cake\TestSuite\TestCase;

class LogChecksumServiceMoreTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logFile = 'test_checksum_more.log';
        file_put_contents(LOGS . $this->logFile, "start\n");
    }

    protected function tearDown(): void
    {
        @unlink(LOGS . $this->logFile);
        foreach (glob(TMP . 'checksums' . DIRECTORY_SEPARATOR . $this->logFile . '.*.txt') ?: [] as $f) {
            @unlink($f);
        }
        parent::tearDown();
    }

    public function testGetIntegrityReportOk(): void
    {
        $svc = new LogChecksumService();
        $svc->generateChecksums(['sha256']);
        $report = $svc->getIntegrityReport();
        $this->assertSame('OK', $report['overall_status']);
        $this->assertGreaterThanOrEqual(1, $report['summary']['verified']);
    }
}
