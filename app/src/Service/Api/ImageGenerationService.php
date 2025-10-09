<?php
declare(strict_types=1);

namespace App\Service\Api;

use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\Http\Exception\ServiceUnavailableException;
use Cake\Log\LogTrait;
use Exception;

/**
 * ImageGenerationService Class
 *
 * This service handles AI-powered image generation for articles that don't have images.
 * It integrates with multiple AI image generation APIs and provides fallback options.
 * 
 * Supported providers:
 * - OpenAI DALL-E (primary)
 * - Anthropic Claude (text-based image prompts)
 * - Fallback to stock photo APIs
 */
class ImageGenerationService extends AbstractApiService
{
    use LogTrait;

    /**
     * OpenAI DALL-E API endpoint
     */
    private const OPENAI_API_URL = 'https://api.openai.com/v1/images/generations';

    /**
     * Supported image generation providers
     */
    public const PROVIDER_OPENAI = 'openai';
    public const PROVIDER_ANTHROPIC = 'anthropic';
    public const PROVIDER_STOCK = 'stock';

    /**
     * Default image generation parameters
     */
    private const DEFAULT_SIZE = '1024x1024';
    private const DEFAULT_QUALITY = 'standard';
    private const DEFAULT_STYLE = 'vivid';

    /**
     * @var string The active provider for image generation
     */
    private string $provider;

    /**
     * @var \\App\\Service\\Api\\RateLimitService Rate limiting service
     */
    private RateLimitService $rateLimitService;

    /**
     * ImageGenerationService constructor.
     */
    public function __construct()
    {
        $this->provider = SettingsManager::read('AI.imageGeneration.provider', self::PROVIDER_OPENAI);
        
        // Initialize based on provider
        switch ($this->provider) {
            case self::PROVIDER_OPENAI:
                // Priority order: environment variable, then database setting, then empty
                $apiKey = env('OPENAI_API_KEY') ?: SettingsManager::read('AI.imageGeneration.openaiApiKey', '');
                parent::__construct(new Client(), $apiKey, self::OPENAI_API_URL, '');
                break;
            case self::PROVIDER_ANTHROPIC:
            case self::PROVIDER_STOCK:
            default:
                // Fallback initialization
                parent::__construct(new Client(), '', '', '');
                break;
        }

        $this->rateLimitService = new RateLimitService();
    }

    /**
     * Generate an image for an article based on its title and content
     *
     * @param string $articleTitle The article title
     * @param string $articleBody The article content (optional)
     * @param array $options Additional generation options
     * @return array|null Generated image data or null on failure
     */
    public function generateArticleImage(string $articleTitle, string $articleBody = '', array $options = []): ?array
    {
        $enabled = SettingsManager::read('AI.imageGeneration.enabled', false);
        
        if (!$enabled) {
            $this->log('Image generation is disabled in settings', 'info');
            return null;
        }

        // Create a descriptive prompt based on article content
        $prompt = $this->buildImagePrompt($articleTitle, $articleBody, $options);
        
        switch ($this->provider) {
            case self::PROVIDER_OPENAI:
                return $this->generateWithOpenAI($prompt, $options);
            case self::PROVIDER_ANTHROPIC:
                return $this->generateWithAnthropic($prompt, $options);
            case self::PROVIDER_STOCK:
                return $this->generateWithStockAPI($articleTitle, $options);
            default:
                $this->log("Unsupported image generation provider: {$this->provider}", 'error');
                return null;
        }
    }

    /**
     * Generate an image for a product based on its details
     *
     * @param string $productTitle The product title
     * @param string $description The product description (optional)
     * @param string $manufacturer The product manufacturer (optional)
     * @param array $options Additional generation options
     * @return array|null Generated image data or null on failure
     */
    public function generateProductImage(string $productTitle, string $description = '', string $manufacturer = '', array $options = []): ?array
    {
        $enabled = SettingsManager::read('AI.imageGeneration.enabled', false);
        
        if (!$enabled) {
            $this->log('Image generation is disabled in settings', 'info');
            return null;
        }

        // Create a product-specific prompt
        $prompt = $this->buildProductImagePrompt($productTitle, $description, $manufacturer, $options);
        
        // Set product-specific options
        $options = array_merge([
            'context' => 'product listing image',
            'style' => 'natural',
            'orientation' => 'square'
        ], $options);
        
        switch ($this->provider) {
            case self::PROVIDER_OPENAI:
                return $this->generateWithOpenAI($prompt, $options);
            case self::PROVIDER_ANTHROPIC:
                return $this->generateWithAnthropic($prompt, $options);
            case self::PROVIDER_STOCK:
                // For stock photos, use product title + manufacturer as search query
                $searchQuery = trim($manufacturer . ' ' . $productTitle);
                return $this->generateWithStockAPI($searchQuery, $options);
            default:
                $this->log("Unsupported image generation provider: {$this->provider}", 'error');
                return null;
        }
    }

    /**
     * Generate image using OpenAI DALL-E
     *
     * @param string $prompt Image generation prompt
     * @param array $options Generation options
     * @return array|null Generated image data
     */
    private function generateWithOpenAI(string $prompt, array $options = []): ?array
    {
        if (empty($this->apiKey)) {
            $this->log('OpenAI API key is not configured', 'error');
            return null;
        }

        // Check rate limits
        if (!$this->rateLimitService->enforceLimit('image_generation')) {
            throw new ServiceUnavailableException('Image generation rate limit exceeded');
        }

        try {
            $payload = [
                'model' => SettingsManager::read('AI.imageGeneration.model', 'dall-e-3'),
                'prompt' => $this->sanitizePrompt($prompt),
                'size' => $options['size'] ?? self::DEFAULT_SIZE,
                'quality' => $options['quality'] ?? self::DEFAULT_QUALITY,
                'style' => $options['style'] ?? self::DEFAULT_STYLE,
                'n' => 1, // Generate one image
                'response_format' => 'url' // Get URL instead of base64
            ];

            $response = $this->sendRequest($payload, 60); // Longer timeout for image generation
            $data = $this->parseResponse($response);

            if ($data && isset($data['data'][0])) {
                $imageData = $data['data'][0];
                
                return [
                    'success' => true,
                    'provider' => self::PROVIDER_OPENAI,
                    'url' => $imageData['url'],
                    'revised_prompt' => $imageData['revised_prompt'] ?? $prompt,
                    'expires_at' => time() + (24 * 60 * 60), // URLs typically expire after 24 hours
                    'metadata' => [
                        'model' => $payload['model'],
                        'size' => $payload['size'],
                        'quality' => $payload['quality'],
                        'style' => $payload['style']
                    ]
                ];
            }

            return null;
        } catch (Exception $e) {
            $this->log('OpenAI image generation failed: ' . $e->getMessage(), 'error');
            
            // Try fallback method
            return $this->generateWithStockAPI(substr($prompt, 0, 50), $options);
        }
    }

    /**
     * Generate image prompt using Anthropic Claude and then use stock photos
     *
     * @param string $prompt Original prompt
     * @param array $options Generation options
     * @return array|null Generated image data
     */
    private function generateWithAnthropic(string $prompt, array $options = []): ?array
    {
        // Use Anthropic to improve the image search terms, then use stock API
        // This is a hybrid approach since Anthropic doesn't generate images directly
        
        $anthropicService = new \App\Service\Api\Anthropic\AnthropicApiService();
        
        try {
            // Create a query to get better search terms
            $searchTerms = $this->generateImageSearchTerms($prompt, $anthropicService);
            
            // Use the enhanced search terms with stock API
            return $this->generateWithStockAPI($searchTerms, $options);
        } catch (Exception $e) {
            $this->log('Anthropic-assisted image search failed: ' . $e->getMessage(), 'error');
            
            // Fallback to basic stock search
            return $this->generateWithStockAPI($prompt, $options);
        }
    }

    /**
     * Generate using stock photo APIs (Unsplash, Pexels, etc.)
     *
     * @param string $searchQuery Search query for stock photos
     * @param array $options Generation options
     * @return array|null Stock image data
     */
    private function generateWithStockAPI(string $searchQuery, array $options = []): ?array
    {
        $unsplashApiKey = SettingsManager::read('AI.imageGeneration.unsplashApiKey', '');
        
        if (empty($unsplashApiKey)) {
            $this->log('No stock photo API keys configured', 'warning');
            return null;
        }

        try {
            $client = new Client();
            $searchUrl = 'https://api.unsplash.com/search/photos';
            
            $response = $client->get($searchUrl, [
                'query' => $this->sanitizeSearchQuery($searchQuery),
                'per_page' => 1,
                'orientation' => $options['orientation'] ?? 'landscape',
                'content_filter' => 'high',
                'order_by' => 'relevance'
            ], [
                'headers' => [
                    'Authorization' => 'Client-ID ' . $unsplashApiKey,
                    'Accept-Version' => 'v1'
                ]
            ]);

            if ($response->isOk()) {
                $data = $response->getJson();
                
                if (!empty($data['results'])) {
                    $image = $data['results'][0];
                    
                    return [
                        'success' => true,
                        'provider' => self::PROVIDER_STOCK,
                        'url' => $image['urls']['regular'],
                        'download_url' => $image['urls']['full'],
                        'alt_text' => $image['alt_description'] ?? $searchQuery,
                        'attribution' => [
                            'photographer' => $image['user']['name'],
                            'photographer_url' => $image['user']['links']['html'],
                            'source' => 'Unsplash',
                            'source_url' => $image['links']['html']
                        ],
                        'metadata' => [
                            'width' => $image['width'],
                            'height' => $image['height'],
                            'color' => $image['color'] ?? '#000000'
                        ]
                    ];
                }
            }

            $this->log('No suitable stock images found for query: ' . $searchQuery, 'info');
            return null;
        } catch (Exception $e) {
            $this->log('Stock photo API failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Build an appropriate image generation prompt from article content
     *
     * @param string $title Article title
     * @param string $body Article body content
     * @param array $options Additional options
     * @return string Generated prompt
     */
    private function buildImagePrompt(string $title, string $body = '', array $options = []): string
    {
        $style = $options['style'] ?? SettingsManager::read('AI.imageGeneration.defaultStyle', 'professional');
        $context = $options['context'] ?? 'article illustration';
        
        // Extract key concepts from title and body
        $content = $title;
        if (!empty($body)) {
            // Get first paragraph or up to 200 characters of body
            $bodyPreview = strip_tags($body);
            $bodyPreview = substr($bodyPreview, 0, 200);
            if (strlen($bodyPreview) >= 200) {
                $bodyPreview = substr($bodyPreview, 0, strrpos($bodyPreview, ' ')) . '...';
            }
            $content .= '. ' . $bodyPreview;
        }

        // Build the prompt based on the style
        switch ($style) {
            case 'photographic':
                $prompt = "A high-quality, professional photograph representing '{$title}'. {$content}. Realistic lighting, sharp focus, suitable for {$context}.";
                break;
            case 'illustration':
                $prompt = "A modern digital illustration of '{$title}'. {$content}. Clean, vector-style artwork suitable for {$context}.";
                break;
            case 'abstract':
                $prompt = "An abstract visual representation of '{$title}'. {$content}. Modern, artistic interpretation suitable for {$context}.";
                break;
            case 'minimalist':
                $prompt = "A minimalist, clean visual representation of '{$title}'. Simple composition, plenty of white space, suitable for {$context}.";
                break;
            default: // professional
                $prompt = "A professional, high-quality image representing '{$title}'. {$content}. Clean, modern aesthetic suitable for {$context}.";
        }

        return $prompt;
    }

    /**
     * Build an appropriate image generation prompt from product details
     *
     * @param string $title Product title
     * @param string $description Product description
     * @param string $manufacturer Product manufacturer
     * @param array $options Additional options
     * @return string Generated prompt
     */
    private function buildProductImagePrompt(string $title, string $description = '', string $manufacturer = '', array $options = []): string
    {
        $style = $options['style'] ?? 'commercial';
        $context = $options['context'] ?? 'product listing image';
        $orientation = $options['orientation'] ?? 'square';
        
        // Build product description for prompt
        $productDetails = $title;
        
        if (!empty($manufacturer)) {
            $productDetails = $manufacturer . ' ' . $productDetails;
        }
        
        if (!empty($description)) {
            // Clean and shorten description
            $cleanDescription = strip_tags($description);
            $cleanDescription = substr($cleanDescription, 0, 150);
            if (strlen($cleanDescription) >= 150) {
                $cleanDescription = substr($cleanDescription, 0, strrpos($cleanDescription, ' ')) . '...';
            }
            $productDetails .= '. ' . $cleanDescription;
        }
        
        // Build the prompt based on the style and context
        switch ($style) {
            case 'commercial':
                $prompt = "Professional product photography of {$productDetails}. Clean white background, studio lighting, high quality commercial image, {$orientation} format, suitable for {$context}.";
                break;
            case 'lifestyle':
                $prompt = "Lifestyle product photo showing {$productDetails} in use. Natural lighting, real-world setting, attractive composition, {$orientation} format, suitable for {$context}.";
                break;
            case 'technical':
                $prompt = "Technical product illustration of {$productDetails}. Clean, detailed, engineering-style drawing with specifications visible, {$orientation} format, suitable for {$context}.";
                break;
            case 'minimalist':
                $prompt = "Minimalist product photo of {$productDetails}. Simple composition, clean background, modern aesthetic, {$orientation} format, suitable for {$context}.";
                break;
            case 'artistic':
                $prompt = "Artistic product photography of {$productDetails}. Creative lighting and composition, visually appealing, {$orientation} format, suitable for {$context}.";
                break;
            default: // professional
                $prompt = "High-quality professional image of {$productDetails}. Clean, well-lit, commercial grade photography, {$orientation} format, suitable for {$context}.";
        }
        
        // Add quality and technical specifications
        $prompt .= " High resolution, sharp focus, professional quality.";
        
        return $prompt;
    }

    /**
     * Generate enhanced search terms using Anthropic Claude
     *
     * @param string $originalPrompt Original search prompt
     * @param AnthropicApiService $anthropicService Anthropic service instance
     * @return string Enhanced search terms
     */
    private function generateImageSearchTerms(string $originalPrompt, $anthropicService): string
    {
        $payload = [
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 100,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Generate 3-5 specific keywords for searching stock photos that would represent this concept: '{$originalPrompt}'. Return only comma-separated keywords, no explanation."
                ]
            ]
        ];

        try {
            $response = $anthropicService->sendRequest($payload);
            $data = $anthropicService->parseResponse($response);
            
            // Extract the keywords from the response
            if (is_array($data) && !empty($data)) {
                $keywords = reset($data); // Get first element if array
                return is_string($keywords) ? $keywords : $originalPrompt;
            }
            
            return $originalPrompt;
        } catch (Exception $e) {
            $this->log('Failed to generate enhanced search terms: ' . $e->getMessage(), 'warning');
            return $originalPrompt;
        }
    }

    /**
     * Sanitize prompt for AI image generation
     *
     * @param string $prompt Raw prompt
     * @return string Sanitized prompt
     */
    private function sanitizePrompt(string $prompt): string
    {
        // Remove any potentially problematic content
        $sanitized = strip_tags($prompt);
        $sanitized = preg_replace('/[^\w\s\-.,!?]/', '', $sanitized);
        $sanitized = trim($sanitized);
        
        // Limit length (DALL-E has prompt limits)
        if (strlen($sanitized) > 1000) {
            $sanitized = substr($sanitized, 0, 997) . '...';
        }
        
        return $sanitized;
    }

    /**
     * Sanitize search query for stock photo APIs
     *
     * @param string $query Raw search query
     * @return string Sanitized query
     */
    private function sanitizeSearchQuery(string $query): string
    {
        // Extract key words from the query
        $words = str_word_count($query, 1);
        $words = array_filter($words, function($word) {
            return strlen($word) > 2; // Remove very short words
        });
        
        // Take up to 5 most relevant words
        $words = array_slice($words, 0, 5);
        
        return implode(' ', $words);
    }

    /**
     * Download image from URL and save it locally
     *
     * @param string $imageUrl Image URL to download
     * @param string $targetPath Local path to save the image
     * @return bool Success status
     */
    public function downloadImage(string $imageUrl, string $targetPath): bool
    {
        try {
            $client = new Client();
            $response = $client->get($imageUrl);

            if ($response->isOk()) {
                $imageData = $response->getStringBody();
                
                // Ensure target directory exists
                $directory = dirname($targetPath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                return file_put_contents($targetPath, $imageData) !== false;
            }

            return false;
        } catch (Exception $e) {
            $this->log('Failed to download image: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Get headers for API requests
     *
     * @return array Headers array
     */
    protected function getHeaders(): array
    {
        switch ($this->provider) {
            case self::PROVIDER_OPENAI:
                return [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ];
            default:
                return [
                    'Content-Type' => 'application/json'
                ];
        }
    }

    /**
     * Parse API response
     *
     * @param Response $response API response
     * @return array Parsed response data
     */
    protected function parseResponse(Response $response): array
    {
        return $response->getJson();
    }
}