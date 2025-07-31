<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppControllerTestCase;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\Admin\SlugsController Test Case
 *
 * @uses \App\Controller\Admin\SlugsController
 */
class SlugsControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Articles',
        'app.Products',
        'app.Slugs',
        'app.Tags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Login as admin user
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);
    }

    /**
     * Data provider for model configurations
     *
     * @return array
     */
    public function modelProvider(): array
    {
        return [
            'Articles' => [
                'model' => 'Articles',
                'foreignKey' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'existingSlugId' => '1e6c7b88-283d-43df-bfa3-fa33d4319f75',
                'existingSlug' => 'article-one',
                'newSlug' => 'new-article-slug',
                'searchTerm' => 'article',
            ],
            'Products' => [
                'model' => 'Products',
                'foreignKey' => 'prod-001-usb-c-cable',
                'existingSlugId' => '2f7d8c99-394e-54ef-cga4-gb44e5430g86', // Update with actual fixture ID
                'existingSlug' => 'product-one',
                'newSlug' => 'new-product-slug',
                'searchTerm' => 'product',
            ],
        ];
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->get('/admin/slugs');
        $this->assertResponseOk();
        $this->assertResponseContains('Slugs');

        // Test model type filtering for both Articles and Products
        $this->get('/admin/slugs?status=Articles');
        $this->assertResponseOk();

        $this->get('/admin/slugs?status=Products');
        $this->assertResponseOk();

        // Test search functionality for both models
        $this->get('/admin/slugs?search=article-one');
        $this->assertResponseOk();

        $this->get('/admin/slugs?search=product-one');
        $this->assertResponseOk();

        // Test AJAX request
        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);
        $this->get('/admin/slugs?search=article');
        $this->assertResponseOk();

        $this->get('/admin/slugs?search=product');
        $this->assertResponseOk();
    }

    /**
     * Test view method with both models
     *
     * @dataProvider modelProvider
     * @param string $model
     * @param string $foreignKey
     * @param string $existingSlugId
     * @param string $existingSlug
     * @return void
     */
    public function testView(string $model, string $foreignKey, string $existingSlugId, string $existingSlug): void
    {
        $this->get('/admin/slugs/view/' . $existingSlugId);
        $this->assertResponseOk();
        $this->assertResponseContains($existingSlug);
        $this->assertResponseContains($model);
    }

    /**
     * Test add method with both models
     *
     * @dataProvider modelProvider
     * @param string $model
     * @param string $foreignKey
     * @param string $existingSlugId
     * @param string $existingSlug
     * @param string $newSlug
     * @return void
     */
    public function testAdd(string $model, string $foreignKey, string $existingSlugId, string $existingSlug, string $newSlug): void
    {
        $this->enableCsrfToken();

        // Test GET request
        $this->get('/admin/slugs/add');
        $this->assertResponseOk();

        // Test POST request with valid data for current model
        $this->post('/admin/slugs/add', [
            'model' => $model,
            'foreign_key' => $foreignKey,
            'slug' => $newSlug,
        ]);

        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The slug has been saved.');
    }

    /**
     * Test add method with invalid data
     *
     * @return void
     */
    public function testAddWithInvalidData(): void
    {
        $this->enableCsrfToken();

        // Test POST request with invalid data
        $this->post('/admin/slugs/add', [
            'model' => '',
            'foreign_key' => '',
            'slug' => '',
        ]);

        $this->assertResponseOk(); // Form should re-render
        $this->assertResponseContains('The slug could not be saved. Please, try again.');
    }

    /**
     * Test edit method with both models
     *
     * @dataProvider modelProvider
     * @param string $model
     * @param string $foreignKey
     * @param string $existingSlugId
     * @param string $existingSlug
     * @param string $newSlug
     * @return void
     */
    public function testEdit(string $model, string $foreignKey, string $existingSlugId, string $existingSlug, string $newSlug): void
    {
        $this->enableCsrfToken();

        // Test GET request
        $this->get('/admin/slugs/edit/' . $existingSlugId);
        $this->assertResponseOk();

        // Test POST request with valid data
        $this->post('/admin/slugs/edit/' . $existingSlugId, [
            'model' => $model,
            'foreign_key' => $foreignKey,
            'slug' => 'updated-' . $newSlug,
        ]);

        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The slug has been saved.');
    }

    /**
     * Test edit method with invalid data
     *
     * @return void
     */
    public function testEditWithInvalidData(): void
    {
        $this->enableCsrfToken();
        $existingSlugId = '1e6c7b88-283d-43df-bfa3-fa33d4319f75';

        // Test POST request with invalid data
        $this->post('/admin/slugs/edit/' . $existingSlugId, [
            'model' => '',
            'foreign_key' => '',
            'slug' => '',
        ]);

        $this->assertResponseOk(); // Form should re-render
        $this->assertResponseContains('The slug could not be saved. Please, try again.');

        // Test with non-existent ID
        $this->get('/admin/slugs/edit/non-existent-id');
        $this->assertResponseError();
    }

    /**
     * Test delete method with both models
     *
     * @dataProvider modelProvider
     * @param string $model
     * @param string $foreignKey
     * @param string $existingSlugId
     * @return void
     */
    public function testDelete(string $model, string $foreignKey, string $existingSlugId): void
    {
        $this->enableCsrfToken();

        // Test successful delete
        $this->delete('/admin/slugs/delete/' . $existingSlugId);
        $this->assertRedirect();
        $this->assertFlashMessage('The slug has been deleted.');
    }

    /**
     * Test delete method with invalid scenarios
     *
     * @return void
     */
    public function testDeleteWithInvalidScenarios(): void
    {
        $this->enableCsrfToken();

        // Test delete with non-existent ID
        $this->delete('/admin/slugs/delete/non-existent-id');
        $this->assertResponseError();

        // Test with GET request (should fail)
        $this->get('/admin/slugs/delete/1e6c7b88-283d-43df-bfa3-fa33d4319f75');
        $this->assertResponseError();
    }

    /**
     * Test cross-model functionality
     *
     * @return void
     */
    public function testCrossModelFunctionality(): void
    {
        $this->enableCsrfToken();

        // Test creating same slug for different models (should be allowed)
        $sameslug = 'shared-slug-test';

        // Create Article slug
        $this->post('/admin/slugs/add', [
            'model' => 'Articles',
            'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
            'slug' => $sameslug,
        ]);
        $this->assertRedirect(['action' => 'index']);

        // Create Product slug with same name (should succeed)
        $this->post('/admin/slugs/add', [
            'model' => 'Products',
            'foreign_key' => 'prod-001-usb-c-cable',
            'slug' => $sameslug,
        ]);
        $this->assertRedirect(['action' => 'index']);

        // Test filtering by model type
        $this->get('/admin/slugs?status=Articles&search=' . $sameslug);
        $this->assertResponseOk();
        $this->assertResponseContains($sameslug);

        $this->get('/admin/slugs?status=Products&search=' . $sameslug);
        $this->assertResponseOk();
        $this->assertResponseContains($sameslug);
    }

    /**
     * Test bulk operations across models
     *
     * @return void
    */
    public function testBulkOperationsAcrossModels(): void
    {
        // Test index with combined search across models
        $this->get('/admin/slugs?search=test');
        $this->assertResponseOk();

        // Test pagination with mixed model results
        $this->get('/admin/slugs?page=1&limit=10');
        $this->assertResponseOk();

        // Test sorting by model
        $this->get('/admin/slugs?sort=model&direction=asc');
        $this->assertResponseOk();
    }

    /**
     * Test access control
     *
     * @return void
     */
    public function testAccessControl(): void
    {
        // Logout
        $this->session(['Auth' => null]);

        // Test access to various actions
        $this->get('/admin/slugs');
        $this->assertResponseCode(302);
        $this->assertFlashMessage('Access denied. You must be logged in as an admin to view this page.', 'flash');

        $this->get('/admin/slugs/add');
        $this->assertResponseCode(302);
        $this->assertFlashMessage('Access denied. You must be logged in as an admin to view this page.', 'flash');

        $this->get('/admin/slugs/edit/1e6c7b88-283d-43df-bfa3-fa33d4319f75');
        $this->assertResponseCode(302);
        $this->assertFlashMessage('Access denied. You must be logged in as an admin to view this page.', 'flash');

        $this->delete('/admin/slugs/delete/1e6c7b88-283d-43df-bfa3-fa33d4319f75');
        $this->assertResponseCode(403);
    }
}