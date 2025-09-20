<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;
use Exception;

/**
 * API Products Controller
 *
 * Provides JSON API endpoints for product search and filtering,
 * primarily used by the quiz system for product recommendations.
 */
class ProductsController extends AppController
{
    /**
     * Initialize method
     *
     * Configure API-specific settings
     */
    public function initialize(): void
    {
        parent::initialize();

        // Disable CSRF protection for API endpoints
        $this->getEventManager()->off($this->Csrf);

        // Set JSON response type
        $this->viewBuilder()->setClassName('Json');
        $this->set('_serialize', true);

        // Load the Products table
        $this->loadModel('Products');
    }

    /**
     * Index method - Search and filter products
     *
     * Supports query parameters:
     * - q: Search query for title, description, keywords
     * - tags: Comma-separated list of tag names or IDs
     * - attributes: JSON object with attribute filters
     * - price_min: Minimum price filter
     * - price_max: Maximum price filter
     * - sort: Sort field (title, price, popularity, created)
     * - direction: Sort direction (asc, desc) - defaults to asc
     * - page: Page number for pagination
     * - limit: Results per page (default 20, max 100)
     * - locale: Language locale for translations
     * - featured: Filter for featured products (true/false)
     * - published: Filter for published products (default true)
     *
     * @return void
     */
    public function index(): void
    {
        try {
            $request = $this->getRequest();
            $query = $this->Products->find();

            // Default filters - only show published products
            $published = $request->getQuery('published', true);
            if ($published !== false && $published !== 'false') {
                $query->where(['Products.is_published' => true]);
            }

            // Search query
            $searchQuery = $request->getQuery('q');
            if (!empty($searchQuery)) {
                $query = $this->Products->findSearch($query, ['search' => $searchQuery]);
            }

            // Tag filters
            $tags = $request->getQuery('tags');
            if (!empty($tags)) {
                $tagList = is_array($tags) ? $tags : explode(',', $tags);
                $query = $this->Products->findByTags($query, $tagList);
            }

            // Price filters
            $priceMin = $request->getQuery('price_min');
            $priceMax = $request->getQuery('price_max');

            if (is_numeric($priceMin)) {
                $query->where(['Products.price >=' => (float)$priceMin]);
            }
            if (is_numeric($priceMax)) {
                $query->where(['Products.price <=' => (float)$priceMax]);
            }

            // Featured filter
            $featured = $request->getQuery('featured');
            if ($featured === 'true' || $featured === true) {
                $query->where(['Products.is_featured' => true]);
            }

            // Attribute filters (JSON object)
            $attributes = $request->getQuery('attributes');
            if (!empty($attributes)) {
                if (is_string($attributes)) {
                    $attributes = json_decode($attributes, true);
                }
                if (is_array($attributes)) {
                    foreach ($attributes as $key => $value) {
                        if (!empty($value)) {
                            $query->where([
                                "JSON_EXTRACT(Products.ai_attributes, '$.{$key}')" => $value,
                            ]);
                        }
                    }
                }
            }

            // Sorting
            $sort = $request->getQuery('sort', 'title');
            $direction = $request->getQuery('direction', 'asc');

            $allowedSorts = ['title', 'price', 'popularity', 'created', 'modified'];
            if (in_array($sort, $allowedSorts)) {
                $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
                $query->orderBy(["Products.{$sort}" => $direction]);
            }

            // Pagination
            $page = max(1, (int)$request->getQuery('page', 1));
            $limit = min(100, max(1, (int)$request->getQuery('limit', 20)));

            $this->paginate = [
                'page' => $page,
                'limit' => $limit,
                'maxLimit' => 100,
            ];

            // Execute query with pagination
            $products = $this->paginate($query);
            $paging = $this->getRequest()->getAttribute('paging')['Products'];

            // Format response
            $response = [
                'success' => true,
                'data' => [
                    'products' => $products->toArray(),
                    'pagination' => [
                        'page' => $paging['page'],
                        'limit' => $paging['perPage'],
                        'pages' => $paging['pageCount'],
                        'total' => $paging['count'],
                        'has_next' => $paging['hasNextPage'],
                        'has_prev' => $paging['hasPrevPage'],
                    ],
                ],
                'query_info' => [
                    'search' => $searchQuery,
                    'tags' => $tags,
                    'price_range' => [$priceMin, $priceMax],
                    'sort' => $sort,
                    'direction' => $direction,
                ],
            ];

            $this->set($response);
        } catch (Exception $e) {
            $this->log('Products API index error: ' . $e->getMessage(), 'error');

            $response = [
                'success' => false,
                'error' => [
                    'message' => 'Failed to retrieve products',
                    'code' => 'PRODUCTS_INDEX_ERROR',
                ],
            ];

            if ($this->getRequest()->is('development')) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }

            $this->set($response);
            $this->getResponse()->withStatus(500);
        }
    }

    /**
     * View method - Get a single product by ID or slug
     *
     * @param string|null $id Product ID or slug
     * @return void
     */
    public function view(?string $id = null): void
    {
        try {
            if (empty($id)) {
                throw new NotFoundException(__('Product not found'));
            }

            $query = $this->Products->find()
                ->where(['Products.is_published' => true]);

            // Try to find by ID first, then by slug
            if (preg_match('/^[0-9a-f-]{36}$/i', $id)) {
                // UUID format - search by ID
                $product = $query->where(['Products.id' => $id])->first();
            } else {
                // String format - search by slug
                $product = $query
                    ->contain(['Slugs'])
                    ->matching('Slugs', function ($q) use ($id) {
                        return $q->where(['Slugs.slug' => $id, 'Slugs.is_active' => true]);
                    })
                    ->first();
            }

            if (!$product) {
                throw new NotFoundException(__('Product not found'));
            }

            $response = [
                'success' => true,
                'data' => [
                    'product' => $product->toArray(),
                ],
            ];

            $this->set($response);
        } catch (NotFoundException $e) {
            $response = [
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'PRODUCT_NOT_FOUND',
                ],
            ];

            $this->set($response);
            $this->getResponse()->withStatus(404);
        } catch (Exception $e) {
            $this->log('Products API view error: ' . $e->getMessage(), 'error');

            $response = [
                'success' => false,
                'error' => [
                    'message' => 'Failed to retrieve product',
                    'code' => 'PRODUCT_VIEW_ERROR',
                ],
            ];

            if ($this->getRequest()->is('development')) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }

            $this->set($response);
            $this->getResponse()->withStatus(500);
        }
    }
}
