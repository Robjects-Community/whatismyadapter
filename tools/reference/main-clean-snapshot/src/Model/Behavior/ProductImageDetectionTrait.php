<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\Datasource\EntityInterface;

/**
 * ProductImageDetectionTrait
 *
 * This trait provides methods to detect whether products need image generation.
 * It can be used by both the Products table and related services to determine
 * which products should have AI-generated images created for them.
 *
 * The trait considers multiple factors:
 * - Whether the product has any associated images
 * - Whether image generation is enabled in settings
 * - Whether the product type should have images
 * - Whether there are any restrictions based on content or metadata
 */
trait ProductImageDetectionTrait
{
    /**
     * Check if a product needs an AI-generated image
     *
     * @param \Cake\Datasource\EntityInterface $product Product entity
     * @return bool True if the product needs an image generated
     */
    public function productNeedsImage(EntityInterface $product): bool
    {
        // Check if image generation is enabled globally
        if (!$this->isImageGenerationEnabled()) {
            return false;
        }

        // Check if product already has images
        if ($this->productHasImages($product)) {
            return false;
        }

        // Check if product type should have images
        if (!$this->productTypeShouldHaveImages($product)) {
            return false;
        }

        // Check if product has sufficient content for image generation
        if (!$this->productHasSufficientContent($product)) {
            return false;
        }

        // Check any custom exclusion rules
        if ($this->productExcludedFromGeneration($product)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a product already has associated images
     *
     * @param \Cake\Datasource\EntityInterface $product Product entity
     * @return bool True if product has images
     */
    public function productHasImages(EntityInterface $product): bool
    {
        // Check direct image field
        if (!empty($product->image) && $product->image !== null) {
            return true;
        }

        // Check if images were loaded in the entity
        if (property_exists($product, 'images') && !empty($product->images)) {
            return is_countable($product->images) && count($product->images) > 0;
        }

        // If images weren't loaded, query the association
        if (method_exists($this, 'Images')) {
            $imageCount = $this->Images->find()
                ->matching('Products', function (Query $q) use ($product) {
                    return $q->where(['Products.id' => $product->id]);
                })
                ->count();
            
            return $imageCount > 0;
        }

        // Alternative: check via ProductsImages junction table if it exists
        if (method_exists($this, 'getTableLocator')) {
            try {
                $tableLocator = $this->getTableLocator();
                $imagesTable = $tableLocator->get('Images');
                
                $imageCount = $imagesTable->find()
                    ->matching('Products', function (Query $q) use ($product) {
                        return $q->where(['Products.id' => $product->id]);
                    })
                    ->count();
                
                return $imageCount > 0;
            } catch (\Exception $e) {
                // If there's an error querying, assume no images to be safe
                return false;
            }
        }

        return false;
    }

    /**
     * Check if image generation is enabled in configuration
     *
     * @return bool True if image generation is enabled
     */
    public function isImageGenerationEnabled(): bool
    {
        // Check application configuration
        if (Configure::check('ImageGeneration.enabled')) {
            return (bool)Configure::read('ImageGeneration.enabled');
        }

        // Check if AI services are configured
        $providers = Configure::read('ImageGeneration.providers', []);
        if (empty($providers)) {
            return false;
        }

        // Check if at least one provider is properly configured
        foreach ($providers as $provider => $config) {
            if (!empty($config['enabled']) && !empty($config['api_key'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the product type should have images generated
     *
     * @param \Cake\Datasource\EntityInterface $product Product entity
     * @return bool True if this product type should have images
     */
    public function productTypeShouldHaveImages(EntityInterface $product): bool
    {
        // Check for product categories that shouldn't have images
        $excludedCategories = Configure::read('ImageGeneration.excludedCategories', [
            'service',
            'software-license',
            'digital-download',
            'consultation'
        ]);

        if (!empty($product->category) && in_array(strtolower($product->category), $excludedCategories)) {
            return false;
        }

        // Check for product types that shouldn't have images
        $excludedTypes = Configure::read('ImageGeneration.excludedTypes', [
            'virtual',
            'digital'
        ]);

        if (!empty($product->type) && in_array(strtolower($product->type), $excludedTypes)) {
            return false;
        }

        // Check if product is marked as not requiring images
        if (property_exists($product, 'requires_image') && $product->requires_image === false) {
            return false;
        }

        return true;
    }

    /**
     * Check if product has sufficient content for meaningful image generation
     *
     * @param \Cake\Datasource\EntityInterface $product Product entity
     * @return bool True if product has enough content
     */
    public function productHasSufficientContent(EntityInterface $product): bool
    {
        // Must have a title
        if (empty($product->title) || strlen(trim($product->title)) < 3) {
            return false;
        }

        // At minimum, should have either description or manufacturer
        $hasDescription = !empty($product->description) && strlen(trim($product->description)) > 10;
        $hasManufacturer = !empty($product->manufacturer) && strlen(trim($product->manufacturer)) > 2;
        
        if (!$hasDescription && !$hasManufacturer) {
            return false;
        }

        // Check for placeholder or template content that shouldn't trigger generation
        $placeholderPatterns = [
            '/lorem ipsum/i',
            '/placeholder/i',
            '/coming soon/i',
            '/tbd/i',
            '/to be determined/i',
            '/\[.*\]/i', // Content in square brackets like [Description needed]
        ];

        $contentToCheck = ($product->title ?? '') . ' ' . ($product->description ?? '');
        
        foreach ($placeholderPatterns as $pattern) {
            if (preg_match($pattern, $contentToCheck)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if product is excluded from image generation by custom rules
     *
     * @param \Cake\Datasource\EntityInterface $product Product entity
     * @return bool True if product should be excluded
     */
    public function productExcludedFromGeneration(EntityInterface $product): bool
    {
        // Check if product is explicitly marked to skip image generation
        if (property_exists($product, 'skip_image_generation') && $product->skip_image_generation === true) {
            return true;
        }

        // Check if product is in draft/unpublished state
        if (property_exists($product, 'status')) {
            $excludedStatuses = ['draft', 'unpublished', 'archived', 'deleted'];
            if (in_array(strtolower($product->status), $excludedStatuses)) {
                return true;
            }
        }

        if (property_exists($product, 'published') && $product->published === false) {
            return true;
        }

        // Check if product is marked as inactive
        if (property_exists($product, 'active') && $product->active === false) {
            return true;
        }

        // Check for products that are marked as templates or examples
        if (!empty($product->title)) {
            $excludePatterns = [
                '/^template/i',
                '/^example/i',
                '/^test\s/i',
                '/^sample/i'
            ];
            
            foreach ($excludePatterns as $pattern) {
                if (preg_match($pattern, $product->title)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get a query for products that need images
     *
     * @param \Cake\ORM\Query|null $query Existing query to modify, or null to create new
     * @return \Cake\ORM\Query Query for products needing images
     */
    public function findProductsNeedingImages(Query $query = null): Query
    {
        if ($query === null) {
            $query = $this->find();
        }

        // Only get products without images
        $query = $query
            ->select(['id', 'title', 'description', 'manufacturer', 'category', 'type', 'status', 'published', 'active'])
            ->where([
                'OR' => [
                    ['image IS' => null],
                    ['image' => '']
                ]
            ]);

        // Exclude products that shouldn't have images based on category
        $excludedCategories = Configure::read('ImageGeneration.excludedCategories', [
            'service',
            'software-license',
            'digital-download',
            'consultation'
        ]);
        
        if (!empty($excludedCategories)) {
            $query = $query->where(['category NOT IN' => $excludedCategories]);
        }

        // Exclude products based on type
        $excludedTypes = Configure::read('ImageGeneration.excludedTypes', [
            'virtual',
            'digital'
        ]);
        
        if (!empty($excludedTypes)) {
            $query = $query->where(['type NOT IN' => $excludedTypes]);
        }

        // Only include published/active products
        if ($this->getSchema()->hasColumn('published')) {
            $query = $query->where(['published' => true]);
        }
        
        if ($this->getSchema()->hasColumn('active')) {
            $query = $query->where(['active' => true]);
        }
        
        if ($this->getSchema()->hasColumn('status')) {
            $query = $query->where(['status NOT IN' => ['draft', 'unpublished', 'archived', 'deleted']]);
        }

        // Exclude products marked to skip generation
        if ($this->getSchema()->hasColumn('skip_image_generation')) {
            $query = $query->where(['skip_image_generation IS NOT' => true]);
        }

        // Require minimum content
        $query = $query
            ->where(['title IS NOT' => null])
            ->where(['LENGTH(title) >=' => 3])
            ->where([
                'OR' => [
                    ['LENGTH(COALESCE(description, "")) >=' => 10],
                    ['LENGTH(COALESCE(manufacturer, "")) >=' => 2]
                ]
            ]);

        return $query;
    }

    /**
     * Count products that need images
     *
     * @return int Number of products needing images
     */
    public function countProductsNeedingImages(): int
    {
        return $this->findProductsNeedingImages()->count();
    }

    /**
     * Check if a specific product ID needs an image without loading the full entity
     *
     * @param int|string $productId Product ID
     * @return bool True if product needs image
     */
    public function productIdNeedsImage($productId): bool
    {
        if (!$this->isImageGenerationEnabled()) {
            return false;
        }

        // Get minimal data needed for the check
        try {
            $product = $this->get($productId, [
                'fields' => [
                    'id', 'title', 'description', 'manufacturer', 'category', 
                    'type', 'status', 'published', 'active', 'image'
                ]
            ]);
        } catch (\Exception $e) {
            return false; // Product not found
        }

        return $this->productNeedsImage($product);
    }

    /**
     * Get products that need images with pagination support
     *
     * @param int $limit Maximum number of products to return
     * @param int $offset Number of products to skip
     * @return \Cake\ORM\Query Query for products needing images
     */
    public function getProductsNeedingImagesBatch(int $limit = 50, int $offset = 0): Query
    {
        return $this->findProductsNeedingImages()
            ->limit($limit)
            ->offset($offset)
            ->order(['id' => 'ASC']); // Consistent ordering for batching
    }
}