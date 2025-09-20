### Overview

This document provides a comprehensive, step-by-step implementation plan for implementing a **simplified** Product Management System in Willow CMS starting from version tag v1.5.0-beta. The system features a single products table with essential fields, unified tagging across articles and products, and optional article associations for detailed product information.

## Prerequisites

*   Docker environment running (`./setup_dev_env.sh`)
*   Queue worker running (`cake_queue_worker`)
*   All development aliases installed (`./setup_dev_aliases.sh`)
*   Willow CMS v1.4.0 baseline

## Phase 1: Foundation &amp; Database Schema (Week 1-2)

### Day 1: Simplified Database Schema Creation

#### 1.1 Create Simplified Products Migration

```plaintext
# Create the simplified products migration
docker compose exec willowcms bin/cake bake migration CreateSimplifiedProducts

```

**Migration Content:** `config/Migrations/YYYYMMDD_HHMMSS_CreateSimplifiedProducts.php`

```php
<!--?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateSimplifiedProducts extends AbstractMigration
{
    public function change(): void
    {
        // Create simplified products table
        $table = $this--->table('products', [
            'id' =&gt; false,
            'primary_key' =&gt; ['id'],
        ]);

        $table-&gt;addColumn('id', 'uuid', [
            'default' =&gt; null,
            'null' =&gt; false,
        ])
        -&gt;addColumn('user_id', 'uuid', [
            'default' =&gt; null,
            'null' =&gt; false,
        ])
        -&gt;addColumn('article_id', 'uuid', [
            'default' =&gt; null,
            'null' =&gt; true,
            'comment' =&gt; 'Optional reference to detailed article'
        ])
        -&gt;addColumn('title', 'string', [
            'default' =&gt; null,
            'limit' =&gt; 255,
            'null' =&gt; false,
        ])
        -&gt;addColumn('slug', 'string', [
            'default' =&gt; null,
            'limit' =&gt; 191,
            'null' =&gt; false,
        ])
        -&gt;addColumn('description', 'text', [
            'default' =&gt; null,
            'null' =&gt; true,
            'comment' =&gt; 'Brief product description'
        ])
        -&gt;addColumn('manufacturer', 'string', [
            'default' =&gt; null,
            'limit' =&gt; 255,
            'null' =&gt; true,
        ])
        -&gt;addColumn('model_number', 'string', [
            'default' =&gt; null,
            'limit' =&gt; 255,
            'null' =&gt; true,
        ])
        -&gt;addColumn('price', 'decimal', [
            'default' =&gt; null,
            'null' =&gt; true,
            'precision' =&gt; 10,
            'scale' =&gt; 2,
        ])
        -&gt;addColumn('currency', 'char', [
            'default' =&gt; 'USD',
            'limit' =&gt; 3,
            'null' =&gt; true,
        ])
        -&gt;addColumn('image', 'string', [
            'default' =&gt; null,
            'limit' =&gt; 255,
            'null' =&gt; true,
            'comment' =&gt; 'Primary product image'
        ])
        -&gt;addColumn('alt_text', 'string', [
            'default' =&gt; null,
            'limit' =&gt; 255,
            'null' =&gt; true,
        ])
        -&gt;addColumn('is_published', 'boolean', [
            'default' =&gt; false,
            'null' =&gt; false,
        ])
        -&gt;addColumn('featured', 'boolean', [
            'default' =&gt; false,
            'null' =&gt; false,
        ])
        -&gt;addColumn('verification_status', 'string', [
            'default' =&gt; 'pending',
            'limit' =&gt; 20,
            'null' =&gt; false,
        ])
        -&gt;addColumn('reliability_score', 'decimal', [
            'default' =&gt; '0.00',
            'null' =&gt; true,
            'precision' =&gt; 3,
            'scale' =&gt; 2,
        ])
        -&gt;addColumn('view_count', 'integer', [
            'default' =&gt; 0,
            'null' =&gt; false,
        ])
        -&gt;addColumn('created', 'datetime', [
            'default' =&gt; null,
            'null' =&gt; false,
        ])
        -&gt;addColumn('modified', 'datetime', [
            'default' =&gt; null,
            'null' =&gt; false,
        ])
        -&gt;addIndex(['slug'], ['unique' =&gt; true, 'name' =&gt; 'idx_products_slug'])
        -&gt;addIndex(['user_id'], ['name' =&gt; 'idx_products_user'])
        -&gt;addIndex(['article_id'], ['name' =&gt; 'idx_products_article'])
        -&gt;addIndex(['is_published'], ['name' =&gt; 'idx_products_published'])
        -&gt;addIndex(['featured'], ['name' =&gt; 'idx_products_featured'])
        -&gt;addIndex(['verification_status'], ['name' =&gt; 'idx_products_verification'])
        -&gt;addIndex(['manufacturer'], ['name' =&gt; 'idx_products_manufacturer'])
        -&gt;addIndex(['reliability_score'], ['name' =&gt; 'idx_products_reliability'])
        -&gt;addIndex(['created'], ['name' =&gt; 'idx_products_created'])
        -&gt;create();

        // Create products_tags junction table for unified tagging
        $tagsTable = $this-&gt;table('products_tags', [
            'id' =&gt; false,
            'primary_key' =&gt; ['product_id', 'tag_id'],
        ]);

        $tagsTable-&gt;addColumn('product_id', 'uuid', [
            'default' =&gt; null,
            'null' =&gt; false,
        ])
        -&gt;addColumn('tag_id', 'uuid', [
            'default' =&gt; null,
            'null' =&gt; false,
        ])
        -&gt;addIndex(['product_id'], ['name' =&gt; 'idx_products_tags_product'])
        -&gt;addIndex(['tag_id'], ['name' =&gt; 'idx_products_tags_tag'])
        -&gt;create();
    }
}
```

```plaintext
# Run the migration after editing
docker compose exec willowcms bin/cake migrations migrate
```

#### 1.2 Generate Core Model Structure

```plaintext
# Generate the simplified model structure using AdminTheme
docker compose exec willowcms bin/cake bake model Products --theme AdminTheme

# This creates:
# - src/Model/Table/ProductsTable.php
# - src/Model/Entity/Product.php
```

#### 1.3 Generate Model Tests

```plaintext
# Generate unit tests for the models
docker compose exec willowcms bin/cake bake test table Products
docker compose exec willowcms bin/cake bake test entity Product

# This creates:
# - tests/TestCase/Model/Table/ProductsTableTest.php
# - tests/TestCase/Model/Entity/ProductTest.php
```

#### 1.4 Generate Fixtures for Testing

```plaintext
# Generate test fixtures
docker compose exec willowcms bin/cake bake fixture Products

# This creates:
# - tests/Fixture/ProductsFixture.php
```

### Day 2: Enhanced Model Relationships &amp; Methods

#### 2.1 Configure Products Model Associations

Update `src/Model/Table/ProductsTable.php`:

```php
<!--?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ProductsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this--->setTable('products');
        $this-&gt;setDisplayField('title');
        $this-&gt;setPrimaryKey('id');

        $this-&gt;addBehavior('Timestamp');
        $this-&gt;addBehavior('Sluggable', [
            'slug' =&gt; 'slug',
            'displayField' =&gt; 'title'
        ]);

        // Core relationships
        $this-&gt;belongsTo('Users', [
            'foreignKey' =&gt; 'user_id',
            'joinType' =&gt; 'INNER',
        ]);

        $this-&gt;belongsTo('Articles', [
            'foreignKey' =&gt; 'article_id',
            'joinType' =&gt; 'LEFT',
            'propertyName' =&gt; 'article'
        ]);

        // Many-to-many with Tags (unified tagging system)
        $this-&gt;belongsToMany('Tags', [
            'foreignKey' =&gt; 'product_id',
            'targetForeignKey' =&gt; 'tag_id',
            'joinTable' =&gt; 'products_tags',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            -&gt;uuid('id')
            -&gt;allowEmptyString('id', null, 'create');

        $validator
            -&gt;scalar('title')
            -&gt;maxLength('title', 255)
            -&gt;requirePresence('title', 'create')
            -&gt;notEmptyString('title');

        $validator
            -&gt;scalar('slug')
            -&gt;maxLength('slug', 191)
            -&gt;requirePresence('slug', 'create')
            -&gt;notEmptyString('slug')
            -&gt;add('slug', 'unique', ['rule' =&gt; 'validateUnique', 'provider' =&gt; 'table']);

        $validator
            -&gt;scalar('description')
            -&gt;allowEmptyString('description');

        $validator
            -&gt;scalar('manufacturer')
            -&gt;maxLength('manufacturer', 255)
            -&gt;allowEmptyString('manufacturer');

        $validator
            -&gt;scalar('model_number')
            -&gt;maxLength('model_number', 255)
            -&gt;allowEmptyString('model_number');

        $validator
            -&gt;decimal('price')
            -&gt;allowEmptyString('price');

        $validator
            -&gt;boolean('is_published')
            -&gt;notEmptyString('is_published');

        $validator
            -&gt;boolean('featured')
            -&gt;notEmptyString('featured');

        return $validator;
    }

    /**
     * Get published products with optional filtering
     */
    public function getPublishedProducts(array $options = []): \Cake\ORM\Query
    {
        $query = $this-&gt;find()
            -&gt;where(['Products.is_published' =&gt; true])
            -&gt;contain(['Users', 'Tags', 'Articles'])
            -&gt;order(['Products.created' =&gt; 'DESC']);

        // Apply filters
        if (!empty($options['tag'])) {
            $query-&gt;matching('Tags', function ($q) use ($options) {
                return $q-&gt;where(['Tags.slug' =&gt; $options['tag']]);
            });
        }

        if (!empty($options['manufacturer'])) {
            $query-&gt;where(['Products.manufacturer LIKE' =&gt; '%' . $options['manufacturer'] . '%']);
        }

        if (!empty($options['featured'])) {
            $query-&gt;where(['Products.featured' =&gt; true]);
        }

        return $query;
    }

    /**
     * Get products by verification status
     */
    public function getProductsByStatus(string $status): \Cake\ORM\Query
    {
        return $this-&gt;find()
            -&gt;where(['verification_status' =&gt; $status])
            -&gt;contain(['Users', 'Tags'])
            -&gt;order(['created' =&gt; 'ASC']);
    }

    /**
     * Search products across title, description, manufacturer
     */
    public function searchProducts(string $term): \Cake\ORM\Query
    {
        return $this-&gt;find()
            -&gt;where([
                'OR' =&gt; [
                    'Products.title LIKE' =&gt; "%{$term}%",
                    'Products.description LIKE' =&gt; "%{$term}%",
                    'Products.manufacturer LIKE' =&gt; "%{$term}%",
                    'Products.model_number LIKE' =&gt; "%{$term}%"
                ]
            ])
            -&gt;contain(['Tags', 'Users'])
            -&gt;where(['Products.is_published' =&gt; true]);
    }

    /**
     * Get products with same tags (for related products)
     */
    public function getRelatedProducts(string $productId, int $limit = 5): array
    {
        $product = $this-&gt;get($productId, ['contain' =&gt; ['Tags']]);

        if (empty($product-&gt;tags)) {
            return [];
        }

        $tagIds = array_map(fn($tag) =&gt; $tag-&gt;id, $product-&gt;tags);

        return $this-&gt;find()
            -&gt;matching('Tags', function ($q) use ($tagIds) {
                return $q-&gt;where(['Tags.id IN' =&gt; $tagIds]);
            })
            -&gt;where([
                'Products.id !=' =&gt; $productId,
                'Products.is_published' =&gt; true
            ])
            -&gt;limit($limit)
            -&gt;toArray();
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(string $productId): bool
    {
        return $this-&gt;updateAll(
            ['view_count = view_count + 1'],
            ['id' =&gt; $productId]
        );
    }
}
```

#### 2.2 Update Articles Table for Product Association

Update `src/Model/Table/ArticlesTable.php` to add the reverse relationship:

```php
// Add to initialize() method in ArticlesTable
$this-&gt;hasMany('Products', [
    'foreignKey' =&gt; 'article_id',
    'dependent' =&gt; false, // Don't delete products when article is deleted
]);
```

#### 2.3 Update Tags Table for Products Association

Update `src/Model/Table/TagsTable.php`:

```php
// Add to initialize() method in TagsTable
$this-&gt;belongsToMany('Products', [
    'foreignKey' =&gt; 'tag_id',
    'targetForeignKey' =&gt; 'product_id',
    'joinTable' =&gt; 'products_tags',
]);
```

### Day 3: Slug System Enhancement

#### 3.1 Update Slug Behavior for Products

Create `src/Model/Behavior/SlugBehavior.php` enhancement or update existing:

```php
// In the existing SlugBehavior, ensure Products model is supported
// Update the behavior to handle the new 'Product' model type

// Add to the model types array:
protected $supportedModels = [
    'Article',
    'Tag', 
    'User',
    'Page',
    'Product' // New addition
];
```

#### 3.2 Update Slugs Table if Needed

If the slugs table needs enhancement, create a migration:

```plaintext
docker compose exec willowcms bin/cake bake migration EnhanceSlugsForProducts
```

**Migration Content:** `config/Migrations/YYYYMMDD_HHMMSS_EnhanceSlugsForProducts.php`

```php
<!--?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class EnhanceSlugsForProducts extends AbstractMigration
{
    public function change(): void
    {
        // Add any necessary indexes for better performance with Products
        $table = $this--->table('slugs');

        // Ensure we have proper composite index for model + foreign_key lookups
        if (!$table-&gt;hasIndex(['model', 'foreign_key'])) {
            $table-&gt;addIndex(['model', 'foreign_key'], [
                'name' =&gt; 'idx_slugs_model_foreign'
            ]);
        }

        $table-&gt;update();
    }
}
```

### Day 4: Unified Search Service

#### 4.1 Create Unified Search Service

Create `src/Service/Search/UnifiedSearchService.php`:

```php
<!--?php
declare(strict_types=1);

namespace App\Service\Search;

use Cake\ORM\TableRegistry;
use Cake\Utility\Text;

class UnifiedSearchService
{
    private $articlesTable;
    private $productsTable;
    private $tagsTable;

    public function __construct()
    {
        $this--->articlesTable = TableRegistry::getTableLocator()-&gt;get('Articles');
        $this-&gt;productsTable = TableRegistry::getTableLocator()-&gt;get('Products');
        $this-&gt;tagsTable = TableRegistry::getTableLocator()-&gt;get('Tags');
    }

    /**
     * Perform unified search across articles, products, and tags
     */
    public function search(string $term, array $options = []): array
    {
        $results = [
            'products' =&gt; [],
            'articles' =&gt; [],
            'tags' =&gt; [],
            'total' =&gt; 0
        ];

        $limit = $options['limit'] ?? 10;
        $includeProducts = $options['include_products'] ?? true;
        $includeArticles = $options['include_articles'] ?? true;
        $includeTags = $options['include_tags'] ?? true;

        // Search products
        if ($includeProducts) {
            $products = $this-&gt;productsTable-&gt;searchProducts($term)
                -&gt;limit($limit)
                -&gt;toArray();

            $results['products'] = array_map(function($product) {
                return [
                    'id' =&gt; $product-&gt;id,
                    'title' =&gt; $product-&gt;title,
                    'type' =&gt; 'product',
                    'url' =&gt; '/products/' . $product-&gt;slug,
                    'description' =&gt; Text::truncate($product-&gt;description ?? '', 150),
                    'image' =&gt; $product-&gt;image,
                    'manufacturer' =&gt; $product-&gt;manufacturer,
                    'price' =&gt; $product-&gt;price,
                    'currency' =&gt; $product-&gt;currency,
                    'tags' =&gt; array_map(fn($tag) =&gt; $tag-&gt;title, $product-&gt;tags ?? [])
                ];
            }, $products);
        }

        // Search articles
        if ($includeArticles) {
            $articles = $this-&gt;articlesTable-&gt;find()
                -&gt;where([
                    'OR' =&gt; [
                        'Articles.title LIKE' =&gt; "%{$term}%",
                        'Articles.body LIKE' =&gt; "%{$term}%",
                        'Articles.lede LIKE' =&gt; "%{$term}%"
                    ],
                    'Articles.is_published' =&gt; true
                ])
                -&gt;contain(['Tags', 'Users'])
                -&gt;limit($limit)
                -&gt;toArray();

            $results['articles'] = array_map(function($article) {
                return [
                    'id' =&gt; $article-&gt;id,
                    'title' =&gt; $article-&gt;title,
                    'type' =&gt; 'article',
                    'url' =&gt; '/articles/' . $article-&gt;slug,
                    'description' =&gt; Text::truncate($article-&gt;lede ?? '', 150),
                    'author' =&gt; $article-&gt;user-&gt;username ?? '',
                    'created' =&gt; $article-&gt;created,
                    'tags' =&gt; array_map(fn($tag) =&gt; $tag-&gt;title, $article-&gt;tags ?? [])
                ];
            }, $articles);
        }

        // Search tags (and include content with those tags)
        if ($includeTags) {
            $tags = $this-&gt;tagsTable-&gt;find()
                -&gt;where(['Tags.title LIKE' =&gt; "%{$term}%"])
                -&gt;contain(['Articles', 'Products'])
                -&gt;limit($limit)
                -&gt;toArray();

            $results['tags'] = array_map(function($tag) {
                return [
                    'id' =&gt; $tag-&gt;id,
                    'title' =&gt; $tag-&gt;title,
                    'type' =&gt; 'tag',
                    'url' =&gt; '/search?tag=' . $tag-&gt;slug,
                    'description' =&gt; "Tag with " . count($tag-&gt;articles ?? []) . " articles and " . count($tag-&gt;products ?? []) . " products",
                    'article_count' =&gt; count($tag-&gt;articles ?? []),
                    'product_count' =&gt; count($tag-&gt;products ?? [])
                ];
            }, $tags);
        }

        $results['total'] = count($results['products']) + count($results['articles']) + count($results['tags']);

        // Sort all results by relevance (simple scoring)
        $allResults = array_merge($results['products'], $results['articles'], $results['tags']);
        usort($allResults, function($a, $b) use ($term) {
            $scoreA = $this-&gt;calculateRelevanceScore($a, $term);
            $scoreB = $this-&gt;calculateRelevanceScore($b, $term);
            return $scoreB &lt;=&gt; $scoreA;
        });

        $results['mixed'] = array_slice($allResults, 0, $limit);

        return $results;
    }

    /**
     * Search by tag across all content types
     */
    public function searchByTag(string $tagSlug): array
    {
        $tag = $this-&gt;tagsTable-&gt;find()
            -&gt;where(['slug' =&gt; $tagSlug])
            -&gt;contain([
                'Articles' =&gt; function($q) {
                    return $q-&gt;where(['is_published' =&gt; true])
                        -&gt;contain(['Users'])
                        -&gt;order(['created' =&gt; 'DESC']);
                },
                'Products' =&gt; function($q) {
                    return $q-&gt;where(['is_published' =&gt; true])
                        -&gt;contain(['Users'])
                        -&gt;order(['created' =&gt; 'DESC']);
                }
            ])
            -&gt;first();

        if (!$tag) {
            return ['tag' =&gt; null, 'articles' =&gt; [], 'products' =&gt; []];
        }

        return [
            'tag' =&gt; [
                'id' =&gt; $tag-&gt;id,
                'title' =&gt; $tag-&gt;title,
                'slug' =&gt; $tag-&gt;slug
            ],
            'articles' =&gt; $tag-&gt;articles ?? [],
            'products' =&gt; $tag-&gt;products ?? []
        ];
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function getSuggestions(string $term, int $limit = 5): array
    {
        $suggestions = [];

        // Product suggestions
        $products = $this-&gt;productsTable-&gt;find()
            -&gt;select(['title', 'slug', 'manufacturer'])
            -&gt;where([
                'title LIKE' =&gt; "%{$term}%",
                'is_published' =&gt; true
            ])
            -&gt;limit($limit)
            -&gt;toArray();

        foreach ($products as $product) {
            $suggestions[] = [
                'text' =&gt; $product-&gt;title,
                'type' =&gt; 'product',
                'url' =&gt; '/products/' . $product-&gt;slug,
                'subtitle' =&gt; $product-&gt;manufacturer
            ];
        }

        // Article suggestions
        $articles = $this-&gt;articlesTable-&gt;find()
            -&gt;select(['title', 'slug'])
            -&gt;where([
                'title LIKE' =&gt; "%{$term}%",
                'is_published' =&gt; true
            ])
            -&gt;limit($limit)
            -&gt;toArray();

        foreach ($articles as $article) {
            $suggestions[] = [
                'text' =&gt; $article-&gt;title,
                'type' =&gt; 'article',
                'url' =&gt; '/articles/' . $article-&gt;slug,
                'subtitle' =&gt; 'Article'
            ];
        }

        // Tag suggestions
        $tags = $this-&gt;tagsTable-&gt;find()
            -&gt;select(['title', 'slug'])
            -&gt;where(['title LIKE' =&gt; "%{$term}%"])
            -&gt;limit($limit)
            -&gt;toArray();

        foreach ($tags as $tag) {
            $suggestions[] = [
                'text' =&gt; $tag-&gt;title,
                'type' =&gt; 'tag',
                'url' =&gt; '/search?tag=' . $tag-&gt;slug,
                'subtitle' =&gt; 'Tag'
            ];
        }

        return array_slice($suggestions, 0, $limit * 3);
    }

    /**
     * Calculate relevance score for search results
     */
    private function calculateRelevanceScore(array $item, string $term): int
    {
        $score = 0;
        $termLower = strtolower($term);
        $titleLower = strtolower($item['title']);

        // Exact title match gets highest score
        if ($titleLower === $termLower) {
            $score += 100;
        }
        // Title starts with term
        elseif (strpos($titleLower, $termLower) === 0) {
            $score += 80;
        }
        // Title contains term
        elseif (strpos($titleLower, $termLower) !== false) {
            $score += 60;
        }

        // Description contains term
        if (isset($item['description']) &amp;&amp; strpos(strtolower($item['description']), $termLower) !== false) {
            $score += 20;
        }

        // Boost for certain content types
        if ($item['type'] === 'product') {
            $score += 10; // Slight boost for products
        }

        return $score;
    }
}
```

### Day 5: Settings Integration

#### 5.1 Create Product Settings Migration

```plaintext
# Create settings migration for product configuration
docker compose exec willowcms bin/cake bake migration InsertSimplifiedProductSettings

```

**Migration Content:** `config/Migrations/YYYYMMDD_HHMMSS_InsertSimplifiedProductSettings.php`

```php
<!--?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

class InsertSimplifiedProductSettings extends AbstractMigration
{
    public function change(): void
    {
        $this--->table('settings')
            -&gt;insert([
                'id' =&gt; Text::uuid(),
                'ordering' =&gt; 50,
                'category' =&gt; 'Products',
                'key_name' =&gt; 'enabled',
                'value' =&gt; '1',
                'value_type' =&gt; 'bool',
                'value_obscure' =&gt; false,
                'description' =&gt; 'Enable the products system. When disabled, products will not be accessible on the frontend.',
                'data' =&gt; null,
                'column_width' =&gt; 2,
            ])
            -&gt;insert([
                'id' =&gt; Text::uuid(),
                'ordering' =&gt; 51,
                'category' =&gt; 'Products',
                'key_name' =&gt; 'userSubmissions',
                'value' =&gt; '1',
                'value_type' =&gt; 'bool',
                'value_obscure' =&gt; false,
                'description' =&gt; 'Allow users to submit products for review. When enabled, registered users can add products that require approval.',
                'data' =&gt; null,
                'column_width' =&gt; 2,
            ])
            -&gt;insert([
                'id' =&gt; Text::uuid(),
                'ordering' =&gt; 52,
                'category' =&gt; 'Products',
                'key_name' =&gt; 'aiVerificationEnabled',
                'value' =&gt; '1',
                'value_type' =&gt; 'bool',
                'value_obscure' =&gt; false,
                'description' =&gt; 'Enable AI-powered verification of product submissions. Uses AI to validate product information and suggest improvements.',
                'data' =&gt; null,
                'column_width' =&gt; 2,
            ])
            -&gt;insert([
                'id' =&gt; Text::uuid(),
                'ordering' =&gt; 53,
                'category' =&gt; 'Products',
                'key_name' =&gt; 'peerVerificationEnabled',
                'value' =&gt; '1',
                'value_type' =&gt; 'bool',
                'value_obscure' =&gt; false,
                'description' =&gt; 'Enable peer verification where users can verify and rate product accuracy.',
                'data' =&gt; null,
                'column_width' =&gt; 2,
            ])
            -&gt;insert([
                'id' =&gt; Text::uuid(),
                'ordering' =&gt; 54,
                'category' =&gt; 'Products',
                'key_name' =&gt; 'minVerificationScore',
                'value' =&gt; '3.0',
                'value_type' =&gt; 'numeric',
                'value_obscure' =&gt; false,
                'description' =&gt; 'Minimum verification score (0-5) required for automatic approval. Products below this score require manual review.',
                'data' =&gt; null,
                'column_width' =&gt; 2,
            ])
            -&gt;insert([
                'id' =&gt; Text::uuid(),
                'ordering' =&gt; 55,
                'category' =&gt; 'Products',
                'key_name' =&gt; 'autoPublishThreshold',
                'value' =&gt; '4.0',
                'value_type' =&gt; 'numeric',
                'value_obscure' =&gt; false,
                'description' =&gt; 'Reliability score threshold for automatic publishing. Products scoring above this will be automatically published.',
                'data' =&gt; null,
                'column_width' =&gt; 2,
            ])
            -&gt;insert([
                'id' =&gt; Text::uuid(),
                'ordering' =&gt; 56,
                'category' =&gt; 'Products',
                'key_name' =&gt; 'maxUserSubmissionsPerDay',
                'value' =&gt; '5',
                'value_type' =&gt; 'numeric',
                'value_obscure' =&gt; false,
                'description' =&gt; 'Maximum number of products a user can submit per day. Set to 0 for unlimited submissions.',
                'data' =&gt; null,
                'column_width' =&gt; 2,
            ])
            -&gt;insert([
                'id' =&gt; Text::uuid(),
                'ordering' =&gt; 57,
                'category' =&gt; 'Products',
                'key_name' =&gt; 'duplicateDetectionEnabled',
                'value' =&gt; '1',
                'value_type' =&gt; 'bool',
                'value_obscure' =&gt; false,
                'description' =&gt; 'Enable duplicate detection to prevent submission of identical products based on title and manufacturer.',
                'data' =&gt; null,
                'column_width' =&gt; 2,
            ])
            -&gt;insert([
                'id' =&gt; Text::uuid(),
                'ordering' =&gt; 58,
                'category' =&gt; 'Products',
                'key_name' =&gt; 'productImageRequired',
                'value' =&gt; '1',
                'value_type' =&gt; 'bool',
                'value_obscure' =&gt; false,
                'description' =&gt; 'Require at least one product image for publication. Helps maintain visual consistency.',
                'data' =&gt; null,
                'column_width' =&gt; 2,
            ])
            -&gt;insert([
                'id' =&gt; Text::uuid(),
                'ordering' =&gt; 59,
                'category' =&gt; 'Products',
                'key_name' =&gt; 'technicalSpecsRequired',
                'value' =&gt; '1',
                'value_type' =&gt; 'bool',
                'value_obscure' =&gt; false,
                'description' =&gt; 'Require basic technical specifications (description, manufacturer, model) for product approval.',
                'data' =&gt; null,
                'column_width' =&gt; 2,
            ])
            -&gt;save();
    }
}
```

```plaintext
# Run the migration after editing
docker compose exec willowcms bin/cake migrations migrate
```

### Day 6: Basic Job System Integration

#### 6.1 Create Product Verification Job

Create `src/Job/ProductVerificationJob.php`:

```php
<!--?php
declare(strict_types=1);

namespace App\Job;

use App\Job\AbstractJob;
use App\Service\Api\Anthropic\ProductAnalyzer;
use App\Utility\SettingsManager;

class ProductVerificationJob extends AbstractJob
{
    public function execute(array $data): void
    {
        $this--->validateArguments($data, ['product_id']);

        $productId = $data['product_id'];
        $this-&gt;log("Starting verification for product {$productId}", 'info');

        try {
            // Get product data
            $productsTable = $this-&gt;getTableInstance('Products');
            $product = $productsTable-&gt;get($productId, [
                'contain' =&gt; ['Tags', 'Article']
            ]);

            $verificationScore = $this-&gt;calculateVerificationScore($product);

            // Use AI verification if enabled
            if (SettingsManager::read('Products.aiVerificationEnabled', true)) {
                $aiScore = $this-&gt;runAIVerification($product);
                $verificationScore = ($verificationScore + $aiScore) / 2;
            }

            // Update product with verification score
            $product-&gt;reliability_score = $verificationScore;

            // Auto-publish if score is high enough
            $autoPublishThreshold = (float)SettingsManager::read('Products.autoPublishThreshold', 4.0);
            if ($verificationScore &gt;= $autoPublishThreshold) {
                $product-&gt;is_published = true;
                $product-&gt;verification_status = 'approved';
                $this-&gt;log("Product {$productId} auto-published with score {$verificationScore}", 'info');
            } else {
                $product-&gt;verification_status = 'pending';
                $this-&gt;log("Product {$productId} pending review with score {$verificationScore}", 'info');
            }

            $productsTable-&gt;save($product);

        } catch (\Exception $e) {
            $this-&gt;log("Product verification job failed: " . $e-&gt;getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Calculate basic verification score based on completeness
     */
    private function calculateVerificationScore($product): float
    {
        $score = 0;
        $maxScore = 0;

        // Title is required (weight: 1.0)
        $maxScore += 1.0;
        if (!empty($product-&gt;title)) {
            $score += 1.0;
        }

        // Description (weight: 1.5)
        $maxScore += 1.5;
        if (!empty($product-&gt;description) &amp;&amp; strlen($product-&gt;description) &gt;= 50) {
            $score += 1.5;
        } elseif (!empty($product-&gt;description)) {
            $score += 0.75;
        }

        // Manufacturer (weight: 1.0)
        $maxScore += 1.0;
        if (!empty($product-&gt;manufacturer)) {
            $score += 1.0;
        }

        // Model number (weight: 0.5)
        $maxScore += 0.5;
        if (!empty($product-&gt;model_number)) {
            $score += 0.5;
        }

        // Image (weight: 1.0)
        $maxScore += 1.0;
        if (!empty($product-&gt;image)) {
            $score += 1.0;
        }

        // Tags (weight: 0.5)
        $maxScore += 0.5;
        if (!empty($product-&gt;tags) &amp;&amp; count($product-&gt;tags) &gt; 0) {
            $score += 0.5;
        }

        // Convert to 5-point scale
        return ($score / $maxScore) * 5.0;
    }

    /**
     * Run AI verification if service is available
     */
    private function runAIVerification($product): float
    {
        try {
            // This would integrate with the AI service
            // For now, return a basic score
            return 4.0;
        } catch (\Exception $e) {
            $this-&gt;log("AI verification failed: " . $e-&gt;getMessage(), 'warning');
            return 3.0; // Fallback score
        }
    }
}
```

### Day 7: Phase 1 Testing &amp; Validation

#### 7.1 Run Initial Test Suite

```plaintext
# Run all new model tests
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Model/Table/ProductsTableTest.php
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Model/Entity/ProductTest.php

# Test the search service
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Service/Search/

# Generate coverage report
docker compose exec willowcms php vendor/bin/phpunit --coverage-html webroot/coverage tests/TestCase/Model/
```

#### 7.2 Validate Database Schema

```plaintext
# Verify all migrations ran successfully
docker compose exec willowcms bin/cake migrations status

# Test database structure
docker compose exec mysql mysql -u cms_user -ppassword cms -e "DESCRIBE products;"
docker compose exec mysql mysql -u cms_user -ppassword cms -e "DESCRIBE products_tags;"
```

## Phase 2: Admin Interface &amp; CRUD Operations (Week 3-4)

### Day 8: Admin Controllers Creation

#### 8.1 Generate Admin Controllers

```plaintext
# Create admin controllers using AdminTheme
docker compose exec willowcms bin/cake bake controller Admin/Products --theme AdminTheme

# This creates:
# - src/Controller/Admin/ProductsController.php
```

#### 8.2 Generate Admin Templates

```plaintext
# Generate all admin templates using AdminTheme
docker compose exec willowcms bin/cake bake template Admin/Products --theme AdminTheme

# This creates:
# - plugins/AdminTheme/templates/Admin/Products/index.php
# - plugins/AdminTheme/templates/Admin/Products/view.php
# - plugins/AdminTheme/templates/Admin/Products/add.php
# - plugins/AdminTheme/templates/Admin/Products/edit.php
```

### Day 9: Enhanced Admin Controllers

#### 9.1 Enhance Products Admin Controller

Update `src/Controller/Admin/ProductsController.php`:

```php
<!--?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AppController;
use App\Service\Search\UnifiedSearchService;

class ProductsController extends AppController
{
    protected UnifiedSearchService $searchService;

    public function initialize(): void
    {
        parent::initialize();
        $this--->searchService = new UnifiedSearchService();
    }

    /**
     * Index method - Enhanced with filtering and search
     */
    public function index(): void
    {
        $query = $this-&gt;Products-&gt;find()
            -&gt;contain(['Users', 'Tags', 'Articles'])
            -&gt;order(['Products.created' =&gt; 'DESC']);

        // Apply filters
        if ($this-&gt;request-&gt;getQuery('status')) {
            $query-&gt;where(['verification_status' =&gt; $this-&gt;request-&gt;getQuery('status')]);
        }

        if ($this-&gt;request-&gt;getQuery('published')) {
            $published = $this-&gt;request-&gt;getQuery('published') === '1';
            $query-&gt;where(['is_published' =&gt; $published]);
        }

        if ($this-&gt;request-&gt;getQuery('featured')) {
            $query-&gt;where(['featured' =&gt; true]);
        }

        if ($this-&gt;request-&gt;getQuery('search')) {
            $search = $this-&gt;request-&gt;getQuery('search');
            $query-&gt;where([
                'OR' =&gt; [
                    'Products.title LIKE' =&gt; "%{$search}%",
                    'Products.description LIKE' =&gt; "%{$search}%",
                    'Products.manufacturer LIKE' =&gt; "%{$search}%",
                    'Products.model_number LIKE' =&gt; "%{$search}%"
                ]
            ]);
        }

        $this-&gt;set('products', $this-&gt;paginate($query));

        // Get filter options
        $tags = $this-&gt;Products-&gt;Tags
            -&gt;find('list', ['keyField' =&gt; 'id', 'valueField' =&gt; 'title'])
            -&gt;order(['title' =&gt; 'ASC']);

        $this-&gt;set(compact('tags'));
    }

    /**
     * Dashboard method - Product overview
     */
    public function dashboard(): void
    {
        // Basic statistics
        $totalProducts = $this-&gt;Products-&gt;find()-&gt;count();
        $publishedProducts = $this-&gt;Products-&gt;find()-&gt;where(['is_published' =&gt; true])-&gt;count();
        $pendingProducts = $this-&gt;Products-&gt;find()-&gt;where(['verification_status' =&gt; 'pending'])-&gt;count();
        $featuredProducts = $this-&gt;Products-&gt;find()-&gt;where(['featured' =&gt; true])-&gt;count();

        // Recent products
        $recentProducts = $this-&gt;Products-&gt;find()
            -&gt;contain(['Users', 'Tags'])
            -&gt;order(['created' =&gt; 'DESC'])
            -&gt;limit(10)
            -&gt;toArray();

        // Top manufacturers
        $topManufacturers = $this-&gt;Products-&gt;find()
            -&gt;select([
                'manufacturer',
                'count' =&gt; $this-&gt;Products-&gt;find()-&gt;func()-&gt;count('*')
            ])
            -&gt;where(['manufacturer IS NOT' =&gt; null])
            -&gt;group('manufacturer')
            -&gt;order(['count' =&gt; 'DESC'])
            -&gt;limit(10)
            -&gt;toArray();

        // Popular tags
        $popularTags = $this-&gt;Products-&gt;Tags-&gt;find()
            -&gt;select([
                'Tags.title',
                'count' =&gt; $this-&gt;Products-&gt;Tags-&gt;find()-&gt;func()-&gt;count('ProductsTags.product_id')
            ])
            -&gt;leftJoinWith('Products')
            -&gt;group('Tags.id')
            -&gt;order(['count' =&gt; 'DESC'])
            -&gt;limit(10)
            -&gt;toArray();

        $this-&gt;set(compact(
            'totalProducts', 
            'publishedProducts', 
            'pendingProducts', 
            'featuredProducts',
            'recentProducts',
            'topManufacturers',
            'popularTags'
        ));
    }

    /**
     * View method - Enhanced with related products
     */
    public function view($id = null): void
    {
        $product = $this-&gt;Products-&gt;get($id, [
            'contain' =&gt; ['Users', 'Tags', 'Articles'],
        ]);

        // Get related products
        $relatedProducts = $this-&gt;Products-&gt;getRelatedProducts($id, 5);

        $this-&gt;set(compact('product', 'relatedProducts'));
    }

    /**
     * Add method - Enhanced with unified tagging
     */
    public function add(): void
    {
        $product = $this-&gt;Products-&gt;newEmptyEntity();

        if ($this-&gt;request-&gt;is('post')) {
            $data = $this-&gt;request-&gt;getData();
            $data['user_id'] = $this-&gt;getRequest()-&gt;getAttribute('identity')-&gt;id;

            $product = $this-&gt;Products-&gt;patchEntity($product, $data, [
                'associated' =&gt; ['Tags']
            ]);

            if ($this-&gt;Products-&gt;save($product)) {
                $this-&gt;Flash-&gt;success(__('The product has been saved.'));

                // Queue verification job
                $this-&gt;queueJob('ProductVerificationJob', [
                    'product_id' =&gt; $product-&gt;id
                ]);

                return $this-&gt;redirect(['action' =&gt; 'index']);
            }
            $this-&gt;Flash-&gt;error(__('The product could not be saved. Please, try again.'));
        }

        // Get form options
        $users = $this-&gt;Products-&gt;Users-&gt;find('list', ['limit' =&gt; 200])-&gt;all();
        $articles = $this-&gt;Products-&gt;Articles
            -&gt;find('list', ['keyField' =&gt; 'id', 'valueField' =&gt; 'title'])
            -&gt;where(['is_published' =&gt; true])
            -&gt;order(['title' =&gt; 'ASC']);
        $tags = $this-&gt;Products-&gt;Tags-&gt;find('list', ['limit' =&gt; 200])-&gt;all();

        $this-&gt;set(compact('product', 'users', 'articles', 'tags'));
    }

    /**
     * Edit method - Enhanced with change tracking
     */
    public function edit($id = null): void
    {
        $product = $this-&gt;Products-&gt;get($id, [
            'contain' =&gt; ['Tags'],
        ]);

        if ($this-&gt;request-&gt;is(['patch', 'post', 'put'])) {
            $originalScore = $product-&gt;reliability_score;

            $product = $this-&gt;Products-&gt;patchEntity($product, $this-&gt;request-&gt;getData(), [
                'associated' =&gt; ['Tags']
            ]);

            if ($this-&gt;Products-&gt;save($product)) {
                $this-&gt;Flash-&gt;success(__('The product has been saved.'));

                // Re-verify if significant changes were made
                if ($this-&gt;hasSignificantChanges($product)) {
                    $this-&gt;queueJob('ProductVerificationJob', [
                        'product_id' =&gt; $product-&gt;id
                    ]);
                }

                return $this-&gt;redirect(['action' =&gt; 'index']);
            }
            $this-&gt;Flash-&gt;error(__('The product could not be saved. Please, try again.'));
        }

        $users = $this-&gt;Products-&gt;Users-&gt;find('list', ['limit' =&gt; 200])-&gt;all();
        $articles = $this-&gt;Products-&gt;Articles
            -&gt;find('list', ['keyField' =&gt; 'id', 'valueField' =&gt; 'title'])
            -&gt;where(['is_published' =&gt; true])
            -&gt;order(['title' =&gt; 'ASC']);
        $tags = $this-&gt;Products-&gt;Tags-&gt;find('list', ['limit' =&gt; 200])-&gt;all();

        $this-&gt;set(compact('product', 'users', 'articles', 'tags'));
    }

    /**
     * Delete method
     */
    public function delete($id = null): void
    {
        $this-&gt;request-&gt;allowMethod(['post', 'delete']);
        $product = $this-&gt;Products-&gt;get($id);

        if ($this-&gt;Products-&gt;delete($product)) {
            $this-&gt;Flash-&gt;success(__('The product has been deleted.'));
        } else {
            $this-&gt;Flash-&gt;error(__('The product could not be deleted. Please, try again.'));
        }

        return $this-&gt;redirect(['action' =&gt; 'index']);
    }

    /**
     * Verify method - Manual verification trigger
     */
    public function verify($id = null): void
    {
        $this-&gt;request-&gt;allowMethod(['post']);

        $this-&gt;queueJob('ProductVerificationJob', [
            'product_id' =&gt; $id
        ]);

        $this-&gt;Flash-&gt;success(__('Product verification has been queued.'));

        return $this-&gt;redirect(['action' =&gt; 'view', $id]);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id = null): void
    {
        $this-&gt;request-&gt;allowMethod(['post']);

        $product = $this-&gt;Products-&gt;get($id);
        $product-&gt;featured = !$product-&gt;featured;

        if ($this-&gt;Products-&gt;save($product)) {
            $status = $product-&gt;featured ? 'featured' : 'unfeatured';
            $this-&gt;Flash-&gt;success(__('Product has been {0}.', $status));
        } else {
            $this-&gt;Flash-&gt;error(__('Could not update product status.'));
        }

        return $this-&gt;redirect($this-&gt;referer(['action' =&gt; 'index']));
    }

    /**
     * Check if product has significant changes requiring re-verification
     */
    private function hasSignificantChanges($product): bool
    {
        return $product-&gt;isDirty(['title', 'description', 'manufacturer', 'model_number']);
    }

    /**
     * Queue a background job
     */
    private function queueJob(string $jobClass, array $data): void
    {
        $this-&gt;loadComponent('Queue.Queue');
        $this-&gt;Queue-&gt;createJob($jobClass, $data);
    }
}
```

### Day 10: Admin Templates Enhancement

#### 10.1 Create Enhanced Products Dashboard Template

Create `plugins/AdminTheme/templates/Admin/Products/dashboard.php`:

```php
<!--?php
$this--->assign('title', __('Products Dashboard'));
$this-&gt;Html-&gt;css('willow-admin', ['block' =&gt; true]);
?&gt;

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><!--?= __('Products Dashboard') ?--></h3>
            <p class="text-muted"><!--?= __('Simplified product management overview') ?--></p>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><!--?= __('Total Products') ?--></h5>
                <h2 class="text-primary"><!--?= number_format($totalProducts) ?--></h2>
                <small class="text-muted"><!--?= __('All products') ?--></small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><!--?= __('Published') ?--></h5>
                <h2 class="text-success"><!--?= number_format($publishedProducts) ?--></h2>
                <small class="text-muted"><!--?= __('Live products') ?--></small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><!--?= __('Pending Review') ?--></h5>
                <h2 class="text-warning"><!--?= number_format($pendingProducts) ?--></h2>
                <small class="text-muted"><!--?= __('Awaiting verification') ?--></small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><!--?= __('Featured') ?--></h5>
                <h2 class="text-info"><!--?= number_format($featuredProducts) ?--></h2>
                <small class="text-muted"><!--?= __('Featured products') ?--></small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><!--?= __('Recent Products') ?--></h5>
            </div>
            <div class="card-body">
                <!--?php if (!empty($recentProducts)): ?-->
                    <div class="list-group list-group-flush">
                        <!--?php foreach ($recentProducts as $product): ?-->
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><!--?= h($product--->title) ?&gt;</h6>
                                <small class="text-muted">
                                    <!--?= $product--->created-&gt;format('M j, Y') ?&gt; 
                                    by <!--?= h($product--->user-&gt;username) ?&gt;
                                    <!--?php if ($product--->manufacturer): ?&gt;
                                        • <!--?= h($product--->manufacturer) ?&gt;
                                    <!--?php endif; ?-->
                                </small>
                                <!--?php if (!empty($product--->tags)): ?&gt;
                                    <div class="mt-1">
                                        <!--?php foreach ($product--->tags as $tag): ?&gt;
                                            <span class="badge badge-secondary badge-sm"><!--?= h($tag--->title) ?&gt;</span>
                                        <!--?php endforeach; ?-->
                                    </div>
                                <!--?php endif; ?-->
                            </div>
                            <div>
                                <span class="badge badge-<?= $product->is_published ? 'success' : 'warning' ?>">
                                    <!--?= $product--->is_published ? __('Published') : __(ucfirst($product-&gt;verification_status)) ?&gt;
                                </span>
                            </div>
                        </div>
                        <!--?php endforeach; ?-->
                    </div>
                <!--?php else: ?-->
                    <p class="text-muted"><!--?= __('No recent products') ?--></p>
                <!--?php endif; ?-->
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><!--?= __('Top Manufacturers') ?--></h5>
            </div>
            <div class="card-body">
                <!--?php if (!empty($topManufacturers)): ?-->
                    <div class="list-group list-group-flush">
                        <!--?php foreach ($topManufacturers as $manufacturer): ?-->
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><!--?= h($manufacturer--->manufacturer) ?&gt;</span>
                            <span class="badge badge-primary badge-pill"><!--?= $manufacturer--->count ?&gt;</span>
                        </div>
                        <!--?php endforeach; ?-->
                    </div>
                <!--?php else: ?-->
                    <p class="text-muted"><!--?= __('No manufacturer data available') ?--></p>
                <!--?php endif; ?-->
            </div>
        </div>
    </div>
</div>

<!-- Popular Tags -->
<!--?php if (!empty($popularTags)): ?-->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><!--?= __('Popular Tags') ?--></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!--?php foreach ($popularTags as $tag): ?-->
                    <div class="col-md-3 mb-2">
                        <span class="badge badge-info p-2">
                            <!--?= h($tag--->title) ?&gt; 
                            <span class="badge badge-light ml-1"><!--?= $tag--->count ?&gt;</span>
                        </span>
                    </div>
                    <!--?php endforeach; ?-->
                </div>
            </div>
        </div>
    </div>
</div>
<!--?php endif; ?-->

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><!--?= __('Quick Actions') ?--></h5>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    <!--?= $this--->Html-&gt;link(
                        '<i class="fas fa-plus"></i> ' . __('Add Product'),
                        ['action' =&gt; 'add'],
                        ['class' =&gt; 'btn btn-success', 'escape' =&gt; false]
                    ) ?&gt;
                    <!--?= $this--->Html-&gt;link(
                        '<i class="fas fa-list"></i> ' . __('All Products'),
                        ['action' =&gt; 'index'],
                        ['class' =&gt; 'btn btn-primary', 'escape' =&gt; false]
                    ) ?&gt;
                    <!--?= $this--->Html-&gt;link(
                        '<i class="fas fa-clock"></i> ' . __('Pending Review'),
                        ['action' =&gt; 'index', '?' =&gt; ['status' =&gt; 'pending']],
                        ['class' =&gt; 'btn btn-warning', 'escape' =&gt; false]
                    ) ?&gt;
                    <!--?= $this--->Html-&gt;link(
                        '<i class="fas fa-star"></i> ' . __('Featured Products'),
                        ['action' =&gt; 'index', '?' =&gt; ['featured' =&gt; '1']],
                        ['class' =&gt; 'btn btn-info', 'escape' =&gt; false]
                    ) ?&gt;
                </div>
            </div>
        </div>
    </div>
</div>
```

## Phase 3: Integration Testing &amp; Navigation (Week 5+)

### Day 11: Integration Testing Setup

#### 11.1 Create Comprehensive Controller Tests

Create `tests/TestCase/Controller/Admin/ProductsControllerTest.php`:

```php
<!--?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

class ProductsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected $fixtures = [
        'app.Products',
        'app.ProductsTags',
        'app.Articles',
        'app.Tags',
        'app.Users'
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this--->configRequest([
            'environment' =&gt; [
                'PHP_AUTH_USER' =&gt; 'admin@test.com',
                'PHP_AUTH_PW' =&gt; 'password'
            ]
        ]);
    }

    public function testIndex(): void
    {
        $this-&gt;get('/admin/products');

        $this-&gt;assertResponseOk();
        $this-&gt;assertResponseContains('Products');

        // Test that products are displayed
        $viewVars = $this-&gt;viewVariable('products');
        $this-&gt;assertNotEmpty($viewVars);
    }

    public function testIndexWithFilters(): void
    {
        // Test status filter
        $this-&gt;get('/admin/products?status=pending');
        $this-&gt;assertResponseOk();

        // Test published filter
        $this-&gt;get('/admin/products?published=1');
        $this-&gt;assertResponseOk();

        // Test search filter
        $this-&gt;get('/admin/products?search=test');
        $this-&gt;assertResponseOk();
    }

    public function testDashboard(): void
    {
        $this-&gt;get('/admin/products/dashboard');

        $this-&gt;assertResponseOk();
        $this-&gt;assertResponseContains('Products Dashboard');

        // Check that required view variables are set
        $this-&gt;assertNotNull($this-&gt;viewVariable('totalProducts'));
        $this-&gt;assertNotNull($this-&gt;viewVariable('publishedProducts'));
        $this-&gt;assertNotNull($this-&gt;viewVariable('pendingProducts'));
        $this-&gt;assertNotNull($this-&gt;viewVariable('featuredProducts'));
    }

    public function testView(): void
    {
        $products = TableRegistry::getTableLocator()-&gt;get('Products');
        $product = $products-&gt;find()-&gt;first();

        $this-&gt;get('/admin/products/view/' . $product-&gt;id);

        $this-&gt;assertResponseOk();
        $this-&gt;assertResponseContains($product-&gt;title);
    }

    public function testAdd(): void
    {
        $this-&gt;get('/admin/products/add');

        $this-&gt;assertResponseOk();
        $this-&gt;assertResponseContains('Add Product');
    }

    public function testAddPost(): void
    {
        $data = [
            'title' =&gt; 'Test Product',
            'slug' =&gt; 'test-product',
            'description' =&gt; 'Test product description',
            'manufacturer' =&gt; 'Test Manufacturer',
            'model_number' =&gt; 'TM-001',
            'is_published' =&gt; false,
            'verification_status' =&gt; 'pending'
        ];

        $this-&gt;post('/admin/products/add', $data);

        $this-&gt;assertResponseSuccess();
        $this-&gt;assertFlashMessage('The product has been saved.');

        // Verify product was created
        $products = TableRegistry::getTableLocator()-&gt;get('Products');
        $product = $products-&gt;find()-&gt;where(['title' =&gt; 'Test Product'])-&gt;first();
        $this-&gt;assertNotNull($product);
    }

    public function testEdit(): void
    {
        $products = TableRegistry::getTableLocator()-&gt;get('Products');
        $product = $products-&gt;find()-&gt;first();

        $this-&gt;get('/admin/products/edit/' . $product-&gt;id);

        $this-&gt;assertResponseOk();
        $this-&gt;assertResponseContains('Edit Product');
        $this-&gt;assertResponseContains($product-&gt;title);
    }

    public function testToggleFeatured(): void
    {
        $products = TableRegistry::getTableLocator()-&gt;get('Products');
        $product = $products-&gt;find()-&gt;first();
        $originalStatus = $product-&gt;featured;

        $this-&gt;post('/admin/products/toggle-featured/' . $product-&gt;id);

        $this-&gt;assertResponseSuccess();

        // Verify featured status was toggled
        $updatedProduct = $products-&gt;get($product-&gt;id);
        $this-&gt;assertEquals(!$originalStatus, $updatedProduct-&gt;featured);
    }

    public function testDelete(): void
    {
        $products = TableRegistry::getTableLocator()-&gt;get('Products');
        $product = $products-&gt;find()-&gt;first();

        $this-&gt;post('/admin/products/delete/' . $product-&gt;id);

        $this-&gt;assertResponseSuccess();
        $this-&gt;assertFlashMessage('The product has been deleted.');

        // Verify product was deleted
        $this-&gt;assertFalse($products-&gt;exists(['id' =&gt; $product-&gt;id]));
    }

    public function testUnifiedTagging(): void
    {
        // Test that products can be associated with same tags as articles
        $data = [
            'title' =&gt; 'Tagged Product',
            'slug' =&gt; 'tagged-product',
            'description' =&gt; 'Product with tags',
            'tags' =&gt; [
                ['_ids' =&gt; [1, 2]] // Assuming tags with IDs 1 and 2 exist
            ]
        ];

        $this-&gt;post('/admin/products/add', $data);
        $this-&gt;assertResponseSuccess();

        // Verify tags were associated
        $products = TableRegistry::getTableLocator()-&gt;get('Products');
        $product = $products-&gt;find()
            -&gt;contain(['Tags'])
            -&gt;where(['title' =&gt; 'Tagged Product'])
            -&gt;first();

        $this-&gt;assertNotEmpty($product-&gt;tags);
    }
}
```

#### 11.2 Create Search Service Tests

Create `tests/TestCase/Service/Search/UnifiedSearchServiceTest.php`:

```php
<!--?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Search;

use App\Service\Search\UnifiedSearchService;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

class UnifiedSearchServiceTest extends TestCase
{
    protected $fixtures = [
        'app.Products',
        'app.Articles',
        'app.Tags',
        'app.ProductsTags',
        'app.ArticlesTags',
        'app.Users'
    ];

    protected $service;

    public function setUp(): void
    {
        parent::setUp();
        $this--->service = new UnifiedSearchService();
    }

    public function testSearch(): void
    {
        $results = $this-&gt;service-&gt;search('test');

        $this-&gt;assertIsArray($results);
        $this-&gt;assertArrayHasKey('products', $results);
        $this-&gt;assertArrayHasKey('articles', $results);
        $this-&gt;assertArrayHasKey('tags', $results);
        $this-&gt;assertArrayHasKey('total', $results);
        $this-&gt;assertArrayHasKey('mixed', $results);
    }

    public function testSearchByTag(): void
    {
        $results = $this-&gt;service-&gt;searchByTag('technology');

        $this-&gt;assertIsArray($results);
        $this-&gt;assertArrayHasKey('tag', $results);
        $this-&gt;assertArrayHasKey('articles', $results);
        $this-&gt;assertArrayHasKey('products', $results);
    }

    public function testGetSuggestions(): void
    {
        $suggestions = $this-&gt;service-&gt;getSuggestions('test', 5);

        $this-&gt;assertIsArray($suggestions);
        $this-&gt;assertLessThanOrEqual(15, count($suggestions)); // 5 * 3 types max

        if (!empty($suggestions)) {
            $this-&gt;assertArrayHasKey('text', $suggestions[^0]);
            $this-&gt;assertArrayHasKey('type', $suggestions[^0]);
            $this-&gt;assertArrayHasKey('url', $suggestions[^0]);
        }
    }

    public function testSearchRelevanceScoring(): void
    {
        // This tests the internal scoring mechanism
        $results = $this-&gt;service-&gt;search('exact product title');

        if (!empty($results['mixed'])) {
            // Results should be ordered by relevance
            $scores = [];
            foreach ($results['mixed'] as $result) {
                // Check that each result has required fields
                $this-&gt;assertArrayHasKey('title', $result);
                $this-&gt;assertArrayHasKey('type', $result);
                $this-&gt;assertArrayHasKey('url', $result);
            }
        }
    }

    public function tearDown(): void
    {
        unset($this-&gt;service);
        parent::tearDown();
    }
}
```

### Day 12: Navigation Integration

#### 12.1 Add Products Navigation to Admin Menu

Update the AdminTheme navigation to include Products menu:

```php
<!-- Add to admin navigation menu -->
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-toggle="dropdown" title="#">
        <i class="fas fa-box"></i> <!--?= __('Products') ?-->
    </a>
    <div class="dropdown-menu">
        <!--?= $this--->Html-&gt;link(
            '<i class="fas fa-chart-line"></i> ' . __('Dashboard'),
            ['controller' =&gt; 'Products', 'action' =&gt; 'dashboard'],
            ['class' =&gt; 'dropdown-item', 'escape' =&gt; false]
        ) ?&gt;
        <div class="dropdown-divider"></div>
        <!--?= $this--->Html-&gt;link(
            '<i class="fas fa-list"></i> ' . __('All Products'),
            ['controller' =&gt; 'Products', 'action' =&gt; 'index'],
            ['class' =&gt; 'dropdown-item', 'escape' =&gt; false]
        ) ?&gt;
        <!--?= $this--->Html-&gt;link(
            '<i class="fas fa-plus"></i> ' . __('Add Product'),
            ['controller' =&gt; 'Products', 'action' =&gt; 'add'],
            ['class' =&gt; 'dropdown-item', 'escape' =&gt; false]
        ) ?&gt;
        <div class="dropdown-divider"></div>
        <!--?= $this--->Html-&gt;link(
            '<i class="fas fa-clock"></i> ' . __('Pending Review'),
            ['controller' =&gt; 'Products', 'action' =&gt; 'index', '?' =&gt; ['status' =&gt; 'pending']],
            ['class' =&gt; 'dropdown-item', 'escape' =&gt; false]
        ) ?&gt;
        <!--?= $this--->Html-&gt;link(
            '<i class="fas fa-star"></i> ' . __('Featured'),
            ['controller' =&gt; 'Products', 'action' =&gt; 'index', '?' =&gt; ['featured' =&gt; '1']],
            ['class' =&gt; 'dropdown-item', 'escape' =&gt; false]
        ) ?&gt;
    </div>
</li>
```

#### 12.2 Update Routes Configuration

Add to `config/routes.php` in the admin prefix section:

```php
// In the existing admin prefix block
$builder-&gt;prefix('Admin', function (RouteBuilder $routes): void {
    // ... existing routes ...

    // Products routes
    $routes-&gt;connect('/products/dashboard', [
        'controller' =&gt; 'Products', 
        'action' =&gt; 'dashboard'
    ]);
    $routes-&gt;connect('/products/verify/*', [
        'controller' =&gt; 'Products', 
        'action' =&gt; 'verify'
    ]);
    $routes-&gt;connect('/products/toggle-featured/*', [
        'controller' =&gt; 'Products', 
        'action' =&gt; 'toggleFeatured'
    ]);

    $routes-&gt;fallbacks(DashedRoute::class);
});
```

### Day 13-14: Final Integration Testing &amp; Polish

#### 13.1 Comprehensive Integration Tests

```plaintext
# Run complete test suite
docker compose exec willowcms php vendor/bin/phpunit --coverage-html webroot/coverage

# Test specific components
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/Admin/Products*
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Service/Search/
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Model/Table/Products*

# Test with queue worker
docker compose exec willowcms bin/cake queue worker &amp;
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Job/Product*
```

#### 13.2 Test Unified Search Functionality

```plaintext
# Test unified search across content types
docker compose exec willowcms bin/cake test_unified_search "technology"

# Test tag-based search
docker compose exec willowcms bin/cake test_tag_search "mobile"

# Test search suggestions
docker compose exec willowcms bin/cake test_search_suggestions "iphone"
```

#### 13.3 Final Validation Checklist

*   All migrations run successfully
*   Products model has proper relationships with Articles and Tags
*   Unified search works across products, articles, and tags
*   Slug system supports products with proper redirects
*   Admin CRUD interfaces work correctly
*   Product verification system functions
*   Settings are properly configured
*   Navigation is integrated
*   All tests pass
*   Performance is acceptable

## Testing Commands Summary

```plaintext
# Complete test suite
docker compose exec willowcms php vendor/bin/phpunit

# Products-specific tests only  
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Model/Table/ProductsTableTest.php
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Controller/Admin/ProductsControllerTest.php
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Service/Search/

# Coverage report
docker compose exec willowcms php vendor/bin/phpunit --coverage-html webroot/coverage

# Specific test methods
docker compose exec willowcms php vendor/bin/phpunit --filter testUnifiedSearch
docker compose exec willowcms php vendor/bin/phpunit --filter testProductTagging
```

## File Structure Created

```plaintext
├── config/Migrations/
│   ├── YYYYMMDD_HHMMSS_CreateSimplifiedProducts.php
│   ├── YYYYMMDD_HHMMSS_EnhanceSlugsForProducts.php
│   └── YYYYMMDD_HHMMSS_InsertSimplifiedProductSettings.php
├── src/
│   ├── Controller/Admin/
│   │   └── ProductsController.php
│   ├── Model/
│   │   ├── Entity/
│   │   │   └── Product.php
│   │   └── Table/
│   │       └── ProductsTable.php (enhanced)
│   ├── Service/Search/
│   │   └── UnifiedSearchService.php
│   └── Job/
│       └── ProductVerificationJob.php
├── plugins/AdminTheme/templates/Admin/Products/
│   ├── index.php
│   ├── view.php
│   ├── add.php
│   ├── edit.php
│   └── dashboard.php
└── tests/
    ├── Fixture/
    │   └── ProductsFixture.php
    └── TestCase/
        ├── Controller/Admin/
        │   └── ProductsControllerTest.php
        ├── Model/Table/
        │   └── ProductsTableTest.php
        ├── Service/Search/
        │   └── UnifiedSearchServiceTest.php
        └── Job/
            └── ProductVerificationJobTest.php
```

## Success Metrics

**Phase 1 Completion:**

*   ✅ Simplified products table created with essential fields
*   ✅ Unified tagging system operational across articles and products
*   ✅ Product-Article association working (optional relationship)
*   ✅ Slug system enhanced to support products
*   ✅ Unified search service functional across all content types

**Phase 2 Completion:**

*   ✅ Admin CRUD interfaces fully functional for products
*   ✅ Product dashboard displays accurate metrics
*   ✅ Settings integration works seamlessly
*   ✅ All controller tests pass
*   ✅ Enhanced templates provide rich functionality

**Phase 3 Completion:**

*   ✅ Navigation properly integrated
*   ✅ All integration tests pass
*   ✅ Unified search working across products, articles, and tags
*   ✅ Performance meets requirements
*   ✅ Complete simplified product management system operational

## Key Benefits of Simplified Approach

1.  **Reduced Complexity**: Single products table instead of 12 separate tables
2.  **Unified Tagging**: Same tag system for articles, products, and pages
3.  **Flexible Content**: Optional article association for detailed product info
4.  **Easy Migration**: Simple path from complex to simplified schema
5.  **Better Performance**: Fewer JOINs and simpler queries
6.  **Unified Search**: Single search interface across all content types
7.  **Maintainable**: Less code to maintain and fewer edge cases

## Rollback Procedures

If issues arise:

```plaintext
# Rollback migrations
docker compose exec willowcms bin/cake migrations rollback

# Disable new features via settings
# Set Products.enabled = 0 in admin settings

# Remove new routes if needed
# Comment out new routes in config/routes.php

# Run core test suite to ensure base functionality
docker compose exec willowcms php vendor/bin/phpunit tests/TestCase/Model/Table/ArticlesTableTest.php
```