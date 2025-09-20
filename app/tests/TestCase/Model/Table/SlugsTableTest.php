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
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        // Test unique slug within same model
        $slug = $this->Slugs->newEntity([
            'model' => 'Articles',
            'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
            'slug' => 'article-one', // Already exists in fixture
        ]);
        $this->assertFalse($this->Slugs->save($slug));

        // Test same slug allowed for different models
        $slug = $this->Slugs->newEntity([
            'model' => 'Tags',
            'foreign_key' => '334310b4-96ad-4d58-a0a9-af6dc7253c5e',
            'slug' => 'article-one',
        ]);
        $this->assertNotFalse($this->Slugs->save($slug));
    }

    /**
     * Test findBySlugAndModel finder
     *
     * @return void
     */
    public function testFindBySlugAndModel(): void
    {
        $result = $this->Slugs->find('bySlugAndModel', slug: 'article-one', model: 'Articles')
            ->first();

        $this->assertNotNull($result);
        $this->assertEquals('article-one', $result->slug);
        $this->assertEquals('Articles', $result->model);

        // Test with non-existent slug
        $result = $this->Slugs->find('bySlugAndModel', slug: 'non-existent', model: 'Articles')
            ->first();

        $this->assertNull($result);
    }

    /**
     * Test saving multiple slugs for the same record
     *
     * @return void
     */
    public function testSavingMultipleSlugs(): void
    {
        $slugs = [
            [
                'model' => 'Articles',
                'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'slug' => 'new-slug-1',
            ],
            [
                'model' => 'Articles',
                'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'slug' => 'new-slug-2',
            ],
        ];

        foreach ($slugs as $slugData) {
            $slug = $this->Slugs->newEntity($slugData);
            $this->assertNotFalse($this->Slugs->save($slug));
        }

        // Verify both slugs were saved
        $count = $this->Slugs->find()
            ->where([
                'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'slug IN' => ['new-slug-1', 'new-slug-2'],
            ])
            ->count();

        $this->assertEquals(2, $count);
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
        $this->assertContains('Tags', $models);
        // Test should be flexible about total count since other tests may create additional models
        $this->assertGreaterThanOrEqual(2, count($models), 'Should have at least Articles and Tags models');
    }
}
