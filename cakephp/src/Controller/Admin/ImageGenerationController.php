<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Table\ArticlesTable;
use App\Utility\SettingsManager;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;

/**
 * Image Generation Controller
 *
 * This controller handles the admin interface for AI image generation features,
 * including statistics viewing, batch processing controls, and monitoring.
 */
class ImageGenerationController extends AppController
{
    /**
     * @var \App\Model\Table\ArticlesTable
     */
    private ArticlesTable $ArticlesTable;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->ArticlesTable = TableRegistry::getTableLocator()->get('Articles');
    }

    /**
     * Index method - Dashboard for image generation
     *
     * Displays statistics, recent activities, and management options
     * for the AI image generation feature.
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
    {
        // Check if feature is enabled
        $featureEnabled = SettingsManager::read('AI.enabled') && SettingsManager::read('AI.imageGeneration.enabled');
        
        if (!$featureEnabled) {
            $this->Flash->warning(__('AI image generation is not enabled. Please check your settings.'));
        }

        // Get statistics
        $statistics = $this->ArticlesTable->getImageGenerationStatistics();
        
        // Get recent articles that need images
        $articlesNeedingImages = $this->getArticlesNeedingImages(10);
        
        // Get rate limit status
        $rateLimitStatus = $this->getRateLimitStatus();
        
        // Get configuration status
        $configStatus = $this->getConfigurationStatus();

        $this->set(compact(
            'featureEnabled',
            'statistics',
            'articlesNeedingImages',
            'rateLimitStatus',
            'configStatus'
        ));

        return null;
    }

    /**
     * Statistics method
     *
     * Displays detailed statistics and analytics for image generation
     *
     * @return \Cake\Http\Response|null
     */
    public function statistics(): ?Response
    {
        $statistics = $this->ArticlesTable->getImageGenerationStatistics();
        
        // Get more detailed statistics
        $monthlyStats = $this->getMonthlyStatistics();
        $providerStats = $this->getProviderStatistics();
        
        $this->set(compact('statistics', 'monthlyStats', 'providerStats'));

        return null;
    }

    /**
     * Batch processing interface
     *
     * Provides interface for batch processing articles that need images
     *
     * @return \Cake\Http\Response|null
     */
    public function batch(): ?Response
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            return $this->processBatch();
        }

        // Get articles that need images for preview
        $limit = (int)($this->request->getQuery('limit') ?? 50);
        $candidates = $this->getArticlesNeedingImages($limit);
        
        // Check rate limits
        $rateLimitExceeded = $this->ArticlesTable->isRateLimitExceeded();

        $this->set(compact('candidates', 'rateLimitExceeded', 'limit'));

        return null;
    }

    /**
     * Process batch image generation
     *
     * @return \Cake\Http\Response
     */
    private function processBatch(): Response
    {
        $data = $this->request->getData();
        $limit = (int)($data['limit'] ?? 50);
        $force = (bool)($data['force'] ?? false);

        // Check rate limits unless forced
        if (!$force && $this->ArticlesTable->isRateLimitExceeded()) {
            $this->Flash->error(__('Rate limits exceeded. Enable "Force" to bypass or wait for limits to reset.'));
            return $this->redirect(['action' => 'batch']);
        }

        try {
            $results = $this->ArticlesTable->batchQueueImageGenerationForArticles($limit);

            if ($results['queued'] > 0) {
                $this->Flash->success(__(
                    'Successfully queued {0} articles for image generation. {1} articles were processed total.',
                    $results['queued'],
                    $results['processed']
                ));
            } else {
                $this->Flash->warning(__('No articles were queued. All articles may already have images or fail filters.'));
            }

            if ($results['skipped'] > 0) {
                $this->Flash->info(__('Skipped {0} articles due to rate limits or content filters.', $results['skipped']));
            }

        } catch (\Exception $e) {
            $this->Flash->error(__('An error occurred during batch processing: {0}', $e->getMessage()));
        }

        return $this->redirect(['action' => 'batch']);
    }

    /**
     * Configuration check method
     *
     * Validates and displays the current configuration status
     *
     * @return \Cake\Http\Response|null
     */
    public function config(): ?Response
    {
        $configStatus = $this->getConfigurationStatus();
        $this->set(compact('configStatus'));

        return null;
    }

    /**
     * Get articles that need images
     *
     * @param int $limit Maximum number of articles to return
     * @return array
     */
    private function getArticlesNeedingImages(int $limit = 50): array
    {
        $query = $this->ArticlesTable->find()
            ->select(['id', 'title', 'published', 'created', 'word_count'])
            ->where([
                'Articles.kind' => 'article',
                'Articles.is_published' => true,
            ])
            ->orderBy(['Articles.published' => 'DESC'])
            ->limit($limit * 2); // Get more to filter

        $articles = $query->toArray();
        $candidates = [];

        foreach ($articles as $article) {
            if ($this->ArticlesTable->articleNeedsImage($article)) {
                $candidates[] = $article;
                if (count($candidates) >= $limit) {
                    break;
                }
            }
        }

        return $candidates;
    }

    /**
     * Get rate limit status information
     *
     * @return array
     */
    private function getRateLimitStatus(): array
    {
        $cache = $this->ArticlesTable->getCache('image_generation');
        
        return [
            'exceeded' => $this->ArticlesTable->isRateLimitExceeded(),
            'minute_count' => $cache->read('image_gen_rate_limit_minute') ?? 0,
            'hour_count' => $cache->read('image_gen_rate_limit_hour') ?? 0,
            'day_count' => $cache->read('image_gen_rate_limit_day') ?? 0,
            'minute_limit' => (int)SettingsManager::read('AI.imageGeneration.rateLimits.perMinute') ?: 5,
            'hour_limit' => (int)SettingsManager::read('AI.imageGeneration.rateLimits.perHour') ?: 50,
            'day_limit' => (int)SettingsManager::read('AI.imageGeneration.rateLimits.perDay') ?: 200,
        ];
    }

    /**
     * Get configuration status
     *
     * @return array
     */
    private function getConfigurationStatus(): array
    {
        $status = [
            'ai_enabled' => SettingsManager::read('AI.enabled'),
            'image_generation_enabled' => SettingsManager::read('AI.imageGeneration.enabled'),
            'primary_provider' => SettingsManager::read('AI.imageGeneration.primaryProvider'),
            'fallback_provider' => SettingsManager::read('AI.imageGeneration.fallbackProvider'),
            'api_keys' => [],
            'model' => SettingsManager::read('AI.imageGeneration.model'),
            'size' => SettingsManager::read('AI.imageGeneration.size'),
            'quality' => SettingsManager::read('AI.imageGeneration.quality'),
        ];

        // Check API key availability (don't show actual keys for security)
        $apiKeys = ['openai', 'anthropic', 'unsplash'];
        foreach ($apiKeys as $provider) {
            $key = SettingsManager::read("AI.imageGeneration.apiKeys.{$provider}");
            $status['api_keys'][$provider] = !empty($key);
        }

        return $status;
    }

    /**
     * Get monthly statistics (mock implementation)
     *
     * @return array
     */
    private function getMonthlyStatistics(): array
    {
        // In a real implementation, this would query the database for monthly data
        return [
            'current_month' => date('F Y'),
            'images_generated' => 45,
            'success_rate' => 92.3,
            'most_common_provider' => 'OpenAI DALL-E',
        ];
    }

    /**
     * Get provider statistics (mock implementation)
     *
     * @return array
     */
    private function getProviderStatistics(): array
    {
        // In a real implementation, this would analyze provider usage from logs
        return [
            'openai' => [
                'count' => 150,
                'success_rate' => 94.2,
                'avg_time' => 12.3,
            ],
            'unsplash' => [
                'count' => 23,
                'success_rate' => 100.0,
                'avg_time' => 2.1,
            ],
            'anthropic' => [
                'count' => 8,
                'success_rate' => 87.5,
                'avg_time' => 15.4,
            ],
        ];
    }
}