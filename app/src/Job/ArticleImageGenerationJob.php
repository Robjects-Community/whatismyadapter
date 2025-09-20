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
 * ArticleImageGenerationJob
 *
 * This job generates AI-powered images for articles that don't have images.
 * It's designed to run as a background job to avoid slowing down the article
 * creation/editing process.
 * 
 * The job will:
 * 1. Check if the article still needs an image
 * 2. Generate an appropriate image using AI services
 * 3. Download and save the image locally
 * 4. Associate the image with the article
 * 5. Update article metadata with image information
 */
class ArticleImageGenerationJob extends AbstractJob
{
    /**
     * @var \\App\\Service\\Api\\ImageGenerationService Image generation service
     */
    private ImageGenerationService $imageGenerationService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \\App\\Service\\Api\\ImageGenerationService|null $imageGenerationService
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
        return 'article image generation';
    }

    /**
     * Execute the job to generate an image for an article
     *
     * @param \\Cake\\Queue\\Job\\Message $message Queue message with article data
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    public function execute(Message $message): ?string
    {
        $articleId = $message->getArgument('id');
        $title = $message->getArgument('title');
        $regenerate = $message->getArgument('regenerate', false);

        if (!$this->validateArguments($message, ['id', 'title'])) {
            return Processor::REJECT;
        }

        return $this->executeWithErrorHandling($articleId, function () use ($articleId, $regenerate) {
            $articlesTable = $this->getTable('Articles');
            
            try {
                // Get the article with images to check current state
                $article = $articlesTable->get($articleId, [
                    'contain' => ['Images']
                ]);
            } catch (Exception $e) {
                $this->log("Article {$articleId} not found: " . $e->getMessage(), 'error');
                return false;
            }

            // Check if article still needs an image (unless forced regeneration)
            if (!$regenerate && $this->articleHasImage($article)) {
                $this->log("Article {$articleId} already has images, skipping generation", 'info');
                return true; // Consider this successful - no work needed
            }

            // Generate image based on article content
            $imageResult = $this->imageGenerationService->generateArticleImage(
                $article->title,
                $article->body ?? '',
                [
                    'context' => 'article header image',
                    'style' => 'vivid',
                    'orientation' => 'landscape'
                ]
            );

            if (!$imageResult || !$imageResult['success']) {
                $this->log("Failed to generate image for article {$articleId}", 'warning');
                return false;
            }

            // Download and save the generated image
            $savedImage = $this->downloadAndSaveImage($article, $imageResult);
            
            if (!$savedImage) {
                $this->log("Failed to save generated image for article {$articleId}", 'error');
                return false;
            }

            // Associate the image with the article
            $this->associateImageWithArticle($article, $savedImage, $imageResult);

            $this->log("Successfully generated and saved image for article {$articleId}: {$savedImage['filename']}", 'info');
            
            return true;
        }, $title);
    }

    /**
     * Check if an article already has associated images
     *
     * @param \\Cake\\Datasource\\EntityInterface $article Article entity
     * @return bool True if article has images
     */
    private function articleHasImage($article): bool
    {
        // Check for direct image field
        if (!empty($article->image)) {
            return true;
        }

        // Check for associated images through ImageAssociable behavior
        if (!empty($article->images) && count($article->images) > 0) {
            return true;
        }

        // Additional check: query the images association directly
        $imagesTable = $this->getTable('Images');
        $imageCount = $imagesTable->find()
            ->matching('Articles', function ($q) use ($article) {
                return $q->where(['Articles.id' => $article->id]);
            })
            ->count();

        return $imageCount > 0;
    }

    /**
     * Download the generated image and save it locally
     *
     * @param \\Cake\\Datasource\\EntityInterface $article Article entity
     * @param array $imageResult Result from image generation service
     * @return array|null Saved image data or null on failure
     */
    private function downloadAndSaveImage($article, array $imageResult): ?array
    {
        try {
            // Generate unique filename
            $extension = $this->getImageExtensionFromUrl($imageResult['url']);
            $filename = 'article_' . $article->id . '_' . Text::uuid() . '.' . $extension;
            
            // Determine save path (following CakePHP conventions)
            $uploadDir = WWW_ROOT . 'files' . DS . 'Articles' . DS . 'image' . DS;
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
                'relative_path' => 'files/Articles/image/' . $filename,
                'size' => filesize($fullPath),
                'mime_type' => mime_content_type($fullPath)
            ];
        } catch (Exception $e) {
            $this->log('Error downloading image: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Associate the generated image with the article
     *
     * @param \\Cake\\Datasource\\EntityInterface $article Article entity  
     * @param array $savedImage Saved image data
     * @param array $imageResult Generation result data
     * @return bool Success status
     */
    private function associateImageWithArticle($article, array $savedImage, array $imageResult): bool
    {
        try {
            $articlesTable = $this->getTable('Articles');
            $imagesTable = $this->getTable('Images');

            // Create image record
            $imageEntity = $imagesTable->newEmptyEntity();
            $imageEntity = $imagesTable->patchEntity($imageEntity, [
                'filename' => $savedImage['filename'],
                'name' => $this->generateImageName($article->title, $imageResult),
                'alt_text' => $imageResult['alt_text'] ?? $article->title,
                'size' => $savedImage['size'],
                'mime_type' => $savedImage['mime_type'],
                'metadata' => json_encode([
                    'generated_by' => 'ai',
                    'provider' => $imageResult['provider'],
                    'generation_prompt' => $imageResult['revised_prompt'] ?? '',
                    'attribution' => $imageResult['attribution'] ?? null,
                    'original_metadata' => $imageResult['metadata'] ?? []
                ])
            ]);

            if (!$imagesTable->save($imageEntity)) {
                $this->log('Failed to save image entity: ' . json_encode($imageEntity->getErrors()), 'error');
                return false;
            }

            // Associate image with article using ImageAssociable behavior
            $articlesTable->Images->link($article, [$imageEntity]);

            // Update article's main image field if it's empty
            if (empty($article->image)) {
                $article->image = $savedImage['filename'];
                $articlesTable->save($article, ['noMessage' => true]); // Prevent triggering more jobs
            }

            return true;
        } catch (Exception $e) {
            $this->log('Error associating image with article: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Generate a descriptive name for the image
     *
     * @param string $articleTitle Article title
     * @param array $imageResult Generation result
     * @return string Generated image name
     */
    private function generateImageName(string $articleTitle, array $imageResult): string
    {
        $baseName = 'Image for: ' . $articleTitle;
        
        // Add provider info if available
        if (isset($imageResult['provider'])) {
            $provider = ucfirst($imageResult['provider']);
            $baseName = "{$provider} generated image for: {$articleTitle}";
        }

        // Truncate if too long
        return strlen($baseName) > 255 ? substr($baseName, 0, 252) . '...' : $baseName;
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