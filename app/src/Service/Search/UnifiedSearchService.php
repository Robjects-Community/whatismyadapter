<?php
declare(strict_types=1);

namespace App\Service\Search;

use App\Model\Table\ArticlesTable;
use App\Model\Table\ProductsTable;
use App\Model\Table\TagsTable;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;

class UnifiedSearchService
{
    /**
     * @var \App\Model\Table\ArticlesTable
     * Articles table
     */
    private ArticlesTable $articlesTable;

    /**
     * @var \App\Model\Table\ProductsTable
     * Products table
     */
    private ProductsTable $productsTable;

    /**
     * @var \App\Model\Table\TagsTable
     * Tags table
     */
    private TagsTable $tagsTable;

    /**
     * Constructor.
     * Initializes the service with required tables.
     */
    public function __construct()
    {
        $this->articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $this->productsTable = TableRegistry::getTableLocator()->get('Products');
        $this->tagsTable = TableRegistry::getTableLocator()->get('Tags');
    }

    /**
     * Perform unified search across articles, products, and tags
     */
    public function search(string $term, array $options = []): array
    {
        $results = [
            'products' => [],
            'articles' => [],
            'tags' => [],
            'total' => 0,
        ];

        $limit = $options['limit'] ?? 10;
        $includeProducts = $options['include_products'] ?? true;
        $includeArticles = $options['include_articles'] ?? true;
        $includeTags = $options['include_tags'] ?? true;

        // Search products
        if ($includeProducts) {
            $products = $this->productsTable->searchProducts($term)
                ->limit($limit)
                ->toArray();

            $results['products'] = array_map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'type' => 'product',
                    'url' => '/products/' . $product->slug,
                    'description' => Text::truncate($product->description ?? '', 150),
                    'image' => $product->image,
                    'manufacturer' => $product->manufacturer,
                    'price' => $product->price,
                    'currency' => $product->currency,
                    'tags' => array_map(fn($tag) => $tag->title, $product->tags ?? []),
                ];
            }, $products);
        }

        // Search articles
        if ($includeArticles) {
            $articles = $this->articlesTable->find()
                ->where([
                    'OR' => [
                        'Articles.title LIKE' => "%{$term}%",
                        'Articles.body LIKE' => "%{$term}%",
                        'Articles.lede LIKE' => "%{$term}%",
                    ],
                    'Articles.is_published' => true,
                ])
                ->contain(['Tags', 'Users'])
                ->limit($limit)
                ->toArray();

            $results['articles'] = array_map(function ($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'type' => 'article',
                    'url' => '/articles/' . $article->slug,
                    'description' => Text::truncate($article->lede ?? '', 150),
                    'author' => $article->user->username ?? '',
                    'created' => $article->created,
                    'tags' => array_map(fn($tag) => $tag->title, $article->tags ?? []),
                ];
            }, $articles);
        }

        // Search tags (and include content with those tags)
        if ($includeTags) {
            $tags = $this->tagsTable->find()
                ->where(['Tags.title LIKE' => "%{$term}%"])
                ->contain(['Articles', 'Products'])
                ->limit($limit)
                ->toArray();

            $results['tags'] = array_map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'title' => $tag->title,
                    'type' => 'tag',
                    'url' => '/search?tag=' . $tag->slug,
                    'description' => 'Tag with ' . count($tag->articles ?? []) . ' articles and ' .
                        count($tag->products ?? []) . ' products',
                    'article_count' => count($tag->articles ?? []),
                    'product_count' => count($tag->products ?? []),
                ];
            }, $tags);
        }

        $results['total'] = count($results['products']) + count($results['articles']) + count($results['tags']);

        // Sort all results by relevance (simple scoring)
        $allResults = array_merge($results['products'], $results['articles'], $results['tags']);
        usort($allResults, function ($a, $b) use ($term) {
            $scoreA = $this->calculateRelevanceScore($a, $term);
            $scoreB = $this->calculateRelevanceScore($b, $term);

            return $scoreB <=> $scoreA;
        });

        $results['mixed'] = array_slice($allResults, 0, $limit);

        return $results;
    }

    /**
     * Search by tag across all content types
     */
    public function searchByTag(string $tagSlug): array
    {
        $tag = $this->tagsTable->find()
            ->where(['slug' => $tagSlug])
            ->contain([
                'Articles' => function ($q) {
                    return $q->where(['is_published' => true])
                        ->contain(['Users'])
                        ->order(['created' => 'DESC']);
                },
                'Products' => function ($q) {
                    return $q->where(['is_published' => true])
                        ->contain(['Users'])
                        ->order(['created' => 'DESC']);
                },
            ])
            ->first();

        if (!$tag) {
            return ['tag' => null, 'articles' => [], 'products' => []];
        }

        return [
            'tag' => [
                'id' => $tag->id,
                'title' => $tag->title,
                'slug' => $tag->slug,
            ],
            'articles' => $tag->articles ?? [],
            'products' => $tag->products ?? [],
        ];
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function getSuggestions(string $term, int $limit = 5): array
    {
        $suggestions = [];

        // Product suggestions
        $products = $this->productsTable->find()
            ->select(['title', 'slug', 'manufacturer'])
            ->where([
                'title LIKE' => "%{$term}%",
                'is_published' => true,
            ])
            ->limit($limit)
            ->toArray();

        foreach ($products as $product) {
            $suggestions[] = [
                'text' => $product->title,
                'type' => 'product',
                'url' => '/products/' . $product->slug,
                'subtitle' => $product->manufacturer,
            ];
        }

        // Article suggestions
        $articles = $this->articlesTable->find()
            ->select(['title', 'slug'])
            ->where([
                'title LIKE' => "%{$term}%",
                'is_published' => true,
            ])
            ->limit($limit)
            ->toArray();

        foreach ($articles as $article) {
            $suggestions[] = [
                'text' => $article->title,
                'type' => 'article',
                'url' => '/articles/' . $article->slug,
                'subtitle' => 'Article',
            ];
        }

        // Tag suggestions
        $tags = $this->tagsTable->find()
            ->select(['title', 'slug'])
            ->where(['title LIKE' => "%{$term}%"])
            ->limit($limit)
            ->toArray();

        foreach ($tags as $tag) {
            $suggestions[] = [
                'text' => $tag->title,
                'type' => 'tag',
                'url' => '/search?tag=' . $tag->slug,
                'subtitle' => 'Tag',
            ];
        }

        return array_slice($suggestions, 0, $limit * 3);
    }

    /**
     * Calculate relevance score for search results
     * Boosts score based on title, description, and author matches
     */
    private function calculateRelevanceScore(array $item, string $term): int
    {
        $score = 0;
        $termLower = strtolower($term);
        $titleLower = strtolower($item['title']);

        // Exact title match gets highest score
        if ($titleLower === $termLower) {
            $score += 100;
        } elseif (strpos($titleLower, $termLower) === 0) {
            $score += 80;
        } elseif (strpos($titleLower, $termLower) !== false) {
            $score += 60; // Boost for title match
        }

        // Description contains term
        if (isset($item['description']) && strpos(strtolower($item['description']), $termLower) !== false) {
            $score += 20; // Boost for description match
        }
        // Author match (for articles)
        if (
            $item['type'] === 'article' && isset($item['author']) &&
            strpos(strtolower($item['author']), $termLower) !== false
        ) {
            $score += 30; // Boost for author match
        }
        // Boost for certain content types
        if ($item['type'] === 'product') {
            $score += 10; // Slight boost for products
        }

        return $score; // Higher score means more relevant
    }
}
