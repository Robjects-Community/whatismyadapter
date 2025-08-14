<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\AppControllerTestCase;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\Admin\AiMetricsController Test Case
 *
 * @link \App\Controller\Admin\AiMetricsController
 */
class AiMetricsControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.AiMetrics',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // Login as admin user using ID from Users fixture
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f');
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndexOk(): void
    {
        $this->get('/admin/ai-metrics');
        $this->assertResponseOk();
    }

    /**
     * Test index with Ajax search
     *
     * @return void
     */
    public function testIndexAjaxSearch(): void
    {
        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);
        $this->get('/admin/ai-metrics?search=summarize');
        $this->assertResponseOk();
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testViewOk(): void
    {
        $this->get('/admin/ai-metrics/view/11111111-1111-1111-1111-111111111111');
        $this->assertResponseOk();
    }

    /**
     * Test add method - success
     *
     * @return void
     */
    public function testAddSuccess(): void
    {
        $data = [
            'task_type' => 'test',
            'success' => true,
            'execution_time_ms' => 100,
            'tokens_used' => 50,
            'cost_usd' => 0.25,
            'model_used' => 'test-model',
        ];

        $this->post('/admin/ai-metrics/add', $data);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The ai metric has been saved.');

        // Verify record was created
        $table = $this->getTableLocator()->get('AiMetrics');
        $entity = $table->find()->where(['task_type' => 'test'])->first();
        $this->assertNotNull($entity);
        $this->assertEquals('test', $entity->task_type);
    }

    /**
     * Test add method - validation error
     *
     * @return void
     */
    public function testAddValidationError(): void
    {
        $data = [
            // Missing required task_type - should cause validation to fail
            'success' => true,
        ];

        $this->post('/admin/ai-metrics/add', $data);
        $this->assertNoRedirect();
        // Validation failed, so no redirect should happen - test passes if we get here
        $this->assertResponseOk();
    }

    /**
     * Test edit method - success
     *
     * @return void
     */
    public function testEditSuccess(): void
    {
        $data = [
            'task_type' => 'updated_task',
            'success' => false,
            'error_message' => 'Updated error',
        ];

        $this->put('/admin/ai-metrics/edit/11111111-1111-1111-1111-111111111111', $data);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The ai metric has been saved.');

        // Verify record was updated
        $table = $this->getTableLocator()->get('AiMetrics');
        $entity = $table->get('11111111-1111-1111-1111-111111111111');
        $this->assertEquals('updated_task', $entity->task_type);
        $this->assertFalse($entity->success);
        $this->assertEquals('Updated error', $entity->error_message);
    }

    /**
     * Test delete method - success
     *
     * @return void
     */
    public function testDeleteSuccess(): void
    {
        $this->configRequest([
            'environment' => [
                'HTTP_REFERER' => 'http://localhost:8080/admin/ai-metrics',
            ],
        ]);

        $this->delete('/admin/ai-metrics/delete/11111111-1111-1111-1111-111111111111');
        $this->assertRedirect('http://localhost:8080/admin/ai-metrics');
        $this->assertFlashMessage('The ai metric has been deleted.');

        // Verify record was deleted
        $table = $this->getTableLocator()->get('AiMetrics');
        $this->expectException('Cake\Datasource\Exception\RecordNotFoundException');
        $table->get('11111111-1111-1111-1111-111111111111');
    }
}
