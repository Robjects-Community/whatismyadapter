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
 * AI Slug Validation and Formatting Service
 *
 * This service uses AI to validate and optimize URL slugs for SEO,
 * readability, and consistency across the application.
 */
class SlugValidationService
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
     * @var \App\Model\Table\SlugsTable
     */
    private $slugsTable;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->metricsService = new AiMetricsService();
        $this->slugsTable = TableRegistry::getTableLocator()->get('Slugs');
        
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
     * Generate optimized slug suggestions for content
     *
     * @param string $title The title to generate slug from
     * @param string $content Optional content for context
     * @param string $model Model type (Articles, Products, etc.)
     * @param int $maxSuggestions Maximum number of suggestions
     * @return array Array of slug suggestions with SEO scores
     */
    public function suggestSlugs(string $title, string $content = '', string $model = 'Articles', int $maxSuggestions = 5): array
    {
        if (!isset($this->anthropicService)) {
            return ['error' => 'AI service not configured'];
        }

        try {
            $startTime = microtime(true);
            
            // Get existing slugs for context and uniqueness checking
            $existingSlugs = $this->getExistingSlugs($model);
            
            $prompt = $this->buildSlugSuggestionPrompt($title, $content, $existingSlugs, $maxSuggestions);
            $response = $this->anthropicService->sendMessage($prompt);
            $result = $this->parseSlugSuggestions($response, $model);
            
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->metricsService->recordMetrics(
                'slug_generation',
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
                'slug_generation',
                $executionTime,
                false,
                $e->getMessage()
            );
            
            return ['error' => 'Failed to generate slug suggestions: ' . $e->getMessage()];
        }
    }

    /**
     * Validate existing slug for SEO best practices
     *
     * @param string $slug The slug to validate
     * @param string $title The associated title
     * @param string $content Optional content for context
     * @param string $model Model type
     * @return array Validation results with recommendations
     */
    public function validateSlug(string $slug, string $title, string $content = '', string $model = 'Articles'): array
    {
        if (!isset($this->anthropicService)) {
            return ['error' => 'AI service not configured'];
        }

        try {
            $startTime = microtime(true);
            
            // Check for conflicts with existing slugs
            $conflicts = $this->checkSlugConflicts($slug, $model);
            
            $prompt = $this->buildSlugValidationPrompt($slug, $title, $content, $conflicts);
            $response = $this->anthropicService->sendMessage($prompt);
            $result = $this->parseSlugValidation($response);
            
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->metricsService->recordMetrics(
                'slug_validation',
                $executionTime,
                true,
                null,
                $this->estimateTokenCount($prompt),
                null,
                'claude-3-sonnet'
            );
            
            // Add technical validation results
            $result['technical_validation'] = $this->performTechnicalValidation($slug, $title);
            
            return $result;
            
        } catch (Exception $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->metricsService->recordMetrics(
                'slug_validation',
                $executionTime,
                false,
                $e->getMessage()
            );
            
            return ['error' => 'Failed to validate slug: ' . $e->getMessage()];
        }
    }

    /**
     * Optimize an existing slug for better SEO
     *
     * @param string $currentSlug Current slug
     * @param string $title Associated title
     * @param string $content Optional content
     * @param string $model Model type
     * @return array Optimization suggestions
     */
    public function optimizeSlug(string $currentSlug, string $title, string $content = '', string $model = 'Articles'): array
    {
        $validation = $this->validateSlug($currentSlug, $title, $content, $model);
        
        if (isset($validation['error'])) {
            return $validation;
        }

        // If current slug scores well, suggest minimal improvements
        $currentScore = $validation['seo_score'] ?? 0;
        
        if ($currentScore >= 0.8) {
            return [
                'current_slug' => $currentSlug,
                'current_score' => $currentScore,
                'optimization_needed' => false,
                'message' => 'Current slug is already well optimized'
            ];
        }

        // Generate better alternatives
        $suggestions = $this->suggestSlugs($title, $content, $model, 3);
        
        return [
            'current_slug' => $currentSlug,
            'current_score' => $currentScore,
            'optimization_needed' => true,
            'suggestions' => $suggestions['suggestions'] ?? [],
            'validation_issues' => $validation['issues'] ?? []
        ];
    }

    /**
     * Build prompt for slug generation
     *
     * @param string $title Content title
     * @param string $content Content text
     * @param array $existingSlugs Existing slugs to avoid conflicts
     * @param int $maxSuggestions Maximum suggestions
     * @return string Formatted prompt
     */
    private function buildSlugSuggestionPrompt(string $title, string $content, array $existingSlugs, int $maxSuggestions): string
    {
        $existingSlugsStr = !empty($existingSlugs) ? implode(', ', array_slice($existingSlugs, 0, 20)) : 'None';
        $contentPreview = !empty($content) ? Text::truncate($content, 500) : 'No additional content';
        
        return "Generate optimized URL slugs for the following content:

TITLE: {$title}

CONTENT PREVIEW: {$contentPreview}

EXISTING SLUGS (avoid conflicts): {$existingSlugsStr}

SEO REQUIREMENTS:
1. Keep slugs between 3-60 characters
2. Use hyphens to separate words
3. Include primary keywords from title
4. Avoid stop words when possible
5. Ensure readability and user-friendliness
6. Make slugs unique and descriptive
7. Consider search intent and relevance

Generate {$maxSuggestions} slug suggestions and return as JSON:
{
  \"suggestions\": [
    {
      \"slug\": \"optimized-slug-example\",
      \"seo_score\": 0.85,
      \"length\": 24,
      \"keywords_included\": [\"optimized\", \"slug\"],
      \"reasoning\": \"Why this slug is effective\",
      \"readability\": \"high\",
      \"uniqueness\": \"confirmed\"
    }
  ]
}";
    }

    /**
     * Build prompt for slug validation
     *
     * @param string $slug Slug to validate
     * @param string $title Associated title
     * @param string $content Content text
     * @param array $conflicts Any existing conflicts
     * @return string Formatted prompt
     */
    private function buildSlugValidationPrompt(string $slug, string $title, string $content, array $conflicts): string
    {
        $conflictsStr = !empty($conflicts) ? 'Yes: ' . implode(', ', $conflicts) : 'No conflicts detected';
        $contentPreview = !empty($content) ? Text::truncate($content, 300) : 'No additional content';
        
        return "Validate this URL slug for SEO effectiveness and best practices:

SLUG TO VALIDATE: {$slug}
TITLE: {$title}
CONTENT PREVIEW: {$contentPreview}
CONFLICTS WITH EXISTING SLUGS: {$conflictsStr}

EVALUATION CRITERIA:
1. SEO friendliness (keyword inclusion, length, structure)
2. Readability and user experience
3. Technical compliance (characters, format)
4. Uniqueness and brandability
5. Search engine optimization potential
6. Mobile and sharing friendliness

Provide detailed analysis as JSON:
{
  \"seo_score\": 0.75,
  \"readability_score\": 0.80,
  \"technical_score\": 0.90,
  \"overall_score\": 0.82,
  \"issues\": [
    {
      \"type\": \"warning|error|info\",
      \"message\": \"Specific issue description\",
      \"suggestion\": \"How to fix this issue\"
    }
  ],
  \"strengths\": [\"What works well about this slug\"],
  \"recommendations\": [\"Specific improvement suggestions\"],
  \"alternative_suggestions\": [\"better-slug-option-1\", \"better-slug-option-2\"]
}";
    }

    /**
     * Get existing slugs for a model to avoid conflicts
     *
     * @param string $model Model name
     * @param int $limit Maximum number to retrieve
     * @return array Array of existing slugs
     */
    private function getExistingSlugs(string $model, int $limit = 100): array
    {
        return $this->slugsTable->find()
            ->select(['slug'])
            ->where(['model' => $model])
            ->orderBy(['created' => 'DESC'])
            ->limit($limit)
            ->toArray();
    }

    /**
     * Check for slug conflicts
     *
     * @param string $slug Slug to check
     * @param string $model Model name
     * @return array Array of conflicting slugs
     */
    private function checkSlugConflicts(string $slug, string $model): array
    {
        $conflicts = [];
        
        // Exact match
        $exactMatch = $this->slugsTable->find()
            ->where(['slug' => $slug, 'model' => $model])
            ->first();
            
        if ($exactMatch) {
            $conflicts[] = $slug . ' (exact match)';
        }
        
        // Similar matches
        $similar = $this->slugsTable->find()
            ->where([
                'slug LIKE' => $slug . '%',
                'model' => $model
            ])
            ->limit(5)
            ->toArray();
            
        foreach ($similar as $s) {
            if ($s->slug !== $slug) {
                $conflicts[] = $s->slug . ' (similar)';
            }
        }
        
        return $conflicts;
    }

    /**
     * Perform technical validation of slug
     *
     * @param string $slug Slug to validate
     * @param string $title Associated title
     * @return array Technical validation results
     */
    private function performTechnicalValidation(string $slug, string $title): array
    {
        $issues = [];
        $score = 1.0;
        
        // Length check
        $length = strlen($slug);
        if ($length < 3) {
            $issues[] = 'Slug is too short (minimum 3 characters)';
            $score -= 0.3;
        } elseif ($length > 60) {
            $issues[] = 'Slug is too long (maximum 60 characters recommended)';
            $score -= 0.2;
        }
        
        // Character validation
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $issues[] = 'Slug contains invalid characters (only lowercase letters, numbers, and hyphens allowed)';
            $score -= 0.4;
        }
        
        // Structure validation
        if (preg_match('/^-|-$/', $slug)) {
            $issues[] = 'Slug cannot start or end with a hyphen';
            $score -= 0.3;
        }
        
        if (strpos($slug, '--') !== false) {
            $issues[] = 'Slug contains consecutive hyphens';
            $score -= 0.2;
        }
        
        // Keyword relevance check
        $titleWords = array_map('strtolower', preg_split('/\s+/', $title));
        $slugWords = explode('-', $slug);
        $keywordMatch = count(array_intersect($titleWords, $slugWords)) / count($titleWords);
        
        if ($keywordMatch < 0.3) {
            $issues[] = 'Slug does not contain enough keywords from the title';
            $score -= 0.2;
        }
        
        return [
            'score' => max(0, $score),
            'issues' => $issues,
            'length' => $length,
            'keyword_match_ratio' => $keywordMatch
        ];
    }

    /**
     * Parse AI response for slug suggestions
     *
     * @param string $response AI response
     * @param string $model Model type for validation
     * @return array Parsed suggestions
     */
    private function parseSlugSuggestions(string $response, string $model): array
    {
        try {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($data['suggestions'])) {
                // Validate each suggestion
                $validSuggestions = [];
                foreach ($data['suggestions'] as $suggestion) {
                    if (isset($suggestion['slug'])) {
                        // Add uniqueness check
                        $conflicts = $this->checkSlugConflicts($suggestion['slug'], $model);
                        $suggestion['has_conflicts'] = !empty($conflicts);
                        $suggestion['conflicts'] = $conflicts;
                        
                        $validSuggestions[] = $suggestion;
                    }
                }
                
                return [
                    'success' => true,
                    'suggestions' => $validSuggestions
                ];
            }
        } catch (Exception $e) {
            // Fall back to basic generation
        }

        // Fallback: generate basic slug from title
        $basicSlug = Text::slug(strtolower($title ?? ''));
        return [
            'success' => false,
            'fallback_slug' => $basicSlug,
            'error' => 'Failed to parse AI suggestions'
        ];
    }

    /**
     * Parse AI response for slug validation
     *
     * @param string $response AI response
     * @return array Parsed validation results
     */
    private function parseSlugValidation(string $response): array
    {
        try {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'success' => true,
                    'seo_score' => $data['seo_score'] ?? 0.5,
                    'readability_score' => $data['readability_score'] ?? 0.5,
                    'technical_score' => $data['technical_score'] ?? 0.5,
                    'overall_score' => $data['overall_score'] ?? 0.5,
                    'issues' => $data['issues'] ?? [],
                    'strengths' => $data['strengths'] ?? [],
                    'recommendations' => $data['recommendations'] ?? [],
                    'alternative_suggestions' => $data['alternative_suggestions'] ?? []
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
     * Estimate token count for cost calculation
     *
     * @param string $text Text to estimate
     * @return int Estimated token count
     */
    private function estimateTokenCount(string $text): int
    {
        return (int)ceil(strlen($text) / 4);
    }

    /**
     * Generate SEO-optimized slug with fallback
     *
     * @param string $title Title to slugify
     * @param string $model Model type
     * @return string Generated slug
     */
    public function generateSlugWithFallback(string $title, string $model = 'Articles'): string
    {
        // Try AI-powered generation first
        $aiSuggestions = $this->suggestSlugs($title, '', $model, 1);
        
        if (isset($aiSuggestions['suggestions'][0]['slug']) && 
            !$aiSuggestions['suggestions'][0]['has_conflicts']) {
            return $aiSuggestions['suggestions'][0]['slug'];
        }
        
        // Fallback to basic slug generation with conflict resolution
        $baseSlug = Text::slug(strtolower($title));
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->checkSlugConflicts($slug, $model)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            
            if ($counter > 100) { // Prevent infinite loop
                $slug = $baseSlug . '-' . uniqid();
                break;
            }
        }
        
        return $slug;
    }
}