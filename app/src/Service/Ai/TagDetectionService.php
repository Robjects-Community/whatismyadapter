<?php
declare(strict_types=1);

namespace App\Service\Ai;

use App\Service\Api\AiMetricsService;
use App\Service\Api\Anthropic\AnthropicApiService;
use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Exception;

/**
 * AI Tag Detection and Validation Service
 *
 * This service uses AI to analyze text content and suggest relevant tags,
 * validate existing tags, and ensure tag consistency across the application.
 */
class TagDetectionService
{
    /**
     * @var \App\Service\Api\Anthropic\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * @var \App\Service\Api\AiMetricsService
     */
    private AiMetricsService $metricsService;

    /**
     * @var \App\Model\Table\TagsTable
     */
    private $tagsTable;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->metricsService = new AiMetricsService();
        $this->tagsTable = TableRegistry::getTableLocator()->get('Tags');
        
        // Initialize Anthropic service
        $apiKey = SettingsManager::read('AI.anthropic_api_key', '');
        if (!empty($apiKey)) {
            $client = new Client();
            $this->anthropicService = new AnthropicApiService(
                $client,
                $apiKey,
                'https://api.anthropic.com/v1/messages',
                '2023-06-01'
            );
        }
    }

    /**
     * Analyze content and suggest relevant tags
     *
     * @param string $title The title of the content
     * @param string $content The main content to analyze
     * @param array $existingTags Optional array of existing tags to consider
     * @param int $maxSuggestions Maximum number of tag suggestions to return
     * @return array Array of suggested tags with confidence scores
     */
    public function suggestTags(string $title, string $content, array $existingTags = [], int $maxSuggestions = 10): array
    {
        if (!isset($this->anthropicService)) {
            return ['error' => 'AI service not configured'];
        }

        try {
            $startTime = microtime(true);
            
            // Get existing tags from database for context
            $allTags = $this->tagsTable->find()
                ->select(['title', 'slug'])
                ->orderBy(['article_count' => 'DESC'])
                ->limit(50)
                ->toArray();
            
            $existingTagsList = array_map(function ($tag) {
                return $tag->title;
            }, $allTags);

            // Prepare the prompt for AI analysis
            $prompt = $this->buildTagSuggestionPrompt($title, $content, $existingTags, $existingTagsList, $maxSuggestions);
            
            // Call the AI service
            $response = $this->anthropicService->sendMessage($prompt);
            $result = $this->parseTagSuggestions($response);
            
            // Calculate execution time and record metrics
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->metricsService->recordMetrics(
                'tag_detection',
                $executionTime,
                true,
                null,
                $this->estimateTokenCount($prompt),
                null,
                'claude-3-sonnet'
            );
            
            return $result;
            
        } catch (Exception $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->metricsService->recordMetrics(
                'tag_detection',
                $executionTime,
                false,
                $e->getMessage()
            );
            
            return ['error' => 'Failed to analyze content: ' . $e->getMessage()];
        }
    }

    /**
     * Validate existing tags for consistency and relevance
     *
     * @param array $tags Array of tag titles to validate
     * @param string $content The content to validate against
     * @return array Validation results with recommendations
     */
    public function validateTags(array $tags, string $content): array
    {
        if (!isset($this->anthropicService)) {
            return ['error' => 'AI service not configured'];
        }

        try {
            $startTime = microtime(true);
            
            $prompt = $this->buildTagValidationPrompt($tags, $content);
            $response = $this->anthropicService->sendMessage($prompt);
            $result = $this->parseTagValidation($response);
            
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->metricsService->recordMetrics(
                'tag_validation',
                $executionTime,
                true,
                null,
                $this->estimateTokenCount($prompt),
                null,
                'claude-3-sonnet'
            );
            
            return $result;
            
        } catch (Exception $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->metricsService->recordMetrics(
                'tag_validation',
                $executionTime,
                false,
                $e->getMessage()
            );
            
            return ['error' => 'Failed to validate tags: ' . $e->getMessage()];
        }
    }

    /**
     * Build prompt for tag suggestion
     *
     * @param string $title The content title
     * @param string $content The main content
     * @param array $existingTags Current tags
     * @param array $availableTags Tags available in the system
     * @param int $maxSuggestions Maximum suggestions
     * @return string The formatted prompt
     */
    private function buildTagSuggestionPrompt(
        string $title,
        string $content,
        array $existingTags,
        array $availableTags,
        int $maxSuggestions
    ): string {
        $existingTagsStr = !empty($existingTags) ? implode(', ', $existingTags) : 'None';
        $availableTagsStr = !empty($availableTags) ? implode(', ', array_slice($availableTags, 0, 30)) : 'None';
        
        return "Analyze the following content and suggest relevant tags for categorization.

CONTENT TO ANALYZE:
Title: {$title}

Content: " . Text::truncate($content, 2000) . "

EXISTING TAGS: {$existingTagsStr}

POPULAR AVAILABLE TAGS: {$availableTagsStr}

INSTRUCTIONS:
1. Suggest up to {$maxSuggestions} relevant tags
2. Prioritize existing popular tags when appropriate
3. Create new tag suggestions only when necessary
4. Consider both technical and topical relevance
5. Ensure tags are concise and meaningful

Return your response as a JSON array with this structure:
{
  \"suggestions\": [
    {
      \"tag\": \"tag name\",
      \"confidence\": 0.85,
      \"reason\": \"why this tag is relevant\",
      \"existing\": true/false
    }
  ]
}";
    }

    /**
     * Build prompt for tag validation
     *
     * @param array $tags Tags to validate
     * @param string $content Content to validate against
     * @return string The formatted prompt
     */
    private function buildTagValidationPrompt(array $tags, string $content): string
    {
        $tagsStr = implode(', ', $tags);
        
        return "Validate the relevance of these tags for the given content:

TAGS TO VALIDATE: {$tagsStr}

CONTENT: " . Text::truncate($content, 1500) . "

INSTRUCTIONS:
1. Rate each tag's relevance (0.0 to 1.0)
2. Identify any irrelevant or misleading tags
3. Suggest improvements or alternatives
4. Check for redundant or overly similar tags

Return your response as a JSON object with this structure:
{
  \"validation\": {
    \"tag_name\": {
      \"relevance\": 0.85,
      \"status\": \"relevant|irrelevant|questionable\",
      \"reason\": \"explanation\",
      \"suggestions\": [\"alternative1\", \"alternative2\"]
    }
  },
  \"overall_quality\": 0.75,
  \"recommendations\": [\"specific recommendation\"]
}";
    }

    /**
     * Parse AI response for tag suggestions
     *
     * @param string $response AI response
     * @return array Parsed suggestions
     */
    private function parseTagSuggestions(string $response): array
    {
        try {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($data['suggestions'])) {
                return [
                    'success' => true,
                    'suggestions' => $data['suggestions']
                ];
            }
        } catch (Exception $e) {
            // Fall back to regex parsing if JSON fails
        }

        // Fallback: try to extract tags from response using regex
        $suggestions = [];
        if (preg_match_all('/["\']([\w\s-]+)["\']\s*:\s*[\d.]+/i', $response, $matches)) {
            foreach ($matches[1] as $index => $tag) {
                $suggestions[] = [
                    'tag' => trim($tag),
                    'confidence' => 0.7,
                    'reason' => 'Extracted from AI response',
                    'existing' => $this->isExistingTag($tag)
                ];
            }
        }

        return [
            'success' => !empty($suggestions),
            'suggestions' => $suggestions
        ];
    }

    /**
     * Parse AI response for tag validation
     *
     * @param string $response AI response
     * @return array Parsed validation results
     */
    private function parseTagValidation(string $response): array
    {
        try {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($data['validation'])) {
                return [
                    'success' => true,
                    'validation' => $data['validation'],
                    'overall_quality' => $data['overall_quality'] ?? 0.5,
                    'recommendations' => $data['recommendations'] ?? []
                ];
            }
        } catch (Exception $e) {
            // Continue to fallback
        }

        return [
            'success' => false,
            'error' => 'Failed to parse validation response'
        ];
    }

    /**
     * Check if a tag already exists in the system
     *
     * @param string $tagTitle The tag title to check
     * @return bool True if tag exists
     */
    private function isExistingTag(string $tagTitle): bool
    {
        return $this->tagsTable->find()
            ->where(['title' => $tagTitle])
            ->count() > 0;
    }

    /**
     * Estimate token count for cost calculation
     *
     * @param string $text Text to estimate
     * @return int Estimated token count
     */
    private function estimateTokenCount(string $text): int
    {
        // Rough approximation: 1 token ≈ 4 characters
        return (int)ceil(strlen($text) / 4);
    }

    /**
     * Create or get existing tags from suggestions
     *
     * @param array $suggestions Tag suggestions from AI
     * @param float $confidenceThreshold Minimum confidence to auto-create tags
     * @return array Array of tag entities
     */
    public function processTagSuggestions(array $suggestions, float $confidenceThreshold = 0.7): array
    {
        $processedTags = [];
        
        foreach ($suggestions as $suggestion) {
            $tagTitle = $suggestion['tag'] ?? '';
            $confidence = $suggestion['confidence'] ?? 0;
            
            if (empty($tagTitle) || $confidence < $confidenceThreshold) {
                continue;
            }
            
            // Check if tag exists
            $existingTag = $this->tagsTable->find()
                ->where(['title' => $tagTitle])
                ->first();
            
            if ($existingTag) {
                $processedTags[] = $existingTag;
            } else {
                // Create new tag if confidence is high enough
                if ($confidence >= 0.8) {
                    $newTag = $this->tagsTable->newEntity([
                        'title' => $tagTitle,
                        'slug' => Text::slug(strtolower($tagTitle)),
                        'article_count' => 0
                    ]);
                    
                    if ($this->tagsTable->save($newTag)) {
                        $processedTags[] = $newTag;
                    }
                }
            }
        }
        
        return $processedTags;
    }
}