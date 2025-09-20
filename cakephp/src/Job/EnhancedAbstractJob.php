<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Api\Google\GoogleApiService;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Cake\Queue\QueueManager;
use Exception;
use Interop\Queue\Processor;

/**
 * Enhanced AbstractJob Class
 *
 * Enhanced base class for all queue jobs providing common functionality for:
 * - API service management (Anthropic, Google)
 * - SEO field processing patterns
 * - Translation management
 * - Requeue logic with backoff
 * - Bulk field updates
 * 
 * Eliminates ~200 lines of duplicated code across job classes.
 */
abstract class EnhancedAbstractJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts to process the job
     */
    public static int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time
     */
    public static bool $shouldBeUnique = true;

    /**
     * Cached API service instances
     */
    private ?AnthropicApiService $anthropicService = null;
    private ?GoogleApiService $googleService = null;

    // ==========================================
    // API SERVICE MANAGEMENT PATTERN
    // ==========================================

    /**
     * Get or create Anthropic API service instance
     *
     * @param \App\Service\Api\Anthropic\AnthropicApiService|null $service Optional service for dependency injection
     * @return \App\Service\Api\Anthropic\AnthropicApiService
     */
    protected function getAnthropicService(?AnthropicApiService $service = null): AnthropicApiService
    {
        if ($service !== null) {
            $this->anthropicService = $service;
        }

        if ($this->anthropicService === null) {
            $this->anthropicService = new AnthropicApiService();
        }

        return $this->anthropicService;
    }

    /**
     * Get or create Google API service instance
     *
     * @param \App\Service\Api\Google\GoogleApiService|null $service Optional service for dependency injection
     * @return \App\Service\Api\Google\GoogleApiService
     */
    protected function getGoogleService(?GoogleApiService $service = null): GoogleApiService
    {
        if ($service !== null) {
            $this->googleService = $service;
        }

        if ($this->googleService === null) {
            $this->googleService = new GoogleApiService();
        }

        return $this->googleService;
    }

    // ==========================================
    // SEO FIELD PROCESSING PATTERN
    // ==========================================

    /**
     * Update entity SEO fields using AI service with empty field checking
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to update
     * @param \Cake\ORM\Table $table The table instance
     * @param string $title The title for AI processing
     * @param string $content The content for AI processing
     * @param string $serviceMethod The method name to call on Anthropic service
     * @return bool Success status
     */
    protected function updateSeoFields(
        EntityInterface $entity,
        Table $table,
        string $title,
        string $content,
        string $serviceMethod = 'generateArticleSeo'
    ): bool {
        // Check if entity has empty SEO fields method
        if (method_exists($table, 'emptySeoFields')) {
            $emptyFields = $table->emptySeoFields($entity);
            
            if (empty($emptyFields)) {
                // No empty SEO fields to update
                return true;
            }
        }

        // Generate SEO content using Anthropic service
        $anthropic = $this->getAnthropicService();
        $seoResult = $anthropic->{$serviceMethod}($title, strip_tags($content));

        if ($seoResult) {
            // Update only empty fields if method exists, otherwise update all
            if (isset($emptyFields)) {
                foreach ($emptyFields as $field) {
                    if (isset($seoResult[$field])) {
                        $entity->{$field} = $seoResult[$field];
                    }
                }
            } else {
                // Fallback: update standard SEO fields
                $standardSeoFields = [
                    'meta_title', 'meta_description', 'meta_keywords',
                    'facebook_description', 'linkedin_description', 
                    'instagram_description', 'twitter_description'
                ];
                
                foreach ($standardSeoFields as $field) {
                    if (isset($seoResult[$field]) && empty($entity->{$field})) {
                        $entity->{$field} = $seoResult[$field];
                    }
                }
            }

            return (bool)$table->save($entity, ['noMessage' => true]);
        }

        return false;
    }

    // ==========================================
    // TRANSLATION FIELD MANAGEMENT PATTERN  
    // ==========================================

    /**
     * Check if translations are enabled in system settings
     *
     * @return bool True if any translation languages are enabled
     */
    protected function areTranslationsEnabled(): bool
    {
        $translationSettings = SettingsManager::read('Translations', []);
        return !empty(array_filter($translationSettings));
    }

    /**
     * Process entity translations using Google API
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to translate
     * @param \Cake\ORM\Table $table The table instance
     * @param array $fieldMapping Map of entity fields to translation field names
     * @return bool Success status
     */
    protected function processTranslations(
        EntityInterface $entity, 
        Table $table, 
        array $fieldMapping
    ): bool {
        if (!$this->areTranslationsEnabled()) {
            return false;
        }

        // Prepare field data for translation
        $translationData = [];
        foreach ($fieldMapping as $entityField => $translationKey) {
            $translationData[$translationKey] = (string)$entity->{$entityField};
        }

        // Call appropriate translation method based on entity type
        $googleService = $this->getGoogleService();
        $entitySource = $entity->getSource();
        
        $translationMethod = match ($entitySource) {
            'Articles' => 'translateArticle',
            'Tags' => 'translateTag',
            'ImageGalleries' => 'translateImageGallery',
            default => 'translateGenericEntity'
        };

        // Execute translation
        if (method_exists($googleService, $translationMethod)) {
            $result = call_user_func_array([$googleService, $translationMethod], array_values($translationData));
        } else {
            // Fallback to generic translation if specific method doesn't exist
            $result = $googleService->translateContent($translationData);
        }

        if ($result) {
            foreach ($result as $locale => $translation) {
                foreach ($fieldMapping as $entityField => $translationKey) {
                    if (isset($translation[$translationKey])) {
                        $entity->translation($locale)->{$entityField} = $translation[$translationKey];
                    }
                }
            }

            return (bool)$table->save($entity, ['noMessage' => true]);
        }

        return false;
    }

    // ==========================================
    // REQUEUE WITH BACKOFF PATTERN
    // ==========================================

    /**
     * Handle job requeuing with exponential backoff
     *
     * @param \Cake\Queue\Job\Message $message Original message
     * @param string $reason Reason for requeue
     * @param int $maxAttempts Maximum attempts before giving up
     * @param int $baseDelay Base delay in seconds
     * @return string Processor result
     */
    protected function requeueWithBackoff(
        Message $message,
        string $reason,
        int $maxAttempts = 5,
        int $baseDelay = 10
    ): string {
        $attempt = (int)$message->getArgument('_attempt', 0);
        $id = $message->getArgument('id', 'unknown');
        $title = $message->getArgument('title', '');

        if ($attempt >= $maxAttempts) {
            $this->logJobError($id, sprintf('%s after %d attempts', $reason, $attempt), $title);
            return Processor::REJECT;
        }

        // Calculate exponential backoff delay
        $delay = $baseDelay * ($attempt + 1);

        // Prepare requeue data
        $data = array_merge($message->getArguments(), [
            '_attempt' => $attempt + 1,
        ]);

        // Requeue the job
        QueueManager::push(
            static::class,
            $data,
            [
                'config' => 'default',
                'delay' => $delay,
            ]
        );

        $this->log(
            sprintf(
                'Job requeued (%s) with %d second delay: %s : %s',
                $reason,
                $delay,
                $id,
                $title
            ),
            'info',
            ['group_name' => static::class]
        );

        return Processor::ACK;
    }

    // ==========================================
    // BULK ENTITY OPERATIONS PATTERN
    // ==========================================

    /**
     * Process multiple fields on an entity with a single API call result
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to update
     * @param array $apiResult The API result containing field data
     * @param array $fieldMap Mapping of API result keys to entity properties
     * @return \Cake\Datasource\EntityInterface The updated entity
     */
    protected function applyBulkFieldUpdates(
        EntityInterface $entity,
        array $apiResult,
        array $fieldMap
    ): EntityInterface {
        foreach ($fieldMap as $apiKey => $entityField) {
            if (isset($apiResult[$apiKey])) {
                $entity->{$entityField} = $apiResult[$apiKey];
            }
        }

        return $entity;
    }

    /**
     * Find or create entity by field value (commonly used for tags, categories)
     *
     * @param \Cake\ORM\Table $table The table to search/create in
     * @param array $searchFields Fields to search by (e.g., ['title' => $value])
     * @param array $createData Data to use when creating new entity
     * @return \Cake\ORM\Entity The found or created entity
     */
    protected function findOrCreateEntity(
        Table $table,
        array $searchFields,
        array $createData
    ): Entity {
        $entity = $table->find()->where($searchFields)->first();
        
        if (!$entity) {
            $entity = $table->newEmptyEntity();
            $entity = $table->patchEntity($entity, $createData);
            $table->save($entity);
        }

        return $entity;
    }

    // ==========================================
    // CONFIGURATION AND SETTINGS PATTERN
    // ==========================================

    /**
     * Get configuration setting with validation and defaults
     *
     * @param string $key Configuration key (e.g., 'AI.anthropic.enabled')
     * @param mixed $default Default value if setting not found
     * @param array $validValues Optional array of valid values for validation
     * @return mixed The configuration value
     */
    protected function getValidatedConfig(string $key, mixed $default = null, array $validValues = []): mixed
    {
        $value = SettingsManager::read($key, $default);
        
        if (!empty($validValues) && !in_array($value, $validValues, true)) {
            $this->log(
                sprintf('Invalid configuration value for %s: %s. Using default: %s', 
                    $key, 
                    json_encode($value), 
                    json_encode($default)
                ),
                'warning',
                ['group_name' => static::class]
            );
            return $default;
        }

        return $value;
    }

    // ==========================================
    // ORIGINAL ABSTRACT JOB METHODS (ENHANCED)
    // ==========================================

    /**
     * Log the start of a job execution
     */
    protected function logJobStart(string $id, string $title = ''): void
    {
        $this->log(
            sprintf('Received %s message: %s%s', static::getJobType(), $id, $title ? " : {$title}" : ''),
            'info',
            ['group_name' => static::class]
        );
    }

    /**
     * Log successful job completion
     */
    protected function logJobSuccess(string $id, string $title = ''): void
    {
        $this->log(
            sprintf('%s completed successfully. ID: %s%s', static::getJobType(), $id, $title ? " ({$title})" : ''),
            'info',
            ['group_name' => static::class]
        );
    }

    /**
     * Log job execution error
     */
    protected function logJobError(string $id, string $error, string $title = ''): void
    {
        $this->log(
            sprintf('%s failed. ID: %s%s Error: %s', static::getJobType(), $id, $title ? " ({$title})" : '', $error),
            'error',
            ['group_name' => static::class]
        );
    }

    /**
     * Execute job operation with standardized error handling and logging
     */
    protected function executeWithErrorHandling(string $id, callable $operation, string $title = ''): ?string
    {
        $this->logJobStart($id, $title);

        try {
            $result = $operation();

            if ($result) {
                $this->logJobSuccess($id, $title);
                $this->clearContentCache();
                return Processor::ACK;
            } else {
                $this->logJobError($id, 'Operation returned false or null', $title);
                return Processor::REJECT;
            }
        } catch (Exception $e) {
            $this->logJobError($id, $e->getMessage(), $title);
            return Processor::REJECT;
        }
    }

    /**
     * Execute job operation with entity save handling
     */
    protected function executeWithEntitySave(string $id, callable $operation, string $title = ''): ?string
    {
        return $this->executeWithErrorHandling($id, function () use ($operation) {
            $result = $operation();

            if ($result instanceof EntityInterface) {
                $table = $this->getTable($result->getSource());
                return $table->save($result);
            }

            return $result;
        }, $title);
    }

    /**
     * Clear content cache after successful operations
     */
    protected function clearContentCache(): void
    {
        Cache::clear('content');
    }

    /**
     * Get table instance using TableRegistry
     */
    protected function getTable(string $tableName): Table
    {
        return TableRegistry::getTableLocator()->get($tableName);
    }

    /**
     * Validate required message arguments
     */
    protected function validateArguments(Message $message, array $required): bool
    {
        foreach ($required as $arg) {
            if (!$message->getArgument($arg)) {
                $this->log(
                    sprintf('Missing required argument: %s for %s', $arg, static::getJobType()),
                    'error',
                    ['group_name' => static::class]
                );
                return false;
            }
        }

        return true;
    }

    /**
     * Get the human-readable job type name for logging
     */
    abstract protected static function getJobType(): string;

    /**
     * Execute the job with the given message
     */
    abstract public function execute(Message $message): ?string;
}