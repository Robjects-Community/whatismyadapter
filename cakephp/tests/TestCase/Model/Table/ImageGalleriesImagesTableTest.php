<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ImageGalleriesImagesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ImageGalleriesImagesTable Test Case
 */
class ImageGalleriesImagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ImageGalleriesImagesTable
     */
    protected $ImageGalleriesImages;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ImageGalleriesImages',
        'app.ImageGalleries',
        'app.Images',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ImageGalleriesImages') ? [] : ['className' => ImageGalleriesImagesTable::class];
        $this->ImageGalleriesImages = $this->getTableLocator()->get('ImageGalleriesImages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ImageGalleriesImages);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ImageGalleriesImagesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $galleryId = '123e4567-e89b-12d3-a456-426614174000';
        $imageId = '123e4567-e89b-12d3-a456-426614174001';

        // Test valid data
        $data = [
            'image_gallery_id' => $galleryId,
            'image_id' => $imageId,
            'position' => 0,
            'caption' => 'Test caption',
        ];
        $entity = $this->ImageGalleriesImages->newEntity($data);
        $this->assertEmpty($entity->getErrors());

        // Test image_gallery_id is required
        $data = [
            'image_id' => $imageId,
            'position' => 0,
        ];
        $entity = $this->ImageGalleriesImages->newEntity($data, ['validate' => true]);
        $this->assertArrayHasKey('image_gallery_id', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['image_gallery_id']);

        // Test image_id is required
        $data = [
            'image_gallery_id' => $galleryId,
            'position' => 0,
        ];
        $entity = $this->ImageGalleriesImages->newEntity($data, ['validate' => true]);
        $this->assertArrayHasKey('image_id', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['image_id']);

        // Test position is required
        $data = [
            'image_gallery_id' => $galleryId,
            'image_id' => $imageId,
        ];
        $entity = $this->ImageGalleriesImages->newEntity($data, ['validate' => true]);
        $this->assertArrayHasKey('position', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['position']);

        // Test invalid UUID for image_gallery_id
        $data = [
            'image_gallery_id' => 'not-a-uuid',
            'image_id' => $imageId,
            'position' => 0,
        ];
        $entity = $this->ImageGalleriesImages->newEntity($data);
        $this->assertArrayHasKey('image_gallery_id', $entity->getErrors());
        $this->assertArrayHasKey('uuid', $entity->getErrors()['image_gallery_id']);

        // Test invalid UUID for image_id
        $data = [
            'image_gallery_id' => $galleryId,
            'image_id' => 'not-a-uuid',
            'position' => 0,
        ];
        $entity = $this->ImageGalleriesImages->newEntity($data);
        $this->assertArrayHasKey('image_id', $entity->getErrors());
        $this->assertArrayHasKey('uuid', $entity->getErrors()['image_id']);

        // Test non-integer position
        $data = [
            'image_gallery_id' => $galleryId,
            'image_id' => $imageId,
            'position' => 'not-an-integer',
        ];
        $entity = $this->ImageGalleriesImages->newEntity($data);
        $this->assertArrayHasKey('position', $entity->getErrors());

        // Test caption can be empty
        $data = [
            'image_gallery_id' => $galleryId,
            'image_id' => $imageId,
            'position' => 0,
            'caption' => '',
        ];
        $entity = $this->ImageGalleriesImages->newEntity($data);
        $this->assertArrayNotHasKey('caption', $entity->getErrors());

        // Test caption can be null
        $data = [
            'image_gallery_id' => $galleryId,
            'image_id' => $imageId,
            'position' => 0,
            'caption' => null,
        ];
        $entity = $this->ImageGalleriesImages->newEntity($data);
        $this->assertArrayNotHasKey('caption', $entity->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\ImageGalleriesImagesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        // Create test gallery first (disable behaviors to avoid SlugBehavior transaction issues)
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->newEntity([
            'name' => 'Test Gallery',
            'slug' => 'test-gallery', // Provide slug manually to avoid SlugBehavior
            'is_published' => true,
        ]);
        $savedGallery = $galleries->save($gallery);
        $this->assertNotFalse($savedGallery);

        // Use existing image from fixture to avoid QueueableImageBehavior issues
        $images = $this->getTableLocator()->get('Images');
        $savedImage = $images->get('1202d58b-8fec-4b0c-900b-32246bb64d79');

        // Test valid foreign keys - should succeed
        $galleryImage = $this->ImageGalleriesImages->newEntity([
            'image_gallery_id' => $savedGallery->id,
            'image_id' => $savedImage->id,
            'position' => 0,
        ]);
        $result = $this->ImageGalleriesImages->save($galleryImage);
        $this->assertNotFalse($result);

        // Test invalid gallery ID - should fail
        $invalidGalleryImage = $this->ImageGalleriesImages->newEntity([
            'image_gallery_id' => '123e4567-e89b-12d3-a456-426614174999',
            'image_id' => $savedImage->id,
            'position' => 1,
        ]);
        $result = $this->ImageGalleriesImages->save($invalidGalleryImage);
        $this->assertFalse($result);
        $this->assertArrayHasKey('image_gallery_id', $invalidGalleryImage->getErrors());

        // Test invalid image ID - should fail
        $invalidImageGallery = $this->ImageGalleriesImages->newEntity([
            'image_gallery_id' => $savedGallery->id,
            'image_id' => '123e4567-e89b-12d3-a456-426614174998',
            'position' => 1,
        ]);
        $result = $this->ImageGalleriesImages->save($invalidImageGallery);
        $this->assertFalse($result);
        $this->assertArrayHasKey('image_id', $invalidImageGallery->getErrors());
    }

    /**
     * Test associations
     *
     * @return void
     */
    public function testAssociations(): void
    {
        $associations = $this->ImageGalleriesImages->associations();

        // Test belongsTo ImageGalleries
        $this->assertTrue($associations->has('ImageGalleries'));
        $imageGalleriesAssoc = $associations->get('ImageGalleries');
        $this->assertEquals('manyToOne', $imageGalleriesAssoc->type());
        $this->assertEquals('image_gallery_id', $imageGalleriesAssoc->getForeignKey());
        $this->assertEquals('INNER', $imageGalleriesAssoc->getJoinType());

        // Test belongsTo Images
        $this->assertTrue($associations->has('Images'));
        $imagesAssoc = $associations->get('Images');
        $this->assertEquals('manyToOne', $imagesAssoc->type());
        $this->assertEquals('image_id', $imagesAssoc->getForeignKey());
        $this->assertEquals('INNER', $imagesAssoc->getJoinType());
    }

    /**
     * Test reorderImages method
     *
     * @return void
     * @uses \App\Model\Table\ImageGalleriesImagesTable::reorderImages()
     */
    public function testReorderImages(): void
    {
        // Create test gallery (provide slug manually to avoid SlugBehavior transaction issues)
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->newEntity([
            'name' => 'Test Gallery',
            'slug' => 'test-gallery-reorder',
            'is_published' => true,
        ]);
        $savedGallery = $galleries->save($gallery);
        $this->assertNotFalse($savedGallery);

        // Use existing images from fixture to avoid QueueableImageBehavior issues
        $images = $this->getTableLocator()->get('Images');
        $savedImage1 = $images->get('1202d58b-8fec-4b0c-900b-32246bb64d79');
        $savedImage2 = $images->get('2202d58b-8fec-4b0c-900b-32246bb64d79');

        // Add images to gallery
        $galleryImage1 = $this->ImageGalleriesImages->newEntity([
            'image_gallery_id' => $savedGallery->id,
            'image_id' => $savedImage1->id,
            'position' => 0,
        ]);
        $this->ImageGalleriesImages->save($galleryImage1);

        $galleryImage2 = $this->ImageGalleriesImages->newEntity([
            'image_gallery_id' => $savedGallery->id,
            'image_id' => $savedImage2->id,
            'position' => 1,
        ]);
        $this->ImageGalleriesImages->save($galleryImage2);

        // Reorder images (swap positions)
        $newOrder = [$savedImage2->id, $savedImage1->id];
        $result = $this->ImageGalleriesImages->reorderImages($savedGallery->id, $newOrder);
        $this->assertTrue($result);

        // Verify new order
        $galleryImages = $this->ImageGalleriesImages->find()
            ->where(['image_gallery_id' => $savedGallery->id])
            ->orderBy(['position' => 'ASC'])
            ->toArray();

        $this->assertEquals($savedImage2->id, $galleryImages[0]->image_id);
        $this->assertEquals(0, $galleryImages[0]->position);
        $this->assertEquals($savedImage1->id, $galleryImages[1]->image_id);
        $this->assertEquals(1, $galleryImages[1]->position);
    }

    /**
     * Test getNextPosition method
     *
     * @return void
     * @uses \App\Model\Table\ImageGalleriesImagesTable::getNextPosition()
     */
    public function testGetNextPosition(): void
    {
        // Create test gallery (provide slug manually to avoid SlugBehavior transaction issues)
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->newEntity([
            'name' => 'Test Gallery',
            'slug' => 'test-gallery-position',
            'is_published' => true,
        ]);
        $savedGallery = $galleries->save($gallery);
        $this->assertNotFalse($savedGallery);

        // Test empty gallery - should return 0
        $nextPosition = $this->ImageGalleriesImages->getNextPosition($savedGallery->id);
        $this->assertEquals(0, $nextPosition);

        // Use existing image from fixture to avoid QueueableImageBehavior issues
        $images = $this->getTableLocator()->get('Images');
        $savedImage = $images->get('1202d58b-8fec-4b0c-900b-32246bb64d79');

        // Add image to gallery at position 0
        $galleryImage = $this->ImageGalleriesImages->newEntity([
            'image_gallery_id' => $savedGallery->id,
            'image_id' => $savedImage->id,
            'position' => 0,
        ]);
        $this->ImageGalleriesImages->save($galleryImage);

        // Test gallery with one image - should return 1
        $nextPosition = $this->ImageGalleriesImages->getNextPosition($savedGallery->id);
        $this->assertEquals(1, $nextPosition);
    }

    /**
     * Test findOrdered finder
     *
     * @return void
     * @uses \App\Model\Table\ImageGalleriesImagesTable::findOrdered()
     */
    public function testFindOrdered(): void
    {
        // Create test gallery (provide slug manually to avoid SlugBehavior transaction issues)
        $galleries = $this->getTableLocator()->get('ImageGalleries');
        $gallery = $galleries->newEntity([
            'name' => 'Test Gallery',
            'slug' => 'test-gallery-ordered',
            'is_published' => true,
        ]);
        $savedGallery = $galleries->save($gallery);
        $this->assertNotFalse($savedGallery);

        // Use existing images from fixture to avoid QueueableImageBehavior issues
        $images = $this->getTableLocator()->get('Images');
        $savedImage1 = $images->get('1202d58b-8fec-4b0c-900b-32246bb64d79');
        $savedImage2 = $images->get('2202d58b-8fec-4b0c-900b-32246bb64d79');

        // Add images to gallery in reverse order (position 1, then 0)
        $galleryImage2 = $this->ImageGalleriesImages->newEntity([
            'image_gallery_id' => $savedGallery->id,
            'image_id' => $savedImage2->id,
            'position' => 1,
        ]);
        $this->ImageGalleriesImages->save($galleryImage2);

        $galleryImage1 = $this->ImageGalleriesImages->newEntity([
            'image_gallery_id' => $savedGallery->id,
            'image_id' => $savedImage1->id,
            'position' => 0,
        ]);
        $this->ImageGalleriesImages->save($galleryImage1);

        // Use findOrdered - should return in position order
        $orderedImages = $this->ImageGalleriesImages->find('ordered')
            ->where(['image_gallery_id' => $savedGallery->id])
            ->toArray();

        $this->assertEquals(2, count($orderedImages));
        $this->assertEquals(0, $orderedImages[0]->position);
        $this->assertEquals($savedImage1->id, $orderedImages[0]->image_id);
        $this->assertEquals(1, $orderedImages[1]->position);
        $this->assertEquals($savedImage2->id, $orderedImages[1]->image_id);
    }
}
