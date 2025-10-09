<?php
declare(strict_types=1);

namespace App\Service\Ai;

use App\Service\Ai\SlugValidationService;
use App\Service\Ai\TagDetectionService;
use App\Service\Api\AiMetricsService;
use App\Service\Api\Anthropic\AnthropicApiService;
use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Utility\Text;
use Exception;

/**
 * External Webpage Extraction Service
 *
 * This service extracts and analyzes content from external webpages,
 * using AI to generate structured page data suitable for CMS management.
 */
class WebpageExtractionService
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
     * @var \App\Service\Ai\TagDetectionService
     */
    private TagDetectionService $tagService;

    /**
     * @var \App\Service\Ai\SlugValidationService
     */
    private SlugValidationService $slugService;

    /**
     * @var \Cake\Http\Client
     */
    private Client $httpClient;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->metricsService = new AiMetricsService();
        $this->tagService = new TagDetectionService();
        $this->slugService = new SlugValidationService();
        $this->httpClient = new Client(['timeout' => 30]);
        
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
     * Extract and analyze webpage content
     *
     * @param string $url The URL to extract content from
     * @param array $options Optional extraction options
     * @return array Extracted and analyzed webpage data
     */
    public function extractWebpage(string $url, array $options = []): array
    {
        if (!isset($this->anthropicService)) {
            return ['error' => 'AI service not configured'];
        }

        try {
            $startTime = microtime(true);
            
            // Step 1: Fetch the webpage content
            $rawContent = $this->fetchWebpageContent($url);
            if (isset($rawContent['error'])) {
                return $rawContent;
            }

            // Step 2: Extract structured data using AI
            $extractedData = $this->analyzeContentWithAI($rawContent, $url, $options);
            if (isset($extractedData['error'])) {
                return $extractedData;
            }

            // Step 3: Generate AI-powered enhancements
            $enhancements = $this->generateEnhancements($extractedData);

            // Step 4: Combine all data
            $result = array_merge($extractedData, $enhancements);
            $result['source_url'] = $url;
            $result['extraction_timestamp'] = date('Y-m-d H:i:s');

            // Record metrics
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->metricsService->recordMetrics(
                'webpage_extraction',
                $executionTime,
                true,
                null,
                $this->estimateTokenCount($rawContent['content']),
                null,
                'claude-3-sonnet'
            );

            return ['success' => true, 'data' => $result];

        } catch (Exception $e) {
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->metricsService->recordMetrics(
                'webpage_extraction',
                $executionTime,
                false,
                $e->getMessage()
            );

            return ['error' => 'Failed to extract webpage: ' . $e->getMessage()];
        }
    }

    /**
     * Fetch raw content from webpage
     *
     * @param string $url URL to fetch
     * @return array Raw content data or error
     */
    private function fetchWebpageContent(string $url): array
    {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['error' => 'Invalid URL format'];
        }

        // Security check - only allow HTTP/HTTPS
        $parsed = parse_url($url);
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            return ['error' => 'Only HTTP and HTTPS URLs are allowed'];
        }

        try {
            $response = $this->httpClient->get($url, [], [
                'headers' => [
                    'User-Agent' => 'WillowCMS/1.0 Content Extractor',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                ]
            ]);

            if (!$response->isOk()) {
                return ['error' => 'Failed to fetch webpage: HTTP ' . $response->getStatusCode()];
            }

            $content = $response->getStringBody();
            $contentType = $response->getHeaderLine('Content-Type');

            // Check if it's HTML content
            if (strpos($contentType, 'text/html') === false) {
                return ['error' => 'URL does not return HTML content'];
            }

            return [
                'content' => $content,
                'content_type' => $contentType,
                'final_url' => $response->getHeaderLine('Location') ?: $url,
                'size' => strlen($content)
            ];

        } catch (Exception $e) {
            return ['error' => 'Network error: ' . $e->getMessage()];
        }
    }

    /**
     * Analyze content using AI
     *
     * @param array $rawContent Raw webpage content
     * @param string $url Original URL
     * @param array $options Extraction options
     * @return array Structured content data
     */
    private function analyzeContentWithAI(array $rawContent, string $url, array $options): array
    {
        $content = $rawContent['content'];
        
        // Clean and prepare content for AI analysis
        $cleanedContent = $this->cleanHtmlContent($content);
        
        $prompt = $this->buildExtractionPrompt($cleanedContent, $url, $options);
        
        try {
            $response = $this->anthropicService->sendMessage($prompt);
            return $this->parseExtractionResponse($response);
        } catch (Exception $e) {
            return ['error' => 'AI analysis failed: ' . $e->getMessage()];
        }
    }

    /**
     * Clean HTML content for AI processing
     *
     * @param string $html Raw HTML content
     * @return string Cleaned text content
     */
    private function cleanHtmlContent(string $html): string
    {
        // Remove script and style tags
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);
        
        // Extract title
        $titleMatch = [];
        preg_match('/<title[^>]*>(.*?)<\/title>/i', $html, $titleMatch);
        $title = isset($titleMatch[1]) ? strip_tags($titleMatch[1]) : '';
        
        // Extract meta description
        $descMatch = [];
        preg_match('/<meta[^>]+name=["\']description["\'][^>]*content=["\']([^"\']+)["\'][^>]*>/i', $html, $descMatch);
        $description = isset($descMatch[1]) ? $descMatch[1] : '';
        
        // Convert HTML to text
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Truncate for AI processing
        $text = Text::truncate($text, 3000);
        
        return json_encode([
            'title' => $title,
            'description' => $description,
            'content' => $text
        ]);
    }

    /**
     * Build extraction prompt for AI
     *
     * @param string $content Cleaned content
     * @param string $url Source URL
     * @param array $options Extraction options
     * @return string AI prompt
     */
    private function buildExtractionPrompt(string $content, string $url, array $options): string
    {
        $extractionType = $options['type'] ?? 'article';
        $targetAudience = $options['audience'] ?? 'general';
        
        return "Extract and structure the following webpage content for CMS management:

SOURCE URL: {$url}
CONTENT TYPE: {$extractionType}
TARGET AUDIENCE: {$targetAudience}

RAW CONTENT DATA: {$content}

EXTRACTION REQUIREMENTS:
1. Generate a clear, descriptive title
2. Create a compelling meta description (150-160 characters)
3. Extract or generate a summary (2-3 sentences)
4. Identify key topics and themes
5. Extract main content sections
6. Determine content category/type
7. Assess content quality and credibility
8. Identify target keywords for SEO

Return the analysis as JSON:
{
  \"title\": \"Extracted/refined title\",
  \"meta_description\": \"SEO-optimized meta description\",
  \"summary\": \"Brief content summary\",
  \"content_type\": \"article|blog|news|tutorial|reference|other\",
  \"main_content\": \"Key content paragraphs\",
  \"key_topics\": [\"topic1\", \"topic2\", \"topic3\"],
  \"target_keywords\": [\"keyword1\", \"keyword2\", \"keyword3\"],
  \"quality_score\": 0.85,
  \"credibility_indicators\": [\"author info\", \"publication date\", \"sources\"],
  \"content_sections\": [
    {
      \"heading\": \"Section title\",
      \"content\": \"Section content\"
    }
  ],
  \"estimated_read_time\": 5,
  \"language\": \"en\",
  \"publication_date\": \"2024-01-15\",
  \"author_info\": \"Author name or source\"
}";
    }

    /**
     * Generate AI-powered enhancements
     *
     * @param array $extractedData Base extracted data
     * @return array Enhancement data
     */
    private function generateEnhancements(array $extractedData): array
    {
        $enhancements = [];

        // Generate SEO-optimized slug
        if (isset($extractedData['title'])) {
            $slugResult = $this->slugService->suggestSlugs($extractedData['title'], $extractedData['main_content'] ?? '', 'Pages', 3);
            $enhancements['suggested_slugs'] = $slugResult['suggestions'] ?? [];
            $enhancements['recommended_slug'] = $this->slugService->generateSlugWithFallback($extractedData['title'], 'Pages');
        }

        // Generate tag suggestions
        if (isset($extractedData['title']) && isset($extractedData['main_content'])) {
            $tagResult = $this->tagService->suggestTags(
                $extractedData['title'], 
                $extractedData['main_content'], 
                $extractedData['key_topics'] ?? []
            );
            $enhancements['suggested_tags'] = $tagResult['suggestions'] ?? [];
        }

        return $enhancements;
    }

    /**
     * Parse AI extraction response
     *
     * @param string $response AI response
     * @return array Parsed extraction data
     */
    private function parseExtractionResponse(string $response): array
    {
        try {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Validate required fields
                $requiredFields = ['title', 'meta_description', 'summary', 'content_type'];
                foreach ($requiredFields as $field) {
                    if (!isset($data[$field])) {
                        $data[$field] = '';
                    }
                }
                
                return $data;
            }
        } catch (Exception $e) {
            // Fall back to basic extraction
        }

        // Fallback extraction
        return [
            'title' => 'Extracted Content',
            'meta_description' => 'Content extracted from external webpage',
            'summary' => 'Content analysis unavailable',
            'content_type' => 'article',
            'main_content' => '',
            'key_topics' => [],
            'target_keywords' => [],
            'quality_score' => 0.5,
            'error' => 'Failed to parse AI response'
        ];
    }

    /**
     * Create page entity from extracted data
     *
     * @param array $extractedData Extracted webpage data
     * @param array $pageOptions Additional page options
     * @return array Page entity data
     */
    public function createPageFromExtraction(array $extractedData, array $pageOptions = []): array
    {
        $data = $extractedData['data'] ?? $extractedData;
        
        // Build page entity data
        $pageData = [
            'title' => $data['title'] ?? 'Untitled Page',
            'slug' => $data['recommended_slug'] ?? Text::slug(strtolower($data['title'] ?? 'untitled')),
            'meta_description' => $data['meta_description'] ?? '',
            'body' => $this->formatContentForPage($data),
            'is_published' => $pageOptions['auto_publish'] ?? false,
            'article_type' => 'page',
            'source_url' => $data['source_url'] ?? null,
            'extraction_metadata' => json_encode([
                'extraction_timestamp' => $data['extraction_timestamp'] ?? date('Y-m-d H:i:s'),
                'quality_score' => $data['quality_score'] ?? 0.5,
                'content_type' => $data['content_type'] ?? 'article',
                'estimated_read_time' => $data['estimated_read_time'] ?? null
            ])
        ];

        // Add tags if available
        if (isset($data['suggested_tags']) && !empty($data['suggested_tags'])) {
            $pageData['suggested_tag_titles'] = array_column($data['suggested_tags'], 'tag');
        }

        return $pageData;
    }

    /**
     * Format extracted content for page body
     *
     * @param array $data Extracted content data
     * @return string Formatted HTML content
     */
    private function formatContentForPage(array $data): string
    {
        $html = '';
        
        // Add summary if available
        if (!empty($data['summary'])) {
            $html .= '<div class="lead">' . htmlspecialchars($data['summary']) . '</div>' . "\n\n";
        }
        
        // Add main content
        if (!empty($data['main_content'])) {
            $html .= '<div class="main-content">' . "\n";
            $html .= nl2br(htmlspecialchars($data['main_content']));
            $html .= "\n" . '</div>' . "\n\n";
        }
        
        // Add structured sections if available
        if (!empty($data['content_sections'])) {
            foreach ($data['content_sections'] as $section) {
                if (!empty($section['heading'])) {
                    $html .= '<h3>' . htmlspecialchars($section['heading']) . '</h3>' . "\n";
                }
                if (!empty($section['content'])) {
                    $html .= '<p>' . nl2br(htmlspecialchars($section['content'])) . '</p>' . "\n\n";
                }
            }
        }
        
        // Add source attribution
        if (!empty($data['source_url'])) {
            $html .= '<div class="source-attribution mt-4">' . "\n";
            $html .= '<p><small>Source: <a href="' . htmlspecialchars($data['source_url']) . '" target="_blank" rel="noopener">';
            $html .= htmlspecialchars($data['source_url']) . '</a></small></p>' . "\n";
            $html .= '</div>' . "\n";
        }
        
        return $html;
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
     * Batch extract multiple URLs
     *
     * @param array $urls Array of URLs to extract
     * @param array $options Extraction options
     * @return array Results for each URL
     */
    public function batchExtractWebpages(array $urls, array $options = []): array
    {
        $results = [];
        $maxBatch = $options['max_batch'] ?? 5;
        
        foreach (array_slice($urls, 0, $maxBatch) as $index => $url) {
            $results[$url] = $this->extractWebpage($url, $options);
            
            // Add small delay to be respectful to target servers
            if ($index < count($urls) - 1) {
                usleep(500000); // 0.5 second delay
            }
        }
        
        return $results;
    }
}