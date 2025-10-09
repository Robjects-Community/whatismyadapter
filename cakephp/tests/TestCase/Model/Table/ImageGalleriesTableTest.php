<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ImageGalleriesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ImageGalleriesTable Test Case
 */
class ImageGalleriesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ImageGalleriesTable
     */
    protected $ImageGalleries;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
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
        $config = $this->getTableLocator()->exists('ImageGalleries') ? [] : ['className' => ImageGalleriesTable::class];
        $this->ImageGalleries = $this->getTableLocator()->get('ImageGalleries', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ImageGalleries);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ImageGalleriesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        // Test valid data
        $data = [
            'name' => 'Test Gallery',
            'description' => 'A test gallery description',
            'is_published' => true,
        ];
        $entity = $this->ImageGalleries->newEntity($data);
        $this->assertEmpty($entity->getErrors());

        // Test name is required
        $data = ['description' => 'Test without name'];
        $entity = $this->ImageGalleries->newEntity($data);
        $this->assertArrayHasKey('name', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['name']);

        // Test name max length (255 characters)
        $data = ['name' => str_repeat('a', 256)];
        $entity = $this->ImageGalleries->newEntity($data);
        $this->assertArrayHasKey('name', $entity->getErrors());
        $this->assertArrayHasKey('maxLength', $entity->getErrors()['name']);

        // Test name cannot be empty string
        $data = ['name' => ''];
        $entity = $this->ImageGalleries->newEntity($data);
        $this->assertArrayHasKey('name', $entity->getErrors());

        // Test slug max length (255 characters)
        $data = [
            'name' => 'Valid Name',
            'slug' => str_repeat('a', 256),
        ];
        $entity = $this->ImageGalleries->newEntity($data);
        $this->assertArrayHasKey('slug', $entity->getErrors());
        $this->assertArrayHasKey('maxLength', $entity->getErrors()['slug']);

        // Test is_published must be boolean
        $data = [
            'name' => 'Valid Name',
            'is_published' => 'not_boolean',
        ];
        $entity = $this->ImageGalleries->newEntity($data);
        $this->assertArrayHasKey('is_published', $entity->getErrors());

        // Test valid UUID for created_by
        $data = [
            'name' => 'Valid Name',
            'created_by' => 'not-a-uuid',
        ];
        $entity = $this->ImageGalleries->newEntity($data);
        $this->assertArrayHasKey('created_by', $entity->getErrors());
        $this->assertArrayHasKey('uuid', $entity->getErrors()['created_by']);

        // Test valid UUID for modified_by
        $data = [
            'name' => 'Valid Name',
            'modified_by' => 'not-a-uuid',
        ];
        $entity = $this->ImageGalleries->newEntity($data);
        $this->assertArrayHasKey('modified_by', $entity->getErrors());
        $this->assertArrayHasKey('uuid', $entity->getErrors()['modified_by']);

        // Test optional fields can be empty
        $data = [
            'name' => 'Valid Name',
            'slug' => '',
            'description' => '',
            'created_by' => '',
            'modified_by' => '',
        ];
        $entity = $this->ImageGalleries->newEntity($data);
        $this->assertArrayNotHasKey('slug', $entity->getErrors());
        $this->assertArrayNotHasKey('description', $entity->getErrors());
        $this->assertArrayNotHasKey('created_by', $entity->getErrors());
        $this->assertArrayNotHasKey('modified_by', $entity->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\ImageGalleriesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        // Create first gallery
        $gallery1 = $this->ImageGalleries->newEntity([
            'name' => 'First Gallery',
            'slug' => 'unique-slug',
            'is_published' => true,
        ]);
        $result1 = $this->ImageGalleries->save($gallery1);
        $this->assertNotFalse($result1);

        // Try to create second gallery with same slug - should fail
        $gallery2 = $this->ImageGalleries->newEntity([
            'name' => 'Second Gallery',
            'slug' => 'unique-slug',
            'is_published' => true,
        ]);
        $result2 = $this->ImageGalleries->save($gallery2);
        $this->assertFalse($result2);
        $this->assertArrayHasKey('slug', $gallery2->getErrors());
        // Check that there's a validation error for slug uniqueness
        $slugErrors = $gallery2->getErrors()['slug'];
        $this->assertNotEmpty($slugErrors);

        // Create gallery with different slug - should succeed
        $gallery3 = $this->ImageGalleries->newEntity([
            'name' => 'Third Gallery',
            'slug' => 'different-slug',
            'is_published' => true,
        ]);
        $result3 = $this->ImageGalleries->save($gallery3);
        $this->assertNotFalse($result3);

        // Test empty slug is allowed (will be auto-generated)
        $gallery4 = $this->ImageGalleries->newEntity([
            'name' => 'Fourth Gallery',
            'slug' => '',
            'is_published' => true,
        ]);
        $result4 = $this->ImageGalleries->save($gallery4);
        $this->assertNotFalse($result4);
    }

    /**
     * Test SlugBehavior integration
     *
     * @return void
     */
    public function testSlugBehavior(): void
    {
        // Test slug auto-generation from name
        $gallery = $this->ImageGalleries->newEntity([
            'name' => 'My Test Gallery',
            'is_published' => true,
        ]);
        $result = $this->ImageGalleries->save($gallery);
        $this->assertNotFalse($result);
        $this->assertEquals('my-test-gallery', $gallery->slug);

        // Test slug is not overwritten if provided
        $gallery2 = $this->ImageGalleries->newEntity([
            'name' => 'Another Gallery',
            'slug' => 'custom-slug',
            'is_published' => true,
        ]);
        $result2 = $this->ImageGalleries->save($gallery2);
        $this->assertNotFalse($result2);
        $this->assertEquals('custom-slug', $gallery2->slug);
    }

    /**
     * Test associations
     *
     * @return void
     */
    public function testAssociations(): void
    {
        $associations = $this->ImageGalleries->associations();

        // Test hasMany ImageGalleriesImages
        $this->assertTrue($associations->has('ImageGalleriesImages'));
        $imageGalleriesImagesAssoc = $associations->get('ImageGalleriesImages');
        $this->assertEquals('oneToMany', $imageGalleriesImagesAssoc->type());
        $this->assertEquals('image_gallery_id', $imageGalleriesImagesAssoc->getForeignKey());
        $this->assertTrue($imageGalleriesImagesAssoc->getDependent());

        // Test belongsToMany Images
        $this->assertTrue($associations->has('Images'));
        $imagesAssoc = $associations->get('Images');
        $this->assertEquals('manyToMany', $imagesAssoc->type());
        $this->assertEquals('image_gallery_id', $imagesAssoc->getForeignKey());
        $this->assertEquals('image_id', $imagesAssoc->getTargetForeignKey());
        $this->assertEquals('image_galleries_images', $imagesAssoc->junction()->getTable());
    }
}
