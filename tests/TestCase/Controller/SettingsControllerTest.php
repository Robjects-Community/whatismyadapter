<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppControllerTestCase;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\Admin\SettingsController Test Case
 *
 * @uses \App\Controller\Admin\SettingsController
 */
class SettingsControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Settings',
        'app.Users',
    ];

    /**
     * Setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->disableErrorHandlerMiddleware();

        $this->configRequest([
            'environment' => [
                'AUTH_TYPE' => 'Form',
            ],
        ]);

        // Clear rate limiting cache
        Cache::clear('rate_limit');
    }

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\Admin\SettingsController::index()
     */
    public function testIndex(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f'; // Assuming this is a valid admin ID in your fixtures
        $this->loginUser($adminId);

        $this->get('/admin/settings');

        $this->assertResponseOk();
        $this->assertResponseContains('Settings');

        $viewVars = $this->viewVariable('groupedSettings');
        $this->assertNotEmpty($viewVars);
        $this->assertIsArray($viewVars);

        // Check for default keys
        $this->assertArrayHasKey('AI', $viewVars);
        $this->assertArrayHasKey('Email', $viewVars);
        $this->assertArrayHasKey('ImageSizes', $viewVars);
        $this->assertArrayHasKey('SEO', $viewVars);

        // Check for some key_names
        $this->assertArrayHasKey('value', $viewVars['AI']['anthropicApiKey']);
        $this->assertArrayHasKey('value', $viewVars['Email']['reply_email']);
        $this->assertArrayHasKey('value', $viewVars['ImageSizes']['extra-large']);
        $this->assertArrayHasKey('value', $viewVars['SEO']['siteStrapline']);

        // Check for some values
        $this->assertEquals('50', $viewVars['ImageSizes']['teeny']['value']);
        $this->assertEquals('500', $viewVars['ImageSizes']['extra-large']['value']);
    }

    /**
     * Test saveSettings method
     *
     * @return void
     * @uses \App\Controller\Admin\SettingsController::saveSettings()
     */
    public function testSaveSettings(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);

        $this->enableCsrfToken();
        $data = [
            'ImageSizes' => [
                'extra-large' => '501',
            ],
        ];

        $this->post('/admin/settings/saveSettings', $data);

        $this->assertResponseSuccess();
        $this->assertRedirect(['controller' => 'Settings', 'action' => 'index', 'prefix' => 'Admin']);
        $this->assertFlashMessage('The settings have been saved.');

        // Verify the settings were actually saved
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $setting = $settingsTable->find()->where(['category' => 'ImageSizes', 'key_name' => 'extra-large'])->first();
        $this->assertEquals('501', $setting->value);
    }

    /**
     * Test saveSettings method with invalid data
     *
     * @return void
     * @uses \App\Controller\Admin\SettingsController::saveSettings()
     */
    public function testSaveSettingsInvalidData(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f'; // Assuming this is a valid admin ID in your fixtures
        $this->loginUser($adminId);

        $this->enableCsrfToken();
        $data = [
            'NonExistentCategory' => [
                'non_existent_key' => 'Some Value',
            ],
        ];

        $this->post('/admin/settings/saveSettings', $data);

        $this->assertResponseSuccess();
        $this->assertRedirect(['controller' => 'Settings', 'action' => 'index', 'prefix' => 'Admin']);
        $this->assertFlashMessage('Some settings could not be saved. Please, try again.');
    }
}
