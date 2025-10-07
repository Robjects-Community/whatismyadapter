<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AiMetricsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class AiMetricsTableSqliteTest extends TestCase
{
    protected array $fixtures = ['app.AiMetrics'];

    private AiMetricsTable $AiMetrics;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var AiMetricsTable $table */
        $table = TableRegistry::getTableLocator()->get('AiMetrics');
        $this->AiMetrics = $table;
    }

    protected function tearDown(): void
    {
        unset($this->AiMetrics);
        parent::tearDown();
    }

    public function testGetCostsByDateRangeSqlite(): void
    {
        $total = $this->AiMetrics->getCostsByDateRange('2025-08-10 00:00:00', '2025-08-31 23:59:59');
        $this->assertEqualsWithDelta(2.05, (float)$total, 0.0001);
    }

    public function testGetTaskTypeSummarySqliteCounts(): void
    {
        $summary = $this->AiMetrics->getTaskTypeSummary('2025-08-01 00:00:00', '2025-08-31 23:59:59');
        $byType = [];
        foreach ($summary as $row) {
            $byType[$row['task_type']] = $row;
        }
        $this->assertSame(2, (int)$byType['summarize']['count']);
        $this->assertSame(1, (int)$byType['translate']['count']);
        $this->assertSame(1, (int)$byType['classify']['count']);
    }
}
