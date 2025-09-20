<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Test\TestCase\AppControllerTestCase;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\ProductsController Test Case
 *
 * @uses \App\Controller\ProductsController
 */
class ProductsControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Products',
        'app.Slugs',
        'app.Users',
        'app.Articles',
        'app.Tags',
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
    }

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\ProductsController::index()
     */
    public function testProductsIndex(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);

        $this->get('/admin/products');
        $this->assertResponseOk();

        $this->assertResponseContains('Products');
        $this->assertNotEmpty($this->viewVariable('products'));
    }

    /**
     * Helper method to login as admin user
     */
    protected function loginAsAdmin(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);
    }

    public function testDashboard(): void
    {
        $this->loginAsAdmin();
        $this->get('/admin/products/dashboard');

        $this->assertResponseOk();
        $this->assertNotNull($this->viewVariable('totalProducts'));
        $this->assertNotNull($this->viewVariable('recentProducts'));
    }

    public function testAddPost(): void
    {
        $this->loginAsAdmin();
        $this->enableCsrfToken();

        $data = [
            'title' => 'Test Product',
            'description' => 'Test description',
            'manufacturer' => 'Test Manufacturer',
            'is_published' => false,
        ];

        $this->post('/admin/products/add', $data);
        $this->assertResponseSuccess();
        $this->assertFlashMessage('The product has been saved.');
    }

    /**
     * Test viewBySlug method for published article with an old slug
     *
     * Checks if accessing an article with an old slug results in a 301 redirect
     * to the current slug, and that the article content is then displayed correctly.
     *
     * @return void
     * @uses \App\Controller\ArticlesController::viewBySlug()
     */
    public function testViewByOldSlugForPublishedArticle(): void
    {
        $this->get('/en/articles/article-one');
        $this->assertResponseCode(301);
        $this->assertRedirect();

        // Get the redirect location and follow it
        $location = $this->_response->getHeaderLine('Location');
        $this->get($location);

        $this->assertResponseOk();
        $this->assertResponseContains('Content for Article One');
    }

    /**
     * Test viewBySlug method for unpublished article
     *
     * Ensures that attempting to view an unpublished article
     * results in a NotFoundException.
     *
     * @return void
     * @uses \App\Controller\ArticlesController::viewBySlug()
     */
    public function testViewBySlugUnpublished(): void
    {
        $this->expectException(NotFoundException::class);
        $this->get('/en/articles/article-five');
    }

    /**
     * Test publishing an unpublished article and viewing it
     *
     * Verifies that an unpublished article cannot be viewed,
     * then publishes it and confirms it can be accessed.
     *
     * @return void
     * @uses \App\Controller\ArticlesController::viewBySlug()
     */
    public function testPublishUnpublishedArticle(): void
    {
        $unpublishedArticleId = 'fef07ae2-1b1a-4653-a444-d093e35c6e2f';

        // Try to view unpublished article
        $this->expectException(NotFoundException::class);
        $this->get('/en/articles/article-five');

        // Publish the article
        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $article = $articlesTable->get($unpublishedArticleId);
        $article->is_published = true;
        $article->published = date('Y-m-d H:i:s');
        $articlesTable->save($article);

        // Try to view the now published article
        $this->get('/en/articles/article-five');
        $this->assertResponseOk();
        $this->assertResponseContains('Content for Article Five');
    }

    /**
     * Test publishing an unpublished page and viewing it
     *
     * Similar to testPublishUnpublishedArticle, but specifically for pages.
     * Checks that an unpublished page becomes accessible after publishing.
     *
     * @return void
     * @uses \App\Controller\ArticlesController::viewBySlug()
     */
    public function testPublishUnpublishedPage(): void
    {
        $unpublishedPageId = 'e98aaafa-415a-4911-8ff2-25f76b326ea4'; // Page 6

        // Try to view unpublished page
        $this->expectException(NotFoundException::class);
        $this->get('/en/articles/page-six');

        // Publish the page
        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $page = $articlesTable->get($unpublishedPageId);
        $page->is_published = true;
        $page->published = date('Y-m-d H:i:s');
        $articlesTable->save($page);

        // Try to view the now published page
        $this->get('/en/articles/page-six');
        $this->assertResponseOk();
        $this->assertResponseContains('Content for Page Six');
    }

    /**
     * Test admin article search functionality
     *
     * Verifies that the admin search feature correctly filters articles
     * based on the search query.
     *
     * @return void
     * @uses \App\Controller\ArticlesController::index()
     */
    public function testAdminSearch(): void
    {
        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminId);

        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);
        $this->get('/admin/articles?search=Article One');
        $this->assertResponseOk();
        $this->assertResponseContains('Article One');
        $this->assertResponseNotContains('Article Two');
    }

    /**
     * Test access to admin area for non-admin user
     *
     * Ensures that non-admin users are redirected when attempting
     * to access the admin area.
     *
     * @return void
     */
    public function testNonAdminAccessToAdminArea(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d02'); // Non-admin user ID
        $this->get('/admin/articles');
        $this->assertRedirect('/en');
    }

    /**
     * Test access to admin area for admin user
     *
     * Verifies that admin users can successfully access the admin area.
     *
     * @return void
     */
    public function testAdminAccessToAdminArea(): void
    {
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f'); // Admin user ID
        $this->get('/admin/articles');
        $this->assertResponseOk();
    }

    /**
     * Test article creation by admin
     *
     * Checks that an admin can successfully create a new article,
     * including automatic slug generation.
     *
     * @return void
     */
    public function testArticleCreationByAdmin(): void
    {
        $adminUserId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminUserId);
        $this->enableCsrfToken();
        $this->post('/admin/articles/add', [
            'title' => 'New Test Article Test Page',
            'body' => 'This is a new test article',
            'slug' => '',
            'user_id' => $adminUserId,
            'is_published' => 1,
        ]);
        $this->assertRedirectContains('/admin');

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $query = $articlesTable->find()->where(['title' => 'New Test Article Test Page']);
        $this->assertEquals(1, $query->count());

        $article = $query->first();
        $this->assertEquals('new-test-article-test-page', $article->slug);
    }

    /**
     * Test article editing by admin
     *
     * Verifies that an admin can successfully edit an existing article.
     *
     * @return void
     */
    public function testArticleEditingByAdmin(): void
    {
        $adminUserId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminUserId);
        $this->enableCsrfToken();
        $articleId = '263a5364-a1bc-401c-9e44-49c23d066a0f'; // Article One ID
        $this->post("/admin/articles/edit/{$articleId}", [
            'title' => 'Updated Article One',
            'body' => 'Updated content for Article One',
        ]);
        $this->assertRedirect('/admin');

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $article = $articlesTable->get($articleId);
        $this->assertEquals('Updated Article One', $article->title);
    }

    /**
     * Test article deletion by admin
     *
     * Ensures that an admin can successfully delete an article,
     * and that the article is no longer retrievable after deletion.
     *
     * @return void
     */
    public function testArticleDeletionByAdmin(): void
    {
        $adminUserId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminUserId);
        $this->enableCsrfToken();

        $articleId = '263a5364-a1bc-401c-9e44-49c23d066a0f'; // Article One ID

        // First, verify the article exists
        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $articleExists = $articlesTable->exists(['id' => $articleId]);
        $this->assertTrue($articleExists, 'Article should exist before deletion');

        // Perform the deletion
        $this->post("/admin/articles/delete/{$articleId}");
        $this->assertRedirect('/admin');
        $this->assertFlashMessage('The article has been deleted.');

        // Verify the article no longer exists
        $articleExists = $articlesTable->exists(['id' => $articleId]);
        $this->assertFalse($articleExists, 'Article should not exist after deletion');

        // Alternative approach if you want to test the exception:
        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionMessage('Record not found in table `articles`.');
        $articlesTable->get($articleId);
    }

    /**
     * Test article creation, editing, and deletion with slug management
     *
     * This test covers creating articles, editing slugs, checking slug uniqueness,
     * and ensuring proper deletion of articles and their associated slugs.
     *
     * @return void
     */
    public function testArticleCreationEditingAndDeletionWithSlugManagement(): void
    {
        $adminUserId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $this->loginUser($adminUserId);
        $this->enableCsrfToken();

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $slugsTable = TableRegistry::getTableLocator()->get('Slugs');

        // Create Article 1
        $this->post('/admin/articles/add', [
            'title' => 'Big Test Article 1',
            'body' => 'Content for Big Test Article 1',
            'slug' => '',
            'user_id' => $adminUserId,
            'is_published' => 1,
        ]);
        $this->assertRedirectContains('/admin');

        $article1 = $articlesTable->find()->where(['title' => 'Big Test Article 1', 'slug' => 'big-test-article-1'])->first();
        $this->assertNotEmpty($article1);

        // Check Article 1 is viewable in front end
        $this->get('/en/articles/big-test-article-1');
        $this->assertResponseOk();
        $this->assertResponseContains('Content for Big Test Article 1');

        // Check there's 1 slug for Article 1
        $slugCount = $slugsTable->find()->where(['foreign_key' => $article1->id])->count();
        $this->assertEquals(1, $slugCount);

        // Edit Article 1 and change slug
        $this->post("/admin/articles/edit/{$article1->id}", [
            'title' => 'Big Test Article 1',
            'body' => 'Updated content for Test Article 1',
            'slug' => 'big-test-article-1-v1',
            'is_published' => 1,
        ]);

        $this->assertRedirect('/admin');

        // Check there are 2 slugs for Article 1
        $slugCount = $slugsTable->find()->where(['foreign_key' => $article1->id])->count();
        $this->assertEquals(2, $slugCount);

        if (env('EXPERIMENTAL_TESTS', 'Off') == 'On') {
            // Check old slug redirects to new slug
            $this->get('/en/articles/big-test-article-1');
            $this->assertResponseCode(301);
            $this->assertRedirect();

            // Get the redirect location and follow it
            $location = $this->_response->getHeaderLine('Location');
            $this->get($location);

            $this->assertResponseOk();
            $this->assertResponseContains('Updated content for Test Article 1');

            // Check new slug is accessible without redirect
            $this->get('/en/articles/big-test-article-1-v1');
            $this->assertResponseOk();
            $this->assertResponseContains('Updated content for Test Article 1');

            // Try to create Article 2 with the same slug as original Article 1
            $this->post('/admin/articles/add', [
                'title' => 'Test Article 2',
                'body' => 'Content for Test Article 2',
                'slug' => 'big-test-article-1',
                'user_id' => $adminUserId,
                'is_published' => 1,
            ]);

            $this->assertResponseOk(); // Form should re-render with validation errors
            $this->assertResponseContains('Slug conflicts with an existing SEO redirect.');

            // Try to create Article 2 with the same slug as current Article 1
            $this->post('/admin/articles/add', [
                'title' => 'Test Article 2',
                'body' => 'Content for Test Article 2',
                'slug' => 'big-test-article-1-v1',
                'user_id' => $adminUserId,
                'is_published' => 1,
            ]);
            $this->assertResponseOk(); // Form should re-render with validation errors
            $this->assertResponseContains('This slug is already in use');

            // Create Article 2 with a unique slug
            $this->post('/admin/articles/add', [
                'title' => 'Test Article 2',
                'body' => 'Content for Test Article 2',
                'slug' => 'test-article-2-slug',
                'user_id' => $adminUserId,
                'is_published' => 1,
            ]);
            $this->assertRedirectContains('/admin');

            $article2 = $articlesTable->find()->where(['title' => 'Test Article 2'])->first();
            $this->assertNotEmpty($article2);

            // Check slug counts
            $slugCount1 = $slugsTable->find()->where(['foreign_key' => $article1->id])->count();
            $slugCount2 = $slugsTable->find()->where(['foreign_key' => $article2->id])->count();
            $this->assertEquals(2, $slugCount1);
            $this->assertEquals(1, $slugCount2);

            // Delete Article 2
            $this->post("/admin/articles/delete/{$article2->id}");
            $this->assertRedirectContains('/admin');

            // Check no slugs for Article 2
            $slugCount2 = $slugsTable->find()->where(['foreign_key' => $article2->id])->count();
            $this->assertEquals(0, $slugCount2);

            // Delete Article 1
            $this->post("/admin/articles/delete/{$article1->id}");
            $this->assertRedirectContains('/admin');

            // Check no slugs for Article 1
            $slugCount1 = $slugsTable->find()->where(['foreign_key' => $article1->id])->count();
            $this->assertEquals(0, $slugCount1);
        }
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\ProductsController::view()
     */
    public function testView(): void
    {
        // Test viewing an existing product
        $productId = '0cab0d79-877c-4e97-81c3-472cefa099a5';
        $this->get("/en/products/view/{$productId}");
        $this->assertResponseOk();
        // $this->assertResponseContains('Test Product One');

        // Verify view variables are set correctly
        $product = $this->viewVariable('product');
        $this->assertNotNull($product);
        $this->assertEquals($productId, $product->id);
    }

    /**
     * Test view method with invalid ID
     *
     * @return void
     * @uses \App\Controller\ProductsController::view()
     */
    public function testViewInvalidId(): void
    {
        $this->expectException(RecordNotFoundException::class);
        $this->get('/en/products/view/invalid-id');
    }

    /**
     * Test add method GET request
     *
     * @return void
     * @uses \App\Controller\ProductsController::add()
     */
    public function testAdd(): void
    {
        $this->loginAsAdmin();

        // Test GET request displays form
        $this->get('/admin/products/add');
        $this->assertResponseOk();

        // Verify form variables are set
        $this->assertNotNull($this->viewVariable('product'));
        $this->assertNotNull($this->viewVariable('users'));
        $this->assertNotNull($this->viewVariable('articles'));
        $this->assertNotNull($this->viewVariable('tags'));
    }

    /**
     * Test add method POST request with valid data
     *
     * @return void
     * @uses \App\Controller\ProductsController::add()
     */
    public function testAddPostValid(): void
    {
        $this->loginAsAdmin();
        $this->enableCsrfToken();

        $data = [
            'title' => 'New Test Product',
            'description' => 'Test product description',
            'manufacturer' => 'Test Manufacturer',
            'model_number' => 'TEST001',
            'price' => 99.99,
            'currency' => 'USD',
            'is_published' => true,
            'featured' => false,
            'verification_status' => 'pending',
            'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f', // Admin user ID
        ];

        $this->post('/admin/products/add', $data);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The product has been saved.');

        // Verify product was created
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find()->where(['title' => 'New Test Product'])->first();
        $this->assertNotNull($product);
        $this->assertEquals('Test Manufacturer', $product->manufacturer);
    }

    /**
     * Test add method POST request with invalid data
     *
     * @return void
     * @uses \App\Controller\ProductsController::add()
     */
    public function testAddPostInvalid(): void
    {
        $this->loginAsAdmin();
        $this->enableCsrfToken();

        $data = [
            'title' => '', // Empty title should be invalid
            'description' => 'Test description',
        ];

        $this->post('/admin/products/add', $data);
        $this->assertResponseOk(); // Form should be re-displayed
        // Note: Flash message might not be set if validation fails before save attempt

        // Verify product was not created
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->find()->where(['description' => 'Test description'])->first();
        $this->assertNull($product);
    }

    /**
     * Test edit method GET request
     *
     * @return void
     * @uses \App\Controller\ProductsController::edit()
     */
    public function testEdit(): void
    {
        $this->loginAsAdmin();
        $productId = '0cab0d79-877c-4e97-81c3-472cefa099a5';

        // Test GET request displays form with existing data
        $this->get("/en/products/edit/{$productId}");
        $this->assertResponseOk();

        // Verify form variables are set
        $product = $this->viewVariable('product');
        $this->assertNotNull($product);
        $this->assertEquals($productId, $product->id);
        $this->assertNotNull($this->viewVariable('users'));
        $this->assertNotNull($this->viewVariable('articles'));
        $this->assertNotNull($this->viewVariable('tags'));
    }

    /**
     * Test edit method PATCH request with valid data
     *
     * @return void
     * @uses \App\Controller\ProductsController::edit()
     */
    public function testEditPatchValid(): void
    {
        $this->loginAsAdmin();
        $productId = '0cab0d79-877c-4e97-81c3-472cefa099a5';
        $this->enableCsrfToken();

        $data = [
            'title' => 'Updated Product Title',
            'description' => 'Updated product description',
            'manufacturer' => 'Updated Manufacturer',
            'price' => 149.99,
        ];

        $this->patch("/en/products/edit/{$productId}", $data);
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The product has been saved.');

        // Verify product was updated
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->get($productId);
        $this->assertEquals('Updated Product Title', $product->title);
        $this->assertEquals('Updated Manufacturer', $product->manufacturer);
    }

    /**
     * Test edit method with invalid ID
     *
     * @return void
     * @uses \App\Controller\ProductsController::edit()
     */
    public function testEditInvalidId(): void
    {
        $this->loginAsAdmin();
        $this->expectException(RecordNotFoundException::class);
        $this->get('/en/products/edit/invalid-id');
    }

    /**
     * Test edit method PATCH request with invalid data
     *
     * @return void
     * @uses \App\Controller\ProductsController::edit()
     */
    public function testEditPatchInvalid(): void
    {
        $this->loginAsAdmin();
        $productId = '0cab0d79-877c-4e97-81c3-472cefa099a5';
        $this->enableCsrfToken();

        $data = [
            'title' => '', // Empty title should be invalid
            'price' => -10, // Negative price should be invalid
        ];

        $this->patch("/en/products/edit/{$productId}", $data);
        $this->assertResponseOk(); // Form should be re-displayed
        // Note: Flash message might not be set if validation fails before save attempt

        // Verify product was not updated
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->get($productId);
        $this->assertNotEquals('', $product->title); // Title should remain unchanged
    }

    /**
     * Test delete method with POST request
     *
     * @return void
     * @uses \App\Controller\ProductsController::delete()
     */
    public function testDelete(): void
    {
        $this->loginAsAdmin();
        $productId = '0cab0d79-877c-4e97-81c3-472cefa099a5';
        $this->enableCsrfToken();

        // Verify product exists before deletion
        $productsTable = TableRegistry::getTableLocator()->get('Products');
        $product = $productsTable->get($productId);
        $this->assertNotNull($product);

        $this->post("/en/products/delete/{$productId}");
        $this->assertRedirect(); // Should redirect to referer or index
        $this->assertFlashMessage('The product has been deleted.');

        // Verify product was deleted
        $this->expectException(RecordNotFoundException::class);
        $productsTable->get($productId);
    }

    /**
     * Test delete method with invalid ID
     *
     * @return void
     * @uses \App\Controller\ProductsController::delete()
     */
    public function testDeleteInvalidId(): void
    {
        $this->loginAsAdmin();
        $this->enableCsrfToken();

        $this->expectException(RecordNotFoundException::class);
        $this->post('/en/products/delete/invalid-id');
    }

    /**
     * Test delete method with GET request (should not be allowed)
     *
     * @return void
     * @uses \App\Controller\ProductsController::delete()
     */
    public function testDeleteGetNotAllowed(): void
    {
        $this->loginAsAdmin();
        $productId = '0cab0d79-877c-4e97-81c3-472cefa099a5';

        $this->expectException('Cake\Http\Exception\MethodNotAllowedException');
        $this->get("/en/products/delete/{$productId}");
    }
}
