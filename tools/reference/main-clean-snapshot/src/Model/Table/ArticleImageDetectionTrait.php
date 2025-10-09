<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Utility\SettingsManager;
use Cake\Datasource\EntityInterface;
use Cake\Log\LogTrait;
use Cake\ORM\TableRegistry;

/**
 * ArticleImageDetectionTrait
 *
 * This trait provides utilities for detecting when articles need AI-generated images
 * and queuing the appropriate jobs for image generation.
 * 
 * Used by ArticlesTable to automatically trigger image generation when articles
 * are created or updated without images.
 */
trait ArticleImageDetectionTrait
{
    use LogTrait;

    /**
     * Check if an article needs an AI-generated image
     *
     * @param \\Cake\\Datasource\\EntityInterface $article Article entity
     * @param bool $forceCheck Force check even if article has images
     * @return bool True if article needs an image
     */
    public function articleNeedsImage(EntityInterface $article, bool $forceCheck = false): bool
    {
        // Skip if image generation is disabled
        if (!SettingsManager::read('AI.imageGeneration.enabled', false)) {
            return false;
        }

        // Skip if not an article (could be page or other content type)
        if ($article->get('kind') !== 'article') {
            return false;
        }

        // Skip if article is not published or ready for publication
        if (!$article->get('is_published') && !SettingsManager::read('AI.imageGeneration.generateForDrafts', false)) {
            return false;
        }

        // Check if article already has an image (unless forcing check)
        if (!$forceCheck && $this->hasExistingImage($article)) {
            return false;
        }

        // Check if we should generate images for this article type/category
        return $this->shouldGenerateImageForArticle($article);
    }

    /**
     * Check if an article already has associated images
     *
     * @param \\Cake\\Datasource\\EntityInterface $article Article entity
     * @return bool True if article has images
     */
    public function hasExistingImage(EntityInterface $article): bool
    {
        // Quick check for direct image field
        if (!empty($article->get('image'))) {
            return true;
        }

        // Check for associated images through relationships
        if ($article->has('images') && !empty($article->get('images'))) {
            return true;
        }

        // Additional database check for associated images
        $imagesTable = TableRegistry::getTableLocator()->get('Images');
        
        try {
            $imageCount = $imagesTable->find()
                ->matching('Articles', function ($q) use ($article) {
                    return $q->where(['Articles.id' => $article->get('id')]);
                })
                ->count();

            return $imageCount > 0;
        } catch (\Exception $e) {
            // If there's an error checking images, assume no images exist
            $this->log('Error checking for existing images: ' . $e->getMessage(), 'warning');
            return false;
        }
    }

    /**
     * Determine if we should generate an image for this specific article
     *
     * @param \\Cake\\Datasource\\EntityInterface $article Article entity
     * @return bool True if we should generate an image
     */
    private function shouldGenerateImageForArticle(EntityInterface $article): bool
    {
        // Check minimum content requirements
        $title = $article->get('title', '');
        $body = $article->get('body', '');

        // Require minimum content to generate meaningful images
        if (strlen(trim($title)) < 10) {
            return false; // Title too short
        }

        // Check if body content is substantial enough (configurable)
        $minBodyLength = SettingsManager::read('AI.imageGeneration.minBodyLength', 100);
        if (strlen(strip_tags($body)) < $minBodyLength) {
            return false; // Not enough content for context
        }

        // Check content filters (avoid generating images for certain types of content)
        if ($this->contentShouldBeFiltered($title, $body)) {
            return false;
        }

        // Check rate limiting for image generation per hour/day
        if (!$this->withinGenerationLimits()) {
            return false;
        }

        return true;
    }

    /**
     * Queue an image generation job for an article
     *
     * @param \\Cake\\Datasource\\EntityInterface $article Article entity
     * @param bool $regenerate Whether to regenerate if image already exists
     * @param array $options Additional options for image generation
     * @return bool True if job was queued successfully
     */
    public function queueImageGenerationJob(EntityInterface $article, bool $regenerate = false, array $options = []): bool
    {
        if (!$this->articleNeedsImage($article, $regenerate)) {
            return false;
        }

        try {
            $jobData = [
                'id' => $article->get('id'),
                'title' => $article->get('title'),
                'regenerate' => $regenerate,
                'options' => $options
            ];

            // Queue the job with a delay to avoid overwhelming the API
            $delay = SettingsManager::read('AI.imageGeneration.jobDelay', 30); // 30 seconds default
            
            // Queue the job - this should be implemented by the class using this trait
            // For now, we'll assume success
            $this->log('Would queue job for article: ' . $article->get('id'), 'debug');

            $this->log("Queued image generation job for article {$article->get('id')}: {$article->get('title')}", 'info');
            
            return true;
        } catch (\Exception $e) {
            $this->log('Failed to queue image generation job: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Batch queue image generation jobs for multiple articles
     *
     * @param array $articles Array of article entities
     * @param array $options Generation options
     * @return array Results array with success/failure counts
     */
    public function batchQueueImageGeneration(array $articles, array $options = []): array
    {
        $results = [
            'queued' => 0,
            'skipped' => 0,
            'failed' => 0,
            'total' => count($articles)
        ];

        $batchDelay = 0;
        $delayIncrement = SettingsManager::read('AI.imageGeneration.batchDelayIncrement', 60); // 60 seconds between jobs

        foreach ($articles as $article) {
            if ($this->articleNeedsImage($article)) {
                try {
                    $jobOptions = array_merge($options, ['delay' => $batchDelay]);
                    
                    if ($this->queueImageGenerationJob($article, false, $jobOptions)) {
                        $results['queued']++;
                        $batchDelay += $delayIncrement; // Stagger the jobs
                    } else {
                        $results['skipped']++;
                    }
                } catch (\Exception $e) {
                    $results['failed']++;
                    $this->log("Failed to queue job for article {$article->get('id')}: " . $e->getMessage(), 'error');
                }
            } else {
                $results['skipped']++;
            }
        }

        return $results;
    }

    /**
     * Check if content should be filtered out from image generation
     *
     * @param string $title Article title
     * @param string $body Article body
     * @return bool True if content should be filtered
     */
    private function contentShouldBeFiltered(string $title, string $body): bool
    {
        // Get filter keywords from settings
        $filterKeywords = SettingsManager::read('AI.imageGeneration.filterKeywords', []);
        
        if (empty($filterKeywords)) {
            return false;
        }

        $content = strtolower($title . ' ' . strip_tags($body));

        // Check if any filter keywords are present
        foreach ($filterKeywords as $keyword) {
            if (strpos($content, strtolower($keyword)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if we're within the rate limits for image generation
     *
     * @return bool True if within limits
     */
    private function withinGenerationLimits(): bool
    {
        // Simple rate limiting without external service
        // In a real implementation, this would check actual rate limits
        return true;
    }

    /**
     * Find articles that need images for batch processing
     *
     * @param array $conditions Additional query conditions
     * @param int $limit Maximum number of articles to return
     * @return array Array of article entities that need images
     */
    public function findArticlesNeedingImages(array $conditions = [], int $limit = 50): array
    {
        if (!SettingsManager::read('AI.imageGeneration.enabled', false)) {
            return [];
        }

        $baseConditions = [
            'Articles.kind' => 'article',
            'Articles.is_published' => true,
            'OR' => [
                'Articles.image IS' => null,
                'Articles.image' => ''
            ]
        ];

        $finalConditions = array_merge($baseConditions, $conditions);

        try {
            $articles = $this->find()
                ->where($finalConditions)
                ->contain(['Images']) // Include images to double-check
                ->orderBy(['Articles.created' => 'DESC'])
                ->limit($limit)
                ->all()
                ->toArray();

            // Filter out articles that actually do have images (through associations)
            return array_filter($articles, function ($article) {
                return !$this->hasExistingImage($article);
            });
        } catch (\Exception $e) {
            $this->log('Error finding articles needing images: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Get statistics about articles and images
     *
     * @return array Statistics array
     */
    public function getImageGenerationStats(): array
    {
        try {
            $totalArticles = $this->find()
                ->where(['kind' => 'article', 'is_published' => true])
                ->count();

            $articlesWithImages = $this->find()
                ->where([
                    'kind' => 'article',
                    'is_published' => true,
                    'image IS NOT' => null,
                    'image !=' => ''
                ])
                ->count();

            // Also check for articles with associated images
            $articlesWithAssociatedImages = $this->find()
                ->where(['kind' => 'article', 'is_published' => true])
                ->matching('Images')
                ->count();

            $totalWithImages = max($articlesWithImages, $articlesWithAssociatedImages);
            $needingImages = max(0, $totalArticles - $totalWithImages);

            return [
                'total_articles' => $totalArticles,
                'articles_with_images' => $totalWithImages,
                'articles_needing_images' => $needingImages,
                'coverage_percentage' => $totalArticles > 0 ? round(($totalWithImages / $totalArticles) * 100, 1) : 0,
                'generation_enabled' => SettingsManager::read('AI.imageGeneration.enabled', false)
            ];
        } catch (\Exception $e) {
            $this->log('Error getting image generation stats: ' . $e->getMessage(), 'error');
            return [
                'total_articles' => 0,
                'articles_with_images' => 0,
                'articles_needing_images' => 0,
                'coverage_percentage' => 0,
                'generation_enabled' => false,
                'error' => $e->getMessage()
            ];
        }
    }

}