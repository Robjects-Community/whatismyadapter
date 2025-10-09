<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AiMetricsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AiMetricsTable Test Case
 */
class AiMetricsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AiMetricsTable
     */
    protected $AiMetrics;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.AiMetrics',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('AiMetrics') ? [] : ['className' => AiMetricsTable::class];
        $this->AiMetrics = $this->getTableLocator()->get('AiMetrics', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AiMetrics);

        parent::tearDown();
    }

    /**
     * Test validationDefault method - success case
     *
     * @return void
     */
    public function testValidationDefaultSuccess(): void
    {
        $entity = $this->AiMetrics->newEntity([
            'task_type' => 'summarize',
            'success' => true,
        ]);
        $this->assertEmpty($entity->getErrors());
        $result = $this->AiMetrics->save($entity);
        $this->assertNotFalse($result, 'Entity should save successfully');
    }

    /**
     * Test validationDefault method - failure cases
     *
     * @return void
     */
    public function testValidationDefaultFailures(): void
    {
        // Test task_type too long
        $entity = $this->AiMetrics->newEntity([
            'task_type' => str_repeat('a', 51), // Max is 50
            'success' => true,
        ]);
        $this->assertNotEmpty($entity->getErrors());
        $this->assertArrayHasKey('task_type', $entity->getErrors());

        // Test missing task_type
        $entity2 = $this->AiMetrics->newEntity([
            'success' => true,
        ]);
        $this->assertNotEmpty($entity2->getErrors());
        $this->assertArrayHasKey('task_type', $entity2->getErrors());

        // Test model_used too long
        $entity3 = $this->AiMetrics->newEntity([
            'task_type' => 'test',
            'model_used' => str_repeat('b', 51), // Max is 50
            'success' => true,
        ]);
        $this->assertNotEmpty($entity3->getErrors());
        $this->assertArrayHasKey('model_used', $entity3->getErrors());
    }

    /**
     * Test getCostsByDateRange method
     *
     * @return void
     */
    public function testGetCostsByDateRange(): void
    {
        $total = $this->AiMetrics->getCostsByDateRange('2025-08-01', '2025-08-31');
        // Based on fixture data: summarize (0.5 + 0.2) + translate (1.3) + classify (0.05) = 2.05
        $this->assertEquals(2.05, $total, 'Cost sum in Aug 2025 mismatch');
    }

    /**
     * Test getTaskTypeSummary method
     *
     * @return void
     */
    public function testGetTaskTypeSummary(): void
    {
        $rows = $this->AiMetrics->getTaskTypeSummary('2025-08-01', '2025-08-31');
        $byType = [];
        foreach ($rows as $r) {
            $byType[$r->task_type] = $r;
        }
        $this->assertArrayHasKey('summarize', $byType);
        $this->assertArrayHasKey('translate', $byType);
        $this->assertArrayHasKey('classify', $byType);
        $this->assertEquals(2, (int)$byType['summarize']->count);
        $this->assertEquals(1, (int)$byType['translate']->count);
        $this->assertEquals(1, (int)$byType['classify']->count);
        $this->assertEqualsWithDelta(50.0, (float)$byType['summarize']->success_rate, 0.1);
    }

    /**
     * Test getRecentErrors method
     *
     * @return void
     */
    public function testGetRecentErrors(): void
    {
        $errors = $this->AiMetrics->getRecentErrors(2);
        $this->assertNotEmpty($errors);
        $this->assertLessThanOrEqual(2, count($errors));
        $this->assertFalse((bool)$errors[0]->success);
        // Verify ordering (most recent first)
        if (count($errors) > 1) {
            $this->assertTrue($errors[0]->created >= $errors[count($errors) - 1]->created);
        }
    }
}
