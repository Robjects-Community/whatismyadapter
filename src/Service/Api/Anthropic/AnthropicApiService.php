<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use App\Model\Table\AipromptsTable;
use App\Service\Api\AbstractApiService;
use App\Utility\SettingsManager;
use Cake\Http\Client;
use Cake\Http\Client\Response;
use Cake\ORM\TableRegistry;

/**
 * AnthropicApiService Class
 *
 * This service class provides an interface to interact with the Anthropic API,
 * handling various AI-related tasks such as SEO content generation, image analysis,
 * comment moderation, and text summarization.
 */
class AnthropicApiService extends AbstractApiService
{
    /**
     * The base URL for the Anthropic API.
     *
     * @var string
     */
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    /**
     * The version of the Anthropic API being used.
     *
     * @var string
     */
    private const API_VERSION = '2023-06-01';

    /**
     * @var \App\Model\Table\AipromptsTable The table instance for AI prompts.
     */
    private AipromptsTable $aipromptsTable;

    /**
     * @var \App\Service\Api\Anthropic\SeoContentGenerator The SEO content generator service.
     */
    private SeoContentGenerator $seoContentGenerator;

    /**
     * @var \App\Service\Api\Anthropic\ImageAnalyzer The image analyzer service.
     */
    private ImageAnalyzer $imageAnalyzer;

    /**
     * @var \App\Service\Api\Anthropic\CommentAnalyzer The comment analyzer service.
     */
    private CommentAnalyzer $commentAnalyzer;

    /**
     * @var \App\Service\Api\Anthropic\ArticleTagsGenerator The article tags generator service.
     */
    private ArticleTagsGenerator $articleTagsGenerator;

    /**
     * @var \App\Service\Api\Anthropic\TextSummaryGenerator The text summary generator service.
     */
    private TextSummaryGenerator $textSummaryGenerator;

    /**
     * @var \App\Service\Api\Anthropic\TranslationGenerator The text summary generator service.
     */
    private TranslationGenerator $translationGenerator;

    /**
     * AnthropicApiService constructor.
     *
     * Initializes the service with necessary dependencies and configurations.
     */
    public function __construct()
    {
        $apiKey = SettingsManager::read('Anthropic.apiKey');
        parent::__construct(new Client(), $apiKey, self::API_URL, self::API_VERSION);

        $this->aipromptsTable = TableRegistry::getTableLocator()->get('Aiprompts');
        $this->seoContentGenerator = new SeoContentGenerator($this, $this->aipromptsTable);
        $this->imageAnalyzer = new ImageAnalyzer($this, $this->aipromptsTable);
        $this->commentAnalyzer = new CommentAnalyzer($this, $this->aipromptsTable);
        $this->articleTagsGenerator = new ArticleTagsGenerator($this, $this->aipromptsTable);
        $this->textSummaryGenerator = new TextSummaryGenerator($this, $this->aipromptsTable);
        $this->translationGenerator = new TranslationGenerator($this, $this->aipromptsTable);
    }

    /**
     * Sends a POST request to the API with the given payload and rate limiting.
     *
     * @param array $payload The data to be sent in the request body.
     * @param int $timeOut Request timeout in seconds.
     * @return \Cake\Http\Client\Response The response from the API.
     * @throws \Cake\Http\Exception\ServiceUnavailableException If the API request fails or rate limit exceeded.
     */
    public function sendRequest(array $payload, int $timeOut = 30): Response
    {
        // Enforce rate limiting before making the API call
        $rateLimitService = new \App\Service\Api\RateLimitService();
        if (!$rateLimitService->enforceLimit('anthropic')) {
            throw new \Cake\Http\Exception\ServiceUnavailableException('Hourly rate limit exceeded for Anthropic API');
        }

        return parent::sendRequest($payload, $timeOut);
    }

    /**
     * Gets the headers for the API request.
     *
     * @return array An associative array of headers.
     */
    protected function getHeaders(): array
    {
        return [
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Generates SEO content for a tag.
     *
     * @param string $tagTitle The title of the tag.
     * @param string $tagDescription The description of the tag.
     * @return array The generated SEO content.
     */
    public function generateTagSeo(string $tagTitle, string $tagDescription): array
    {
        return $this->seoContentGenerator->generateTagSeo($tagTitle, $tagDescription);
    }

    /**
     * Generates SEO content for an article.
     *
     * @param string $title The title of the article.
     * @param string $body The body content of the article.
     * @return array The generated SEO content.
     */
    public function generateArticleSeo(string $title, string $body): array
    {
        return $this->seoContentGenerator->generateArticleSeo($title, $body);
    }

    /**
     * Generates SEO content for an image gallery.
     *
     * @param string $name The name of the gallery.
     * @param string $context Additional context about the gallery content and images.
     * @return array The generated SEO content.
     */
    public function generateGallerySeo(string $name, string $context): array
    {
        return $this->seoContentGenerator->generateGallerySeo($name, $context);
    }

    /**
     * Generates tags for an article.
     *
     * @param array $allTags All available tags.
     * @param string $title The title of the article.
     * @param string $body The body content of the article.
     * @return array The generated article tags.
     */
    public function generateArticleTags(array $allTags, string $title, string $body): array
    {
        return $this->articleTagsGenerator->generateArticleTags($allTags, $title, $body);
    }

    /**
     * Analyzes an image.
     *
     * @param string $imagePath The path to the image file.
     * @return array The analysis results.
     */
    public function analyzeImage(string $imagePath): array
    {
        return $this->imageAnalyzer->analyze($imagePath);
    }

    /**
     * Analyzes a comment.
     *
     * @param string $comment The comment text to analyze.
     * @return array The analysis results.
     */
    public function analyzeComment(string $comment): array
    {
        return $this->commentAnalyzer->analyze($comment);
    }

    /**
     * Generates a summary for a given text.
     *
     * @param string $context The context of the text (e.g., 'article', 'page', 'report').
     * @param string $text The text to summarize.
     * @return array The generated summary.
     */
    public function generateTextSummary(string $context, string $text): array
    {
        return $this->textSummaryGenerator->generateTextSummary($context, $text);
    }

    /**
     * Translates an array of strings from one locale to another.
     *
     * This method utilizes the TranslationGenerator service to perform translations
     * of the provided strings from the specified source locale to the target locale.
     *
     * @param array $strings The array of strings to be translated.
     * @param string $localeFrom The locale code of the source language (e.g., 'en_US').
     * @param string $localeTo The locale code of the target language (e.g., 'fr_FR').
     * @return array The translated strings.
     */
    public function translateStrings(array $strings, string $localeFrom, string $localeTo): array
    {
        return $this->translationGenerator->generateTranslation($strings, $localeFrom, $localeTo);
    }

    /**
     * Parses the response from the API.
     *
     * @param \Cake\Http\Client\Response $response The HTTP response from the API.
     * @return array The parsed response data.
     */
    public function parseResponse(Response $response): array
    {
        $responseData = $response->getJson();

        return json_decode($responseData['content'][0]['text'], true);
    }



    /**
     * Generate the next question for Akinator-style quiz
     * 
     * @param array $context Current quiz context and answers
     * @param array $remainingProducts Products that still match criteria
     * @return array|null Question data or null if should terminate
     */
    public function generateNextQuestion(array $context, array $remainingProducts): ?array
    {
        try {
            if (empty($remainingProducts) || count($remainingProducts) <= 3) {
                return null; // Should terminate and show results
            }

            $prompt = $this->buildQuestionPrompt($context, $remainingProducts);
            $payload = [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 1000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ];
            
            $response = $this->sendRequest($payload);
            return $this->parseQuestionResponse($response);

        } catch (\Exception $e) {
            $this->log('Anthropic API error generating question: ' . $e->getMessage(), 'error');
            return $this->generateFallbackQuestion($context, $remainingProducts);
        }
    }

    /**
     * Generate product recommendations with AI explanations
     * 
     * @param array $quizAnswers User's quiz answers
     * @param array $matchedProducts Products that match criteria
     * @return array Recommendations with AI explanations
     */
    public function generateRecommendations(array $quizAnswers, array $matchedProducts): array
    {
        try {
            $prompt = $this->buildRecommendationPrompt($quizAnswers, $matchedProducts);
            $payload = [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 1500,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ];
            
            $response = $this->sendRequest($payload);
            return $this->parseRecommendationResponse($response, $matchedProducts);

        } catch (\Exception $e) {
            $this->log('Anthropic API error generating recommendations: ' . $e->getMessage(), 'error');
            return $this->generateFallbackRecommendations($matchedProducts);
        }
    }

    /**
     * Analyze user responses to determine product filtering criteria
     * 
     * @param array $answers Quiz answers
     * @return array Filtering criteria
     */
    public function analyzeAnswers(array $answers): array
    {
        try {
            $prompt = $this->buildAnalysisPrompt($answers);
            $payload = [
                'model' => 'claude-3-5-sonnet-20241022',
                'max_tokens' => 500,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ];
            
            $response = $this->sendRequest($payload);
            return $this->parseAnalysisResponse($response);

        } catch (\Exception $e) {
            $this->log('Anthropic API error analyzing answers: ' . $e->getMessage(), 'error');
            return $this->fallbackAnalyzeAnswers($answers);
        }
    }

    /**
     * Build prompt for question generation
     * 
     * @param array $context Quiz context
     * @param array $products Remaining products
     * @return string Prompt for AI
     */
    private function buildQuestionPrompt(array $context, array $products): string
    {
        $questionCount = count($context['answers'] ?? []);
        $deviceTypes = array_unique(array_column($products, 'device_category'));
        $manufacturers = array_unique(array_column($products, 'manufacturer'));
        $portTypes = array_unique(array_column($products, 'port_family'));

        $previousAnswers = '';
        if (!empty($context['answers'])) {
            $previousAnswers = "Previous answers: " . json_encode($context['answers']) . "\n\n";
        }

        return "You are helping users find the perfect adapter/charger through an Akinator-style quiz. 

{$previousAnswers}Current situation:
- Question #{$questionCount}
- " . count($products) . " products remaining
- Device types available: " . implode(', ', $deviceTypes) . "
- Manufacturers available: " . implode(', ', $manufacturers) . "
- Port types available: " . implode(', ', $portTypes) . "

Generate the next MULTIPLE CHOICE question with 3-5 options that will best narrow down the remaining products. The question should be:
1. Clear and easy to understand
2. About device compatibility, usage patterns, or technical specifications
3. Designed to efficiently categorize products
4. Natural and conversational
5. Have options that cover the main variations in the remaining products

Respond with JSON in this format:
{
  \"question\": \"What type of device are you looking to charge?\",
  \"type\": \"choice\",
  \"options\": [
    {\"id\": \"laptop\", \"label\": \"Laptop/Computer\"},
    {\"id\": \"phone\", \"label\": \"Phone/Mobile\"},
    {\"id\": \"tablet\", \"label\": \"Tablet\"},
    {\"id\": \"other\", \"label\": \"Other Device\"}
  ],
  \"category\": \"device_type\",
  \"targeting\": \"narrow_by_device_category\"
}";
    }

    /**
     * Build prompt for recommendation generation
     * 
     * @param array $answers User answers
     * @param array $products Matched products
     * @return string Prompt for AI
     */
    private function buildRecommendationPrompt(array $answers, array $products): string
    {
        $answersText = json_encode($answers, JSON_PRETTY_PRINT);
        $productsText = '';
        
        foreach (array_slice($products, 0, 5) as $product) {
            $price = isset($product['price']) ? '$' . number_format($product['price'], 2) : 'N/A';
            $productsText .= "- {$product['title']} by {$product['manufacturer']} ({$price}, {$product['port_family']})\n";
        }

        return "Based on this quiz conversation and user answers, provide personalized recommendations.

User's Quiz Answers:
{$answersText}

Top Matching Products:
{$productsText}

For each product, provide:
1. A personalized explanation of why it matches their needs
2. Key benefits specific to their situation
3. Any compatibility considerations

Respond with JSON array of recommendations:
[
  {
    \"product_id\": 1,
    \"confidence_score\": 0.95,
    \"explanation\": \"This MacBook charger is perfect for you because...\",
    \"key_benefits\": [\"Fast charging\", \"Portable design\", \"Apple certified\"],
    \"compatibility_notes\": \"Works with MacBook Air 2018 and newer\"
  }
]

Be conversational, helpful, and specific to their answers.";
    }

    /**
     * Build prompt for answer analysis
     * 
     * @param array $answers Quiz answers
     * @return string Prompt for AI
     */
    private function buildAnalysisPrompt(array $answers): string
    {
        $answersText = json_encode($answers, JSON_PRETTY_PRINT);

        return "Analyze these quiz answers to determine product filtering criteria:

Quiz Answers:
{$answersText}

Based on the answers, determine the most likely:
1. Device type/category
2. Manufacturer preference
3. Port type requirements
4. Usage patterns
5. Budget considerations

Respond with JSON:
{
  \"device_category\": \"laptop\",
  \"manufacturer\": \"apple\",
  \"port_family\": \"usbc\",
  \"form_factor\": \"compact\",
  \"usage_type\": \"mobile\",
  \"budget_range\": [30, 80],
  \"confidence_factors\": [\"strong_brand_preference\", \"port_type_certain\"]
}";
    }

    /**
     * Parse question response from AI
     * 
     * @param \Cake\Http\Client\Response $response API response
     * @return array|null Parsed question
     */
    private function parseQuestionResponse(Response $response): ?array
    {
        try {
            $responseData = $response->getJson();
            $content = $responseData['content'][0]['text'] ?? '';
            
            // Extract JSON from response
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $questionData = json_decode($matches[0], true);
                
                if ($questionData && isset($questionData['question'])) {
                    $options = $questionData['options'] ?? [
                        ['id' => 'yes', 'label' => 'Yes'],
                        ['id' => 'no', 'label' => 'No']
                    ];
                    
                    return [
                        'id' => \Cake\Utility\Text::uuid(),
                        'text' => $questionData['question'],
                        'type' => $questionData['type'] ?? 'choice',
                        'category' => $questionData['category'] ?? 'general',
                        'options' => $options
                    ];
                }
            }

            return null;

        } catch (\Exception $e) {
            $this->log('Error parsing question response: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Parse recommendation response from AI
     * 
     * @param \Cake\Http\Client\Response $response API response
     * @param array $products Original products
     * @return array Parsed recommendations
     */
    private function parseRecommendationResponse(Response $response, array $products): array
    {
        try {
            $responseData = $response->getJson();
            $content = $responseData['content'][0]['text'] ?? '';
            
            // Extract JSON from response
            if (preg_match('/\[.*\]/s', $content, $matches)) {
                $recommendations = json_decode($matches[0], true);
                
                if (is_array($recommendations)) {
                    return array_map(function($rec) use ($products) {
                        $product = null;
                        if (isset($rec['product_id'])) {
                            $product = array_filter($products, function($p) use ($rec) {
                                return $p['id'] == $rec['product_id'];
                            });
                            $product = reset($product) ?: null;
                        }
                        
                        return [
                            'product' => $product,
                            'confidence_score' => $rec['confidence_score'] ?? 0.8,
                            'explanation' => $rec['explanation'] ?? '',
                            'key_benefits' => $rec['key_benefits'] ?? [],
                            'compatibility_notes' => $rec['compatibility_notes'] ?? ''
                        ];
                    }, $recommendations);
                }
            }

            return $this->generateFallbackRecommendations($products);

        } catch (\Exception $e) {
            $this->log('Error parsing recommendation response: ' . $e->getMessage(), 'error');
            return $this->generateFallbackRecommendations($products);
        }
    }

    /**
     * Parse analysis response from AI
     * 
     * @param \Cake\Http\Client\Response $response API response
     * @return array Parsed criteria
     */
    private function parseAnalysisResponse(Response $response): array
    {
        try {
            $responseData = $response->getJson();
            $content = $responseData['content'][0]['text'] ?? '';
            
            // Extract JSON from response
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $criteria = json_decode($matches[0], true);
                
                if (is_array($criteria)) {
                    return $criteria;
                }
            }

            return [];

        } catch (\Exception $e) {
            $this->log('Error parsing analysis response: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Generate fallback question when AI fails
     * 
     * @param array $context Quiz context
     * @param array $products Remaining products
     * @return array|null Fallback question
     */
    private function generateFallbackQuestion(array $context, array $products): ?array
    {
        $questionCount = count($context['answers'] ?? []);
        
        // Predefined fallback questions
        $fallbackQuestions = [
            [
                'text' => 'Do you need this adapter primarily for a laptop computer?',
                'category' => 'device_type'
            ],
            [
                'text' => 'Is your device made by Apple?',
                'category' => 'manufacturer'
            ],
            [
                'text' => 'Does your device use a USB-C port for charging?',
                'category' => 'port_type'
            ],
            [
                'text' => 'Do you need fast charging capabilities?',
                'category' => 'features'
            ],
            [
                'text' => 'Is portability important to you (small, lightweight adapter)?',
                'category' => 'form_factor'
            ]
        ];

        if ($questionCount < count($fallbackQuestions)) {
            $question = $fallbackQuestions[$questionCount];
            return [
                'id' => \Cake\Utility\Text::uuid(),
                'text' => $question['text'],
                'type' => 'binary',
                'category' => $question['category'],
                'options' => [
                    ['id' => 'yes', 'label' => 'Yes'],
                    ['id' => 'no', 'label' => 'No']
                ]
            ];
        }

        return null;
    }

    /**
     * Generate fallback recommendations when AI fails
     * 
     * @param array $products Products to recommend
     * @return array Fallback recommendations
     */
    private function generateFallbackRecommendations(array $products): array
    {
        return array_slice(array_map(function($product) {
            return [
                'product' => $product,
                'confidence_score' => 0.7,
                'explanation' => "This {$product['manufacturer']} adapter matches your requirements and is highly rated by users.",
                'key_benefits' => ['Compatible', 'Reliable', 'Good value'],
                'compatibility_notes' => 'Please check product specifications for exact compatibility.'
            ];
        }, $products), 0, 3);
    }

    /**
     * Fallback analysis when AI fails
     * 
     * @param array $answers Quiz answers
     * @return array Basic criteria extraction
     */
    private function fallbackAnalyzeAnswers(array $answers): array
    {
        $criteria = [];

        // Simple rule-based analysis
        foreach ($answers as $questionId => $answer) {
            if (strpos($questionId, 'laptop') !== false && $answer === 'yes') {
                $criteria['device_category'] = 'laptop';
            }
            if (strpos($questionId, 'apple') !== false && $answer === 'yes') {
                $criteria['manufacturer'] = 'apple';
            }
            if (strpos($questionId, 'usbc') !== false && $answer === 'yes') {
                $criteria['port_family'] = 'usbc';
            }
        }

        return $criteria;
    }

    /**
     * Records metrics for a specific AI task.
     *
     * @param string $taskType The type of the AI task.
     * @param float $startTime The start time of the task.
     * @param array $payload The payload data for the task.
     * @param bool $success Whether the task was successful.
     * @param string|null $error An optional error message.
     * @return void
     */
    private function recordMetrics(string $taskType, float $startTime, array $payload, bool $success, ?string $error = null): void
{
    if (!SettingsManager::read('AI.enableMetrics', true)) {
        return;
    }
    
    $executionTime = (microtime(true) - $startTime) * 1000;
    $cost = $this->calculateCost($payload);
    
    $metric = $this->aiMetricsTable->newEntity([
        'task_type' => $taskType,
        'execution_time_ms' => (int)$executionTime,
        'tokens_used' => $payload['max_tokens'] ?? null,
        'model_used' => $payload['model'] ?? null,
        'success' => $success,
        'error_message' => $error,
        'cost_usd' => $cost,
    ]);
    
    $this->aiMetricsTable->save($metric);
}
}
