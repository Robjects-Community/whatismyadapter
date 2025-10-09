<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\ImageGenerationService;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\Message;
use Cake\Utility\Text;
use Exception;
use Interop\Queue\Processor;

/**
 * ProductImageGenerationJob
 *
 * This job generates AI-powered images for products that don't have images.
 * It's designed to run as a background job to avoid slowing down the product
 * creation/editing process.
 * 
 * The job will:
 * 1. Check if the product still needs an image
 * 2. Generate an appropriate image using AI services based on product details
 * 3. Download and save the image locally
 * 4. Associate the image with the product
 * 5. Update product metadata with image information
 */
class ProductImageGenerationJob extends AbstractJob
{
    /**
     * @var \App\Service\Api\ImageGenerationService Image generation service
     */
    private ImageGenerationService $imageGenerationService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\ImageGenerationService|null $imageGenerationService
     */
    public function __construct(?ImageGenerationService $imageGenerationService = null)
    {
        $this->imageGenerationService = $imageGenerationService ?? new ImageGenerationService();
    }

    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    protected static function getJobType(): string
    {
        return 'product image generation';
    }

    /**
     * Execute the job to generate an image for a product
     *
     * @param \Cake\Queue\Job\Message $message Queue message with product data
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    public function execute(Message $message): ?string
    {
        $productId = $message->getArgument('id');
        $title = $message->getArgument('title');
        $regenerate = $message->getArgument('regenerate', false);

        if (!$this->validateArguments($message, ['id', 'title'])) {
            return Processor::REJECT;
        }

        return $this->executeWithErrorHandling($productId, function () use ($productId, $regenerate) {
            $productsTable = $this->getTable('Products');
            
            try {
                // Get the product with images to check current state
                $product = $productsTable->get($productId, [
                    'contain' => ['Images']
                ]);
            } catch (Exception $e) {
                $this->log("Product {$productId} not found: " . $e->getMessage(), 'error');
                return false;
            }

            // Check if product still needs an image (unless forced regeneration)
            if (!$regenerate && $this->productHasImage($product)) {
                $this->log("Product {$productId} already has images, skipping generation", 'info');
                return true; // Consider this successful - no work needed
            }

            // Generate image based on product content
            $imageResult = $this->imageGenerationService->generateProductImage(
                $product->title,
                $product->description ?? '',
                $product->manufacturer ?? '',
                [
                    'context' => 'product listing image',
                    'style' => 'commercial',
                    'orientation' => 'square'
                ]
            );

            if (!$imageResult || !$imageResult['success']) {
                $this->log("Failed to generate image for product {$productId}", 'warning');
                return false;
            }

            // Download and save the generated image
            $savedImage = $this->downloadAndSaveImage($product, $imageResult);
            
            if (!$savedImage) {
                $this->log("Failed to save generated image for product {$productId}", 'error');
                return false;
            }

            // Associate the image with the product
            $this->associateImageWithProduct($product, $savedImage, $imageResult);

            $this->log("Successfully generated and saved image for product {$productId}: {$savedImage['filename']}", 'info');
            
            return true;
        }, $title);
    }

    /**
     * Check if a product already has associated images
     *
     * @param \Cake\Datasource\EntityInterface $product Product entity
     * @return bool True if product has images
     */
    private function productHasImage($product): bool
    {
        // Check for direct image field
        if (!empty($product->image)) {
            return true;
        }

        // Check for associated images through ImageAssociable behavior
        if (!empty($product->images) && count($product->images) > 0) {
            return true;
        }

        // Additional check: query the images association directly
        $imagesTable = $this->getTable('Images');
        $imageCount = $imagesTable->find()
            ->matching('Products', function ($q) use ($product) {
                return $q->where(['Products.id' => $product->id]);
            })
            ->count();

        return $imageCount > 0;
    }

    /**
     * Download the generated image and save it locally
     *
     * @param \Cake\Datasource\EntityInterface $product Product entity
     * @param array $imageResult Result from image generation service
     * @return array|null Saved image data or null on failure
     */
    private function downloadAndSaveImage($product, array $imageResult): ?array
    {
        try {
            // Generate unique filename
            $extension = $this->getImageExtensionFromUrl($imageResult['url']);
            $filename = 'product_' . $product->id . '_' . Text::uuid() . '.' . $extension;
            
            // Determine save path (following CakePHP conventions)
            $uploadDir = WWW_ROOT . 'files' . DS . 'Products' . DS . 'image' . DS;
            $fullPath = $uploadDir . $filename;

            // Download the image
            if (!$this->imageGenerationService->downloadImage($imageResult['url'], $fullPath)) {
                return null;
            }

            // Verify the downloaded image
            if (!$this->verifyDownloadedImage($fullPath)) {
                @unlink($fullPath); // Clean up invalid file
                return null;
            }

            return [
                'filename' => $filename,
                'path' => $fullPath,
                'relative_path' => 'files/Products/image/' . $filename,
                'size' => filesize($fullPath),
                'mime_type' => mime_content_type($fullPath)
            ];
        } catch (Exception $e) {
            $this->log('Error downloading image: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Associate the generated image with the product
     *
     * @param \Cake\Datasource\EntityInterface $product Product entity  
     * @param array $savedImage Saved image data
     * @param array $imageResult Generation result data
     * @return bool Success status
     */
    private function associateImageWithProduct($product, array $savedImage, array $imageResult): bool
    {
        try {
            $productsTable = $this->getTable('Products');
            $imagesTable = $this->getTable('Images');

            // Create image record
            $imageEntity = $imagesTable->newEmptyEntity();
            $imageEntity = $imagesTable->patchEntity($imageEntity, [
                'filename' => $savedImage['filename'],
                'name' => $this->generateImageName($product->title, $product->manufacturer, $imageResult),
                'alt_text' => $imageResult['alt_text'] ?? $this->generateAltText($product),
                'size' => $savedImage['size'],
                'mime_type' => $savedImage['mime_type'],
                'metadata' => json_encode([
                    'generated_by' => 'ai',
                    'provider' => $imageResult['provider'],
                    'generation_prompt' => $imageResult['revised_prompt'] ?? '',
                    'attribution' => $imageResult['attribution'] ?? null,
                    'original_metadata' => $imageResult['metadata'] ?? [],
                    'product_context' => [
                        'manufacturer' => $product->manufacturer ?? '',
                        'model_number' => $product->model_number ?? ''
                    ]
                ])
            ]);

            if (!$imagesTable->save($imageEntity)) {
                $this->log('Failed to save image entity: ' . json_encode($imageEntity->getErrors()), 'error');
                return false;
            }

            // Associate image with product using ImageAssociable behavior
            if (method_exists($productsTable, 'Images')) {
                $productsTable->Images->link($product, [$imageEntity]);
            }

            // Update product's main image field if it's empty
            if (empty($product->image)) {
                $product->image = $savedImage['filename'];
                $productsTable->save($product, ['noMessage' => true]); // Prevent triggering more jobs
            }

            return true;
        } catch (Exception $e) {
            $this->log('Error associating image with product: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Generate a descriptive name for the image
     *
     * @param string $productTitle Product title
     * @param string|null $manufacturer Product manufacturer
     * @param array $imageResult Generation result
     * @return string Generated image name
     */
    private function generateImageName(string $productTitle, ?string $manufacturer, array $imageResult): string
    {
        $baseName = 'Image for: ' . $productTitle;
        
        // Add manufacturer info if available
        if (!empty($manufacturer)) {
            $baseName = "Image for: {$manufacturer} {$productTitle}";
        }
        
        // Add provider info if available
        if (isset($imageResult['provider'])) {
            $provider = ucfirst($imageResult['provider']);
            $baseName = "{$provider} generated " . strtolower($baseName);
        }

        // Truncate if too long
        return strlen($baseName) > 255 ? substr($baseName, 0, 252) . '...' : $baseName;
    }

    /**
     * Generate alt text for the product image
     *
     * @param \Cake\Datasource\EntityInterface $product Product entity
     * @return string Generated alt text
     */
    private function generateAltText($product): string
    {
        $altText = $product->title;
        
        if (!empty($product->manufacturer)) {
            $altText = $product->manufacturer . ' ' . $altText;
        }
        
        if (!empty($product->model_number)) {
            $altText .= ' (' . $product->model_number . ')';
        }
        
        return $altText;
    }

    /**
     * Extract file extension from image URL
     *
     * @param string $url Image URL
     * @return string File extension (defaults to 'jpg')
     */
    private function getImageExtensionFromUrl(string $url): string
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        // Default to jpg if no extension found or if it's not a common image format
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        return in_array(strtolower($extension), $validExtensions) ? strtolower($extension) : 'jpg';
    }

    /**
     * Verify that the downloaded file is a valid image
     *
     * @param string $filePath Path to downloaded file
     * @return bool True if file is a valid image
     */
    private function verifyDownloadedImage(string $filePath): bool
    {
        if (!file_exists($filePath) || filesize($filePath) < 100) {
            return false; // File doesn't exist or is too small
        }

        // Check if it's a valid image using getimagesize
        $imageInfo = @getimagesize($filePath);
        if ($imageInfo === false) {
            return false;
        }

        // Check if it has reasonable dimensions
        [$width, $height] = $imageInfo;
        if ($width < 100 || $height < 100) {
            return false; // Image too small
        }

        // Check MIME type
        $validMimeTypes = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/webp'
        ];

        return in_array($imageInfo['mime'], $validMimeTypes);
    }

    /**
     * Clean up temporary or failed downloads
     *
     * @param string $filePath Path to file to clean up
     * @return void
     */
    private function cleanupFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }
}