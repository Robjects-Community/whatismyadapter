<?php
declare(strict_types=1);

namespace App\Test\TestCase\View\Cell;

use App\View\Cell\GalleryCell;
use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;

/**
 * App\View\Cell\GalleryCell Test Case
 */
class GalleryCellTest extends TestCase
{
    /**
     * Request mock
     *
     * @var \Cake\Http\ServerRequest|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * Response mock
     *
     * @var \Cake\Http\Response|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * Test fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ImageGalleries',
        'app.Images',
        'app.ImageGalleriesImages',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = $this->getMockBuilder('Cake\Http\ServerRequest')->getMock();
        $this->response = $this->getMockBuilder('Cake\Http\Response')->getMock();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->request, $this->response);
        parent::tearDown();
    }

    /**
     * Test display method with published gallery
     *
     * @return void
     */
    public function testDisplayPublishedGallery(): void
    {
        $cell = new GalleryCell($this->request, $this->response);

        // Test with published gallery from fixture
        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea';

        // Ensure gallery is published (in case other tests changed it)
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->get($galleryId);
        $gallery->is_published = true;
        $galleries->save($gallery);

        // Clear cache to ensure fresh data
        Cache::clear('default');

        $cell->display($galleryId, 'default', 'Test Gallery');

        $viewVars = $cell->viewBuilder()->getVars();

        $this->assertFalse($viewVars['isEmpty'], 'Gallery should not be empty');
        $this->assertNotNull($viewVars['gallery'], 'Gallery should be loaded');
        $this->assertNotEmpty($viewVars['images'], 'Gallery should have images');
        $this->assertCount(2, $viewVars['images'], 'Gallery should have 2 images from fixtures');
        $this->assertEquals('default', $viewVars['theme'], 'Theme should be set correctly');
    }

    /**
     * Test display method with admin theme (should show unpublished galleries)
     *
     * @return void
     */
    public function testDisplayAdminTheme(): void
    {
        $cell = new GalleryCell($this->request, $this->response);

        // First, make the gallery unpublished
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->get('32cf930e-1456-4cf9-ab9e-a7db7250b1ea');
        $originalStatus = $gallery->is_published;
        $gallery->is_published = false;
        $galleries->save($gallery);

        // Test with admin theme (should still show unpublished gallery)
        $cell->display('32cf930e-1456-4cf9-ab9e-a7db7250b1ea', 'admin', 'Test Gallery');

        $viewVars = $cell->viewBuilder()->getVars();

        $this->assertFalse($viewVars['isEmpty'], 'Admin should see unpublished galleries');
        $this->assertNotNull($viewVars['gallery'], 'Gallery should be loaded in admin mode');
        $this->assertEquals('admin', $viewVars['theme'], 'Theme should be admin');

        // Restore original status for other tests
        $gallery->is_published = $originalStatus;
        $galleries->save($gallery);
    }

    /**
     * Test display method with default theme and unpublished gallery
     *
     * @return void
     */
    public function testDisplayUnpublishedGalleryDefaultTheme(): void
    {
        $cell = new GalleryCell($this->request, $this->response);

        // Make the gallery unpublished and clear cache
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->get('32cf930e-1456-4cf9-ab9e-a7db7250b1ea');
        $originalStatus = $gallery->is_published;
        $gallery->is_published = false;
        $galleries->save($gallery);

        // Clear cache to ensure fresh data
        Cache::clear('default');

        // Test with default theme (should not show unpublished gallery)
        $cell->display('32cf930e-1456-4cf9-ab9e-a7db7250b1ea', 'default', 'Test Gallery');

        $viewVars = $cell->viewBuilder()->getVars();

        $this->assertTrue($viewVars['isEmpty'], 'Default theme should not show unpublished galleries');
        $this->assertNull($viewVars['gallery'], 'Gallery should not be loaded for unpublished in default theme');

        // Restore original status for other tests
        $gallery->is_published = $originalStatus;
        $galleries->save($gallery);
    }

    /**
     * Test display method with non-existent gallery
     *
     * @return void
     */
    public function testDisplayNonExistentGallery(): void
    {
        $cell = new GalleryCell($this->request, $this->response);

        $cell->display('00000000-0000-0000-0000-000000000000', 'default', 'Non-existent Gallery');

        $viewVars = $cell->viewBuilder()->getVars();

        $this->assertTrue($viewVars['isEmpty'], 'Non-existent gallery should be marked as empty');
        $this->assertNull($viewVars['gallery'], 'Non-existent gallery should be null');
    }

    /**
     * Test that the cell works with galleries that have images with actual filenames
     * This should catch the bug where images with empty filenames were causing issues
     *
     * @return void
     */
    public function testDisplayGalleryWithValidImages(): void
    {
        $cell = new GalleryCell($this->request, $this->response);

        $galleryId = '32cf930e-1456-4cf9-ab9e-a7db7250b1ea';

        // Ensure gallery is published (in case other tests changed it)
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->get($galleryId);
        $gallery->is_published = true;
        $galleries->save($gallery);

        // Clear cache to ensure fresh data
        Cache::clear('default');

        $cell->display($galleryId, 'default', 'Test Gallery');

        $viewVars = $cell->viewBuilder()->getVars();

        $this->assertFalse($viewVars['isEmpty'], 'Gallery with valid images should not be empty');
        $this->assertNotEmpty($viewVars['images'], 'Should have images');

        // Verify all images have actual filenames (not empty)
        foreach ($viewVars['images'] as $image) {
            $this->assertNotEmpty($image->image, 'Each image should have a filename');
            $this->assertStringContainsString('.', $image->image, 'Image filename should have extension');
        }
    }

    /**
     * Test error handling when gallery table operations fail
     * This would catch issues like missing LogTrait dependencies
     *
     * @return void
     */
    public function testErrorHandling(): void
    {
        $cell = new GalleryCell($this->request, $this->response);

        // Test with a malformed gallery ID that might cause database errors
        $cell->display('invalid-gallery-id', 'default', 'Error Test');

        $viewVars = $cell->viewBuilder()->getVars();

        // Should handle errors gracefully and set isEmpty = true
        $this->assertTrue($viewVars['isEmpty'], 'Invalid gallery ID should result in empty state');
        $this->assertNull($viewVars['gallery'], 'Invalid gallery should be null');

        // Error information may or may not be set depending on how the error occurs
        // The important thing is that it handles errors gracefully
        $this->assertTrue(true, 'Error was handled gracefully without exceptions');
    }
}
