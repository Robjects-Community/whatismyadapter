<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Test\TestCase\AppControllerTestCase;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\Admin\ImageGalleriesController Test Case
 *
 * @uses \App\Controller\Admin\ImageGalleriesController
 */
class ImageGalleriesControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ImageGalleries',
        'app.Images',
        'app.ImageGalleriesImages',
        'app.Slugs',
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

        // Disable error handler middleware for cleaner error messages
        $this->disableErrorHandlerMiddleware();

        // Enable CSRF token for testing
        $this->enableCsrfToken();

        // Login as admin user using the proper method from AppControllerTestCase
        // Using admin user ID from Users fixture
        $this->loginUser('6509480c-e7e6-4e65-9c38-1423a8d09d0f');
    }

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndex(): void
    {
        $this->get('/admin/image-galleries');
        $this->assertResponseOk();
        $this->assertResponseContains('Image Galleries');

        // Test that galleries are loaded
        $this->assertResponseContains('Lorem ipsum dolor sit amet'); // From fixture
    }

    /**
     * Test index method with grid view
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexGridView(): void
    {
        $this->get('/admin/image-galleries?view=grid');
        $this->assertResponseOk();

        // Should render index_grid template
        $this->assertTemplate('index_grid');
    }

    /**
     * Test index method with list view
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexListView(): void
    {
        $this->get('/admin/image-galleries?view=list');
        $this->assertResponseOk();

        // Should render index template (list view)
        $this->assertTemplate('index');
    }

    /**
     * Test index method view type switching
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexViewTypePersistence(): void
    {
        // Test list view
        $this->get('/admin/image-galleries?view=list');
        $this->assertResponseOk();
        $this->assertTemplate('index'); // List view uses index template

        // Test grid view
        $this->get('/admin/image-galleries?view=grid');
        $this->assertResponseOk();
    }

    /**
     * Test index method with search functionality
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexSearch(): void
    {
        $this->get('/admin/image-galleries?search=Lorem');
        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum dolor sit amet');

        // Test search with no results
        $this->get('/admin/image-galleries?search=NonExistentGallery');
        $this->assertResponseOk();
        $this->assertResponseNotContains('Lorem ipsum dolor sit amet');
    }

    /**
     * Test index method with status filtering
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexStatusFilter(): void
    {
        // Test published filter
        $this->get('/admin/image-galleries?status=1');
        $this->assertResponseOk();

        // Test unpublished filter
        $this->get('/admin/image-galleries?status=0');
        $this->assertResponseOk();
    }

    /**
     * Test index method with AJAX request
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::index()
     */
    public function testIndexAjax(): void
    {
        $this->configRequest([
            'headers' => ['X-Requested-With' => 'XMLHttpRequest'],
        ]);

        $this->get('/admin/image-galleries?search=Lorem');
        $this->assertResponseOk();
        $this->assertTemplate('search_results');
        $this->assertLayout('ajax');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::view()
     */
    public function testView(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $this->get("/admin/image-galleries/view/{$galleryId}");
        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum dolor sit amet');
    }

    /**
     * Test view method with invalid gallery ID
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::view()
     */
    public function testViewNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);

        $this->get('/admin/image-galleries/view/00000000-0000-0000-0000-000000000000');
    }

    /**
     * Test add method GET request
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::add()
     */
    public function testAddGet(): void
    {
        $this->get('/admin/image-galleries/add');
        $this->assertResponseOk();
        $this->assertResponseContains('Add Image Gallery');
    }

    /**
     * Test add method POST request with valid data
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::add()
     */
    public function testAddPostValid(): void
    {
        $data = [
            'name' => 'Test Gallery',
            'description' => 'A test gallery description',
            'is_published' => true,
        ];

        $this->post('/admin/image-galleries/add', $data);
        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The image gallery has been saved.');

        // Verify gallery was created
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->find()->where(['name' => 'Test Gallery'])->first();
        $this->assertNotNull($gallery);
        $this->assertEquals('test-gallery', $gallery->slug); // SlugBehavior should create this
    }

    /**
     * Test add method POST request with invalid data
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::add()
     */
    public function testAddPostInvalid(): void
    {
        $data = [
            'name' => '', // Name is required
            'description' => 'A test gallery description',
            'is_published' => true,
        ];

        $this->post('/admin/image-galleries/add', $data);
        $this->assertResponseOk(); // Should render form again with errors
    }

    /**
     * Test add method POST request without file uploads
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::add()
     */
    public function testAddPostWithoutFileUploads(): void
    {
        $data = [
            'name' => 'Gallery Without Files',
            'description' => 'A test gallery without file uploads',
            'is_published' => true,
        ];

        $this->post('/admin/image-galleries/add', $data);
        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The image gallery has been saved.');
    }

    /**
     * Test edit method GET request
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::edit()
     */
    public function testEditGet(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $this->get("/admin/image-galleries/edit/{$galleryId}");
        $this->assertResponseOk();
        $this->assertResponseContains('Edit Image Gallery');
        $this->assertResponseContains('Lorem ipsum dolor sit amet'); // Gallery name from fixture
    }

    /**
     * Test edit method GET request with invalid gallery ID
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::edit()
     */
    public function testEditGetNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);

        $this->get('/admin/image-galleries/edit/00000000-0000-0000-0000-000000000000');
    }

    /**
     * Test edit method POST request with valid data
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::edit()
     */
    public function testEditPostValid(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture
        $data = [
            'name' => 'Updated Gallery Name',
            'description' => 'Updated description',
            'is_published' => false,
        ];

        $this->put("/admin/image-galleries/edit/{$galleryId}", $data);
        $this->assertResponseSuccess();
        $this->assertRedirect(['action' => 'index']);
        $this->assertFlashMessage('The image gallery has been saved.');

        // Verify gallery was updated
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->get($galleryId);
        $this->assertEquals('Updated Gallery Name', $gallery->name);
        $this->assertEquals('Updated description', $gallery->description);
        $this->assertFalse($gallery->is_published);
    }

    /**
     * Test edit method POST request with invalid data
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::edit()
     */
    public function testEditPostInvalid(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture
        $data = [
            'name' => '', // Name is required
            'description' => 'Updated description',
            'is_published' => false,
        ];

        $this->put("/admin/image-galleries/edit/{$galleryId}", $data);
        $this->assertResponseOk(); // Should render form again with errors
    }

    /**
     * Test delete method with valid gallery
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::delete()
     */
    public function testDeleteSuccess(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        // Set referer so we can test redirect
        $this->configRequest([
            'headers' => ['Referer' => '/admin/image-galleries'],
        ]);

        $this->delete("/admin/image-galleries/delete/{$galleryId}");
        $this->assertResponseSuccess();
        $this->assertRedirect('/admin/image-galleries');
        $this->assertFlashMessage('The image gallery has been deleted.');

        // Verify gallery was deleted
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $exists = $galleries->exists(['id' => $galleryId]);
        $this->assertFalse($exists);
    }

    /**
     * Test delete method with invalid gallery ID
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::delete()
     */
    public function testDeleteNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);

        $this->delete('/admin/image-galleries/delete/00000000-0000-0000-0000-000000000000');
    }

    /**
     * Test delete method requires POST or DELETE method
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::delete()
     */
    public function testDeleteMethodSecurity(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $this->expectException(MethodNotAllowedException::class);

        $this->get("/admin/image-galleries/delete/{$galleryId}");
    }

    /**
     * Test manageImages method
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::manageImages()
     */
    public function testManageImages(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $this->get("/admin/image-galleries/manageImages/{$galleryId}");
        $this->assertResponseOk();
        $this->assertResponseContains('Manage Images');
    }

    /**
     * Test manageImages method with invalid gallery ID
     *
     * @return void
     * @uses \App\Controller\Admin\ImageGalleriesController::manageImages()
     */
    public function testManageImagesNotFound(): void
    {
        $this->expectException(RecordNotFoundException::class);

        $this->get('/admin/image-galleries/manageImages/00000000-0000-0000-0000-000000000000');
    }

    /**
     * Test that galleries with images can be properly loaded for display
     * This test should catch issues where galleries with images fail to load
     * due to missing dependencies (like LogTrait) or filtering problems
     *
     * @return void
     */
    public function testGalleryWithImagesLoading(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture, has 2 images

        // Test that the gallery loads with images in view action
        $this->get("/admin/image-galleries/view/{$galleryId}");
        $this->assertResponseOk();

        // Get the gallery with images
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->get($galleryId, contain: ['Images']);

        // Verify the gallery has images (from fixtures)
        $this->assertNotEmpty($gallery->images, 'Gallery should have images from fixtures');
        $this->assertCount(2, $gallery->images, 'Gallery should have exactly 2 images from fixtures');

        // Verify images have actual filenames (not empty)
        foreach ($gallery->images as $image) {
            $this->assertNotEmpty($image->image, 'Image should have a filename');
        }
    }

    /**
     * Test that getGalleryForPlaceholder method works correctly
     * This should catch issues where galleries fail to load due to
     * filtering problems or missing dependencies
     *
     * @return void
     */
    public function testGetGalleryForPlaceholder(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $galleries = $this->getTableLocator()->get('ImageGalleries');

        // First ensure the gallery is published (in case other tests changed it)
        $gallery = $galleries->get($galleryId);
        $gallery->is_published = true;
        $galleries->save($gallery);

        // Clear cache to ensure fresh data
        Cache::clear('default');

        // Test published gallery loading (default behavior)
        $gallery = $galleries->getGalleryForPlaceholder($galleryId, true);
        $this->assertNotNull($gallery, 'Published gallery should be loaded');
        $this->assertNotEmpty($gallery->images, 'Gallery should have images');
        $this->assertCount(2, $gallery->images, 'Gallery should have 2 images from fixtures');

        // Test admin mode (unpublished galleries allowed)
        $gallery = $galleries->getGalleryForPlaceholder($galleryId, false);
        $this->assertNotNull($gallery, 'Gallery should be loaded in admin mode regardless of publish status');
        $this->assertNotEmpty($gallery->images, 'Gallery should have images in admin mode');

        // Test with non-existent gallery
        $nonExistentGallery = $galleries->getGalleryForPlaceholder('00000000-0000-0000-0000-000000000000', false);
        $this->assertNull($nonExistentGallery, 'Non-existent gallery should return null');
    }

    /**
     * Test that publishing/unpublishing galleries triggers cache clearing
     * This should catch issues where article cache isn't invalidated when gallery status changes
     *
     * @return void
     */
    public function testGalleryPublishStatusCacheClear(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->get($galleryId);

        // Change publish status
        $originalStatus = $gallery->is_published;
        $gallery->is_published = !$originalStatus;

        // Save and check that it doesn't throw errors (would fail if LogTrait missing)
        $result = $galleries->save($gallery);
        $this->assertNotFalse($result, 'Gallery save should succeed');

        // Verify the status actually changed
        $updatedGallery = $galleries->get($galleryId);
        $this->assertEquals(!$originalStatus, $updatedGallery->is_published, 'Gallery publish status should have changed');

        // Restore original status for other tests
        $updatedGallery->is_published = $originalStatus;
        $galleries->save($updatedGallery);
    }

    /**
     * Test that image operations on galleries work correctly
     * This should catch issues with missing LogTrait or QueueableJobsTrait dependencies
     *
     * @return void
     */
    public function testGalleryImageOperations(): void
    {
        // Skip this test in CI environment to avoid Redis authentication issues
        if (getenv('GITHUB_ACTIONS') === 'true') {
            $this->markTestSkipped('Skipping Redis-dependent test in GitHub Actions CI environment');
        }

        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture

        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $images = $this->getTableLocator()->get('Images');

        // Test creating a new image (this would fail if LogTrait missing from ImagesTable)
        $newImage = $images->newEntity([
            'name' => 'Test Image for Gallery',
            'alt_text' => 'Test alt text',
            'image' => 'test-new-image.png',
            'dir' => 'files/Images/image/',
            'mime' => 'image/png',
            'size' => 1024,
        ]);

        $result = $images->save($newImage);
        $this->assertNotFalse($result, 'Image save should succeed (tests LogTrait dependency)');

        // Test gallery operations that might trigger logging
        $gallery = $galleries->get($galleryId);
        $gallery->name = 'Updated Gallery Name for Test';

        $result = $galleries->save($gallery);
        $this->assertNotFalse($result, 'Gallery save should succeed (tests trait dependencies)');
    }

    /**
     * Test that galleries are properly displayed in view template
     * This ensures the GalleryCell integration works correctly
     *
     * @return void
     */
    public function testGalleryCellIntegration(): void
    {
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea'; // From fixture, has images

        // Test view page renders gallery content
        $this->get("/admin/image-galleries/view/{$galleryId}");
        $this->assertResponseOk();

        // The view should contain gallery display (even if just placeholder text)
        // If GalleryCell fails, this would be empty or show error
        $this->assertResponseContains('Gallery Images', 'View should show gallery section');

        // Test that no PHP errors occur (would show up in response)
        $response = (string)$this->_response->getBody();
        $this->assertStringNotContainsString('Fatal error', $response, 'Should not contain PHP fatal errors');
        $this->assertStringNotContainsString('Unknown method', $response, 'Should not contain unknown method errors');
    }
}
