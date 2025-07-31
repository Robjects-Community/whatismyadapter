<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SlugsTable;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

/**
 * App\Model\Table\SlugsTable Test Case
 */
class SlugsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SlugsTable
     */
    protected $Slugs;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Slugs',
        'app.Articles',
        'app.Products',
        'app.Tags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Slugs') ? [] : ['className' => SlugsTable::class];
        $this->Slugs = $this->getTableLocator()->get('Slugs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Slugs);
        parent::tearDown();
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
                'existingSlug' => 'article-one',
                'newSlug' => 'new-article-slug',
            ],
            'Products' => [
                'model' => 'Products',
                'foreignKey' => 'prod-001-usb-c-cable',
                'existingSlug' => 'product-one',
                'newSlug' => 'new-product-slug',
            ],
        ];
    }

    /**
     * Test initialization
     *
     * @return void
     */
    public function testInitialization(): void
    {
        $this->assertSame('slugs', $this->Slugs->getTable());
        $this->assertSame('slug', $this->Slugs->getDisplayField());
        $this->assertSame('id', $this->Slugs->getPrimaryKey());
        $this->assertTrue($this->Slugs->hasBehavior('Timestamp'));
    }

    /**
     * Test validation rules
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $validator = new Validator();
        $validator = $this->Slugs->validationDefault($validator);

        // Test valid data
        $data = [
            'model' => 'Articles',
            'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
            'slug' => 'valid-slug-123',
        ];
        $errors = $validator->validate($data);
        $this->assertEmpty($errors);

        // Test invalid slug format
        $data['slug'] = 'Invalid Slug!';
        $errors = $validator->validate($data);
        $this->assertNotEmpty($errors['slug']);

        // Test empty model
        $data['model'] = '';
        $errors = $validator->validate($data);
        $this->assertNotEmpty($errors['model']);

        // Test model length
        $data['model'] = str_repeat('a', 21);
        $errors = $validator->validate($data);
        $this->assertNotEmpty($errors['model']);
    }

    /**
     * Test buildRules method with both models
     *
     * @dataProvider modelProvider
     * @return void
     */
    public function testBuildRules(string $model, string $foreignKey, string $existingSlug): void
    {
        // Test unique slug within same model
        $slug = $this->Slugs->newEntity([
            'model' => $model,
            'foreign_key' => $foreignKey,
            'slug' => $existingSlug, // Already exists in fixture
        ]);
        $this->assertFalse($this->Slugs->save($slug));

        // Test same slug allowed for different models
        $differentModel = $model === 'Articles' ? 'Products' : 'Articles';
        $differentKey = $model === 'Articles' ? 'prod-001-usb-c-cable' : '263a5364-a1bc-401c-9e44-49c23d066a0f';
        
        $slug = $this->Slugs->newEntity([
            'model' => $differentModel,
            'foreign_key' => $differentKey,
            'slug' => $existingSlug,
        ]);
        $this->assertNotFalse($this->Slugs->save($slug));
    }

    /**
     * Test findBySlugAndModel finder with both models
     *
     * @dataProvider modelProvider
     * @return void
     */
    public function testFindBySlugAndModel(string $model, string $foreignKey, string $existingSlug): void
    {
        $result = $this->Slugs->find('bySlugAndModel', slug: $existingSlug, model: $model)
            ->first();

        $this->assertNotNull($result);
        $this->assertEquals($existingSlug, $result->slug);
        $this->assertEquals($model, $result->model);

        // Test with non-existent slug
        $result = $this->Slugs->find('bySlugAndModel', slug: 'non-existent', model: $model)
            ->first();

        $this->assertNull($result);
    }

    /**
     * Test saving multiple slugs for the same record
     *
     * @dataProvider modelProvider
     * @return void
     */
    public function testSavingMultipleSlugs(string $model, string $foreignKey, string $existingSlug, string $newSlug): void
    {
        $slugs = [
            [
                'model' => $model,
                'foreign_key' => $foreignKey,
                'slug' => $newSlug . '-1',
            ],
            [
                'model' => $model,
                'foreign_key' => $foreignKey,
                'slug' => $newSlug . '-2',
            ],
        ];

        foreach ($slugs as $slugData) {
            $slug = $this->Slugs->newEntity($slugData);
            $this->assertNotFalse($this->Slugs->save($slug));
        }

        // Verify both slugs were saved
        $count = $this->Slugs->find()
            ->where([
                'foreign_key' => $foreignKey,
                'slug IN' => [$newSlug . '-1', $newSlug . '-2'],
            ])
            ->count();

        $this->assertEquals(2, $count);
    }

    /**
     * Test cross-model slug compatibility
     *
     * @return void
     */
    public function testCrossModelSlugCompatibility(): void
    {
        $sameslug = 'shared-slug-name';
        
        // Create slug for Articles
        $articleSlug = $this->Slugs->newEntity([
            'model' => 'Articles',
            'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
            'slug' => $sameslug,
        ]);
        $this->assertNotFalse($this->Slugs->save($articleSlug));

        // Create same slug for Products (should be allowed)
        $productSlug = $this->Slugs->newEntity([
            'model' => 'Products',
            'foreign_key' => 'prod-001-usb-c-cable',
            'slug' => $sameslug,
        ]);
        $this->assertNotFalse($this->Slugs->save($productSlug));

        // Verify both exist
        $articleResult = $this->Slugs->find('bySlugAndModel', slug: $sameslug, model: 'Articles')->first();
        $productResult = $this->Slugs->find('bySlugAndModel', slug: $sameslug, model: 'Products')->first();

        $this->assertNotNull($articleResult);
        $this->assertNotNull($productResult);
        $this->assertEquals('Articles', $articleResult->model);
        $this->assertEquals('Products', $productResult->model);
    }

    /**
     * Test getting unique models
     *
     * @return void
     */
    public function testGetUniqueModels(): void
    {
        $models = $this->Slugs->find()
            ->select(['model'])
            ->distinct('model')
            ->orderBy(['model' => 'ASC'])
            ->all()
            ->map(fn($row) => $row->model)
            ->toArray();

        $this->assertContains('Articles', $models);
        $this->assertContains('Products', $models);
        $this->assertContains('Tags', $models);
        $this->assertGreaterThanOrEqual(3, count($models), 'Should have at least Articles, Products, and Tags models');
    }

    /**
     * Test bulk operations across models
     *
     * @return void
     */
    public function testBulkOperationsAcrossModels(): void
    {
        // Create multiple slugs for both models
        $bulkData = [
            ['model' => 'Articles', 'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f', 'slug' => 'bulk-article-1'],
            ['model' => 'Articles', 'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f', 'slug' => 'bulk-article-2'],
            ['model' => 'Products', 'foreign_key' => 'prod-001-usb-c-cable', 'slug' => 'bulk-product-1'],
            ['model' => 'Products', 'foreign_key' => 'prod-001-usb-c-cable', 'slug' => 'bulk-product-2'],
        ];

        foreach ($bulkData as $data) {
            $entity = $this->Slugs->newEntity($data);
            $this->assertNotFalse($this->Slugs->save($entity));
        }

        // Test filtering by model
        $articleCount = $this->Slugs->find()
            ->where(['model' => 'Articles', 'slug LIKE' => 'bulk-article-%'])
            ->count();
        $this->assertEquals(2, $articleCount);

        $productCount = $this->Slugs->find()
            ->where(['model' => 'Products', 'slug LIKE' => 'bulk-product-%'])
            ->count();
        $this->assertEquals(2, $productCount);
    }
}