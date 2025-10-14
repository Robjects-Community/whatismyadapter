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
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->get('/admin/slugs');
        $this->assertResponseOk();
        $this->assertResponseContains('Slugs');

        // Test model type filtering
        $this->get('/admin/slugs?status=Articles');
        $this->assertResponseOk();

        // Test search functionality
        $this->get('/admin/slugs?search=article-one');
        $this->assertResponseOk();

        // Test AJAX request
        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);
        $this->get('/admin/slugs?search=article');
        $this->assertResponseOk();
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $this->get('/admin/slugs/view/1e6c7b88-283d-43df-bfa3-fa33d4319f75');
        $this->assertResponseOk();
        $this->assertResponseContains('article-one');
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->enableCsrfToken();

        // Test GET request
        $this->get('/admin/slugs/add');
        $this->assertResponseOk();

        // Test POST request with valid data
        $this->post('/admin/slugs/add', [
            'model' => 'Articles',
            'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
            'slug' => 'new-test-slug',
        ]);

        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The slug has been saved.');

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
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void
    {
        $this->enableCsrfToken();

        // Test GET request
        $this->get('/admin/slugs/edit/1e6c7b88-283d-43df-bfa3-fa33d4319f75');
        $this->assertResponseOk();

        // Test POST request with valid data
        $this->post('/admin/slugs/edit/1e6c7b88-283d-43df-bfa3-fa33d4319f75', [
            'model' => 'Articles',
            'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
            'slug' => 'updated-test-slug',
        ]);

        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The slug has been saved.');

        // Test POST request with invalid data
        $this->post('/admin/slugs/edit/1e6c7b88-283d-43df-bfa3-fa33d4319f75', [
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
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->enableCsrfToken();

        // Test successful delete
        $this->delete('/admin/slugs/delete/1e6c7b88-283d-43df-bfa3-fa33d4319f75');
        $this->assertRedirect();
        $this->assertFlashMessage('The slug has been deleted.');

        // Test delete with non-existent ID
        $this->delete('/admin/slugs/delete/non-existent-id');
        $this->assertResponseError();

        // Test with GET request (should fail)
        $this->get('/admin/slugs/delete/1e6c7b88-283d-43df-bfa3-fa33d4319f75');
        $this->assertResponseError();
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
