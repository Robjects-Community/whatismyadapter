<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TypeError;

class OrderableBehaviorProductTest extends TestCase
{
    /**
     * Test fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Products',
    ];

    /**
     * Test table instance
     *
     * @var \Cake\ORM\Table
     */
    protected Table $Table;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Table = TableRegistry::getTableLocator()->get('Products');
        $this->Table->addBehavior('Orderable');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Table);
        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertTrue($this->Table->hasBehavior('Tree'));

        // Remove both behaviors to start fresh
        $this->Table->removeBehavior('Tree');
        $this->Table->removeBehavior('Orderable');

        // Add Orderable with custom config
        $this->Table->addBehavior('Orderable', [
            'treeConfig' => [
                'parent' => 'custom_parent_id',
                'left' => 'custom_left',
                'right' => 'custom_right',
            ],
        ]);

        // Get the Tree behavior that was automatically added by Orderable
        $treeBehavior = $this->Table->getBehavior('Tree');
        $config = $treeBehavior->getConfig();

        $this->assertEquals('custom_parent_id', $config['parent']);
        $this->assertEquals('custom_left', $config['left']);
        $this->assertEquals('custom_right', $config['right']);
    }

    /**
     * Test reorder method with invalid data
     *
     * @return void
     */
    public function testReorderInvalidData(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('must be of type array, string given');
        $this->Table->reorder('not an array');
    }

    /**
     * Test reorder method for root level movement
     *
     * @return void
     */
    public function testReorderToRoot(): void
    {
        // Move Page Two to root level
        $result = $this->Table->reorder([
            'id' => 'ce6794a2-b191-445c-99ed-de93f6e7eeb1', // Page Two
            'newParentId' => 'root',
            'newIndex' => 0,
        ]);

        $this->assertTrue($result);

        // Verify the move
        $movedPage = $this->Table->get('ce6794a2-b191-445c-99ed-de93f6e7eeb1');
        $this->assertNull($movedPage->parent_id);

        // Verify it's at the root level
        $rootPages = $this->Table->find()
            ->where(['parent_id IS' => null])
            ->orderBy(['lft' => 'ASC'])
            ->toArray();

        $this->assertContains($movedPage->id, array_column($rootPages, 'id'));
    }

    /**
     * Test reorder method for changing parent
     *
     * @return void
     */
    public function testReorderChangeParent(): void
    {
        // Move Page Six under Page One
        $result = $this->Table->reorder([
            'id' => 'e98aaafa-415a-4911-8ff2-25f76b326ea4', // Page Six
            'newParentId' => '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e', // Page One
            'newIndex' => 0,
        ]);

        $this->assertTrue($result);

        // Verify the move
        $movedPage = $this->Table->get('e98aaafa-415a-4911-8ff2-25f76b326ea4');
        $this->assertEquals('630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e', $movedPage->parent_id);

        // Verify it's the first child of Page One
        $children = $this->Table->find('children', for: '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e', direct: true)
            ->orderBy(['lft' => 'ASC'])
            ->toArray();

        $this->assertEquals($movedPage->id, $children[0]->id);
    }

    /**
     * Test getTree method
     *
     * @return void
     */
    public function testGetTree(): void
    {
        // Test getting only pages
        $pageTree = $this->Table->getTree(
            ['kind' => 'page'],
            ['title', 'slug', 'is_published'],
        );

        $this->assertIsArray($pageTree);

        // Should have 2 root level pages
        $rootPages = array_filter($pageTree, fn($item) => $item->parent_id === null);
        $this->assertCount(2, $rootPages);

        // Verify Page One's children
        $pageOne = array_filter($pageTree, fn($item) => $item->title === 'Page One')[0];
        $this->assertCount(3, $pageOne->children);

        // Verify all required fields are present
        $this->assertTrue(isset($pageOne->title));
        $this->assertTrue(isset($pageOne->slug));
        $this->assertTrue(isset($pageOne->is_published));

        // Test getting only products
        $productTree = $this->Table->getTree(
            ['kind' => 'product'],
            ['title', 'is_published'],
        );

        $this->assertIsArray($productTree);

        // All products should be at root level (3 products in fixture)
        $this->assertCount(3, $productTree);
        $this->assertTrue(array_reduce($productTree, fn($carry, $item) => $carry && $item->parent_id === null, true));
    }

    /**
     * Test reordering siblings
     *
     * @return void
     */
    public function testReorderSiblings(): void
    {
        // Move Page Three to be first among siblings
        $result = $this->Table->reorder([
            'id' => 'de7894a3-c292-556d-00ee-ef04g7f8ffc2', // Page Three
            'newParentId' => '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e', // Page One
            'newIndex' => 0,
        ]);

        $this->assertTrue($result);

        // Verify the new order
        $siblings = $this->Table->find('children', for: '630fe0f3-7d68-472f-a1b1-c73ed3fe0c8e', direct: true)
            ->orderBy(['lft' => 'ASC'])
            ->toArray();

        $this->assertEquals('Page Three', $siblings[0]->title);
        $this->assertEquals('Page Two', $siblings[1]->title);
    }
}
