<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class SlugBehaviorTest extends TestCase
{
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
    ];

    /**
     * @var array<string, Table>
     */
    protected array $tables = [];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        
        // Initialize both tables with slug behavior
        $this->tables['Articles'] = $this->initializeTableWithSlugBehavior('Articles');
        $this->tables['Products'] = $this->initializeTableWithSlugBehavior('Products');
        $this->tables['Slugs'] = TableRegistry::getTableLocator()->get('Slugs');
    }

    /**
     * Initialize table with slug behavior
     *
     * @param string $tableName
     * @return Table
     */
    private function initializeTableWithSlugBehavior(string $tableName): Table
    {
        $table = TableRegistry::getTableLocator()->get($tableName);
        $table->addBehavior('Slug', [
            'sourceField' => 'title',
            'targetField' => 'slug',
        ]);
        return $table;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        foreach ($this->tables as $table) {
            unset($table);
        }
        $this->tables = [];
        parent::tearDown();
    }

    /**
     * Data provider for table configurations
     *
     * @return array
     */
    public function tableProvider(): array
    {
        return [
            'Articles' => [
                'tableName' => 'Articles',
                'entityData' => [
                    'title' => 'Test Article',
                    'body' => 'Content for test article',
                    'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                    'is_published' => true,
                    'kind' => 'article',
                ],
                'existingId' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'existingSlug' => 'article-one-final',
                'historySlug' => 'article-one-updated',
            ],
            'Products' => [
                'tableName' => 'Products',
                'entityData' => [
                    'title' => 'Test Product',
                    'description' => 'Description for test product',
                    'price' => 99.99,
                    'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                    'is_active' => true,
                ],
                'existingId' => 'prod-001-usb-c-cable', // Update with actual fixture ID
                'existingSlug' => 'product-one-final',
                'historySlug' => 'product-one-updated',
            ],
        ];
    }

    /**
     * Test automatic slug generation from title
     *
     * @dataProvider tableProvider
     * @param string $tableName
     * @param array $entityData
     * @return void
     */
    public function testAutomaticSlugGeneration(string $tableName, array $entityData): void
    {
        $table = $this->tables[$tableName];
        $entityData['title'] = 'New Test ' . $tableName;
        
        $entity = $table->newEntity($entityData);
        $result = $table->save($entity);
        
        $this->assertNotFalse($result);
        $this->assertEquals('new-test-' . strtolower($tableName), $entity->slug);
    }

    /**
     * Test slug uniqueness validation
     *
     * @dataProvider tableProvider
     * @param string $tableName
     * @param array $entityData
     * @param string $existingId
     * @param string $existingSlug
     * @return void
     */
    public function testSlugUniqueness(string $tableName, array $entityData, string $existingId, string $existingSlug): void
    {
        $table = $this->tables[$tableName];
        $entityData['slug'] = $existingSlug;
        
        $entity = $table->newEntity($entityData);
        $result = $table->save($entity);
        
        $this->assertFalse($result);
        $this->assertNotEmpty($entity->getError('slug'));
    }

    /**
     * Test slug history creation
     *
     * @dataProvider tableProvider
     * @param string $tableName
     * @param array $entityData
     * @param string $existingId
     * @return void
     */
    public function testSlugHistoryCreation(string $tableName, array $entityData, string $existingId): void
    {
        $table = $this->tables[$tableName];
        $entity = $table->get($existingId);
        
        $newSlug = strtolower($tableName) . '-updated-again';
        $entity->patch([
            'title' => $tableName . ' Updated Again',
            'slug' => $newSlug,
        ]);
        
        $result = $table->save($entity);
        $this->assertNotFalse($result);
        
        $this->assertSlugHistoryExists($existingId, $tableName, $newSlug);
    }

    /**
     * Test custom slug setting
     *
     * @dataProvider tableProvider
     * @param string $tableName
     * @param array $entityData
     * @return void
     */
    public function testCustomSlugSetting(string $tableName, array $entityData): void
    {
        $table = $this->tables[$tableName];
        $customSlug = 'custom-' . strtolower($tableName) . '-slug';
        $entityData['slug'] = $customSlug;
        
        $entity = $table->newEntity($entityData);
        $result = $table->save($entity);
        
        $this->assertNotFalse($result);
        $this->assertEquals($customSlug, $entity->slug);
    }

    /**
     * Test slug validation against historical slugs
     *
     * @dataProvider tableProvider
     * @param string $tableName
     * @param array $entityData
     * @param string $existingId
     * @param string $existingSlug
     * @param string $historySlug
     * @return void
     */
    public function testSlugValidationAgainstHistory(string $tableName, array $entityData, string $existingId, string $existingSlug, string $historySlug): void
    {
        $table = $this->tables[$tableName];
        $entityData['slug'] = $historySlug;
        
        $entity = $table->newEntity($entityData);
        $result = $table->save($entity);
        
        $this->assertFalse($result);
        $this->assertNotEmpty($entity->getError('slug'));
    }

    /**
     * Test slug generation with special characters
     *
     * @dataProvider tableProvider
     * @param string $tableName
     * @param array $entityData
     * @return void
     */
    public function testSlugGenerationWithSpecialCharacters(string $tableName, array $entityData): void
    {
        $table = $this->tables[$tableName];
        $entityData['title'] = 'Test & ' . $tableName . '! With @ Special # Characters';
        
        $entity = $table->newEntity($entityData);
        $result = $table->save($entity);
        
        $this->assertNotFalse($result);
        $this->assertEquals('test-' . strtolower($tableName) . '-with-special-characters', $entity->slug);
    }

    /**
     * Helper method to assert slug history exists
     *
     * @param string $foreignKey
     * @param string $model
     * @param string $slug
     * @return void
     */
    private function assertSlugHistoryExists(string $foreignKey, string $model, string $slug): void
    {
        $slugHistory = $this->tables['Slugs']->find()
            ->where([
                'foreign_key' => $foreignKey,
                'model' => $model,
                'slug' => $slug,
            ])
            ->first();
            
        $this->assertNotNull($slugHistory);
        $this->assertEquals($slug, $slugHistory->slug);
    }
}
