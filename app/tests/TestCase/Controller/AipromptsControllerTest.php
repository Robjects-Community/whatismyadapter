<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\AipromptsTable;
use App\Test\TestCase\AppControllerTestCase;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\AipromptsController Test Case
 */
class AipromptsControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Aiprompts',
        'app.Users',
        'app.Settings',
    ];

    /**
     * @var \App\Model\Table\AipromptsTable
     */
    protected AipromptsTable $Aiprompts;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Aiprompts = TableRegistry::getTableLocator()->get('Aiprompts');
        $this->disableErrorHandlerMiddleware();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Aiprompts);
        parent::tearDown();
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user

        $this->get('/admin/aiprompts');
        //$this->assertResponseOk();
        $this->assertResponseContains('AI Prompts');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user
        $aiprompt = $this->Aiprompts->find()->first();
        $this->get('/admin/aiprompts/view/' . $aiprompt->id);
        $this->assertResponseOk();
        $this->assertResponseContains($aiprompt->task_type);
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user
        $this->enableCsrfToken();
        $data = [
            'task_type' => 'new_task',
            'system_prompt' => 'This is a new system prompt',
            'model' => 'gpt-4',
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ];
        $this->post('/admin/aiprompts/add', $data);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The aiprompt has been saved.', 'Flash.flash.0.message');

        $query = $this->Aiprompts->find()->where(['task_type' => 'new_task']);
        $this->assertEquals(1, $query->count());
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user
        $this->enableCsrfToken();
        $aiprompt = $this->Aiprompts->find()->first();
        $data = [
            'task_type' => 'updated_task',
            'system_prompt' => 'This is an updated system prompt',
        ];
        $this->post('/admin/aiprompts/edit/' . $aiprompt->id, $data);
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession('The aiprompt has been saved.', 'Flash.flash.0.message');

        $updatedAiprompt = $this->Aiprompts->get($aiprompt->id);
        $this->assertEquals('updated_task', $updatedAiprompt->task_type);
    }

    /**
     * Test access control
     *
     * @return void
     */
    public function testAccessControl(): void
    {
        // Test unauthenticated access
        $this->get('/admin/aiprompts');
        $this->assertResponseCode(302);
        $this->assertFlashMessage('Access denied. You must be logged in as an admin to view this page.', 'flash');

        // Test non-admin access
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d02'); // Non-admin user
        $this->get('/admin/aiprompts');
        $this->assertResponseCode(302);
        $this->assertFlashMessage('Access denied. You must be logged in as an admin to view this page.', 'flash');
    }

    /**
     * Test validation errors
     *
     * @return void
     */
    public function testValidationErrors(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user
        $this->enableCsrfToken();
        $data = [
            'task_type' => '', // Empty task_type should fail validation
            'system_prompt' => 'This is a new system prompt',
        ];
        $this->post('/admin/aiprompts/add', $data);
        $this->assertResponseContains('The aiprompt could not be saved');
    }
}
