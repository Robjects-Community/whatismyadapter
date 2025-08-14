<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\AiMetric;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\AiMetric Test Case
 */
class AiMetricTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\AiMetric
     */
    protected $AiMetric;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->AiMetric = new AiMetric();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AiMetric);

        parent::tearDown();
    }

    /**
     * Test property access and casting
     *
     * @return void
     */
    public function testPropertyAccessAndCasting(): void
    {
        $data = [
            'task_type' => 'summarize',
            'execution_time_ms' => 250,
            'tokens_used' => 120,
            'cost_usd' => '0.500000',
            'success' => true,
            'error_message' => 'Test error',
            'model_used' => 'gpt-4o-mini',
        ];

        $entity = new AiMetric($data);

        $this->assertSame('summarize', $entity->task_type);
        $this->assertSame(250, $entity->execution_time_ms);
        $this->assertSame(120, $entity->tokens_used);
        $this->assertSame('0.500000', $entity->cost_usd);
        $this->assertTrue($entity->success);
        $this->assertSame('Test error', $entity->error_message);
        $this->assertSame('gpt-4o-mini', $entity->model_used);
    }

    /**
     * Test mass assignment security
     *
     * @return void
     */
    public function testMassAssignmentSecurity(): void
    {
        $entity = new AiMetric();
        $data = [
            'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'task_type' => 'summarize',
            'success' => true,
        ];
        $patched = $entity->patch($data, ['guard' => true]);
        $this->assertNull($patched->id, 'id should not be mass assignable');
        $this->assertSame('summarize', $patched->task_type);
    }

    /**
     * Test accessible properties
     *
     * @return void
     */
    public function testAccessibleProperties(): void
    {
        $entity = new AiMetric();

        // These should be accessible
        $this->assertTrue($entity->isAccessible('task_type'));
        $this->assertTrue($entity->isAccessible('execution_time_ms'));
        $this->assertTrue($entity->isAccessible('tokens_used'));
        $this->assertTrue($entity->isAccessible('cost_usd'));
        $this->assertTrue($entity->isAccessible('success'));
        $this->assertTrue($entity->isAccessible('error_message'));
        $this->assertTrue($entity->isAccessible('model_used'));

        // ID should not be accessible by default
        $this->assertFalse($entity->isAccessible('id'));
    }
}
