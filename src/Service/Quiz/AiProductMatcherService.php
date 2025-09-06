<?php
declare(strict_types=1);

namespace App\Service\Quiz;

use App\Service\Api\Anthropic\AnthropicApiService;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Log\LogTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Hash;
use Cake\Utility\Text;

/**
 * AI Product Matcher Service
 * 
 * Matches quiz answers to products using hybrid AI and rule-based scoring
 */
class AiProductMatcherService
{
    use LogTrait;
    use LocatorAwareTrait;

    /**
     * AI API service for advanced scoring
     */
    private $aiService;

    /**
     * Products table for data access
     */
    private $productsTable;

    /**
     * Configuration settings
     */
    private array $config;

    /**
     * Constructor
     * 
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        $this->config = $config + Configure::read('Quiz') + [
            'ai_enabled' => true,
            'confidence_threshold' => 0.6,
            'max_results' => 10,
            'cache_ttl' => 900,
            'fallback_enabled' => true,
        ];

        $this->productsTable = $this->getTableLocator()->get('Products');
        
        // Initialize AI service if available and enabled
        if ($this->config['ai_enabled']) {
            try {
                $this->aiService = new AnthropicApiService();
                $this->log('AI service initialized successfully', 'info');
            } catch (\Exception $e) {
                $this->log('AI service initialization failed: ' . $e->getMessage(), 'warning');
                $this->aiService = null;
            }
        }
    }

    /**
     * Match products based on quiz answers
     * 
     * @param array $answers Normalized quiz answers
     * @param array $constraints Additional filtering constraints
     * @return array Matched products with scores and explanations
     */
    public function match(array $answers, array $constraints = []): array
    {
        $startTime = microtime(true);
        
        try {
            // Generate cache key
            $cacheKey = $this->generateCacheKey($answers, $constraints);
            
            // Check cache first
            $cached = Cache::read($cacheKey, 'quiz');
            if ($cached !== false) {
                $this->log("Quiz match served from cache: {$cacheKey}", 'info');
                return $cached;
            }

            // Get candidate products using rule-based filtering
            $candidates = $this->getCandidateProducts($answers, $constraints);
            
            if (empty($candidates)) {
                $result = [
                    'products' => [],
                    'total_matches' => 0,
                    'overall_confidence' => 0.0,
                    'processing_time' => microtime(true) - $startTime,
                    'method' => 'rule_based',
                ];
                
                Cache::write($cacheKey, $result, 'quiz');
                return $result;
            }

            // Score products using hybrid approach
            $scoredProducts = $this->scoreProducts($candidates, $answers);
            
            // Filter by confidence threshold
            $scoredProducts = array_filter($scoredProducts, function($product) {
                return $product['confidence_score'] >= $this->config['confidence_threshold'];
            });

            // Sort by confidence score
            usort($scoredProducts, function($a, $b) {
                return $b['confidence_score'] <=> $a['confidence_score'];
            });

            // Limit results
            $scoredProducts = array_slice($scoredProducts, 0, $this->config['max_results']);

            // Calculate overall confidence
            $overallConfidence = $this->calculateOverallConfidence($scoredProducts);

            $result = [
                'products' => $scoredProducts,
                'total_matches' => count($scoredProducts),
                'overall_confidence' => $overallConfidence,
                'processing_time' => microtime(true) - $startTime,
                'method' => $this->aiService ? 'hybrid_ai' : 'rule_based',
            ];

            // Cache result
            Cache::write($cacheKey, $result, 'quiz');
            
            $this->log(sprintf(
                'Quiz match completed: %d matches, %.3f confidence, %.3fs',
                count($scoredProducts),
                $overallConfidence,
                $result['processing_time']
            ), 'info');

            return $result;

        } catch (\Exception $e) {
            $this->log('Error in product matching: ' . $e->getMessage(), 'error');
            
            if ($this->config['fallback_enabled']) {
                return $this->fallbackMatch($answers, $constraints);
            }
            
            throw $e;
        }
    }

    /**
     * Generate explanation for why a product was recommended
     * 
     * @param array $answers Quiz answers
     * @param array $product Product data
     * @return string Explanation text
     */
    public function explain(array $answers, array $product): string
    {
        try {
            if ($this->aiService) {
                return $this->generateAiExplanation($answers, $product);
            }
        } catch (\Exception $e) {
            $this->log('AI explanation failed: ' . $e->getMessage(), 'warning');
        }

        return $this->generateRuleBasedExplanation($answers, $product);
    }

    /**
     * Get candidate products using rule-based filtering
     * 
     * @param array $answers Quiz answers
     * @param array $constraints Additional constraints
     * @return array Candidate products
     */
    private function getCandidateProducts(array $answers, array $constraints): array
    {
        $query = $this->productsTable->findForQuiz($answers, $constraints);
        
        // Limit initial candidates to prevent performance issues
        $query->limit($constraints['candidate_limit'] ?? 50);
        
        return $query->toArray();
    }

    /**
     * Score products using hybrid approach
     * 
     * @param array $candidates Candidate products
     * @param array $answers Quiz answers
     * @return array Scored products with confidence and explanation
     */
    private function scoreProducts(array $candidates, array $answers): array
    {
        $scoredProducts = [];

        foreach ($candidates as $product) {
            // Rule-based scoring (always available)
            $ruleScore = $this->productsTable->calculateProductScore($product, $answers);
            
            // AI-enhanced scoring (if available)
            $aiScore = null;
            if ($this->aiService) {
                try {
                    $aiScore = $this->getAiScore($product, $answers);
                } catch (\Exception $e) {
                    $this->log('AI scoring failed for product ' . $product->id . ': ' . $e->getMessage(), 'warning');
                }
            }

            // Combine scores (weighted average)
            $finalScore = $aiScore ? 
                ($ruleScore * 0.4 + $aiScore * 0.6) : 
                $ruleScore;

            // Generate explanation
            $explanation = $this->explain($answers, $product->toArray());

            $scoredProducts[] = [
                'product' => $product,
                'confidence_score' => $finalScore,
                'rule_based_score' => $ruleScore,
                'ai_score' => $aiScore,
                'explanation' => $explanation,
                'match_reasons' => $this->getMatchReasons($product, $answers),
            ];
        }

        return $scoredProducts;
    }

    /**
     * Get AI-based confidence score for a product
     * 
     * @param \App\Model\Entity\Product $product
     * @param array $answers
     * @return float|null Score between 0.0 and 1.0, or null if AI unavailable
     */
    private function getAiScore($product, array $answers): ?float
    {
        if (!$this->aiService) {
            return null;
        }

        // Create a cache key for this specific AI scoring request
        $aiCacheKey = 'ai_score_' . md5($product->id . serialize($answers));
        $cached = Cache::read($aiCacheKey, 'quiz');
        
        if ($cached !== false) {
            return $cached;
        }

        try {
            $prompt = $this->buildAiScoringPrompt($product, $answers);
            $response = $this->aiService->generateResponse($prompt, [
                'max_tokens' => 100,
                'temperature' => 0.3,
            ]);

            $score = $this->parseAiScoreResponse($response);
            
            // Cache the AI score
            Cache::write($aiCacheKey, $score, 'quiz');
            
            return $score;

        } catch (\Exception $e) {
            $this->log('AI scoring request failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Build AI prompt for product scoring
     * 
     * @param \App\Model\Entity\Product $product
     * @param array $answers
     * @return string AI prompt
     */
    private function buildAiScoringPrompt($product, array $answers): string
    {
        $userProfile = $this->buildUserProfile($answers);
        $productSummary = $this->buildProductSummary($product);

        return "You are an expert adapter and charger recommendation system. " .
               "Rate how well this product matches the user's needs on a scale of 0.0 to 1.0.\n\n" .
               "User Profile:\n{$userProfile}\n\n" .
               "Product:\n{$productSummary}\n\n" .
               "Consider: device compatibility, port types, power requirements, certification, budget, and brand preferences.\n" .
               "Respond with only a decimal score (e.g., 0.85):";
    }

    /**
     * Parse AI response to extract confidence score
     * 
     * @param string $response AI response
     * @return float Confidence score
     */
    private function parseAiScoreResponse(string $response): float
    {
        // Extract decimal number from response
        if (preg_match('/(\d+\.?\d*)/', $response, $matches)) {
            $score = (float)$matches[1];
            
            // Normalize if needed
            if ($score > 1.0) {
                $score = $score / 100; // Convert percentage to decimal
            }
            
            return max(0.0, min(1.0, $score));
        }

        return 0.0;
    }

    /**
     * Build user profile from quiz answers
     * 
     * @param array $answers
     * @return string User profile description
     */
    private function buildUserProfile(array $answers): string
    {
        $profile = [];
        
        if (!empty($answers['device_type'])) {
            $profile[] = "Device: " . $answers['device_type'];
        }
        
        if (!empty($answers['manufacturer'])) {
            $profile[] = "Preferred brand: " . $answers['manufacturer'];
        }
        
        if (!empty($answers['port_type'])) {
            $profile[] = "Port type: " . $answers['port_type'];
        }
        
        if (!empty($answers['budget_range'])) {
            if (is_array($answers['budget_range'])) {
                $min = $answers['budget_range']['min'] ?? 0;
                $max = $answers['budget_range']['max'] ?? 999;
                $profile[] = "Budget: $${min} - $${max}";
            } else {
                $profile[] = "Budget: " . $answers['budget_range'];
            }
        }
        
        if (!empty($answers['certification_required'])) {
            $profile[] = "Certification required: " . $answers['certification_required'];
        }

        return implode("\n", $profile);
    }

    /**
     * Build product summary for AI evaluation
     * 
     * @param \App\Model\Entity\Product $product
     * @return string Product summary
     */
    private function buildProductSummary($product): string
    {
        $summary = [];
        
        $summary[] = "Name: " . ($product->title ?? 'Unknown');
        
        if ($product->manufacturer) {
            $summary[] = "Brand: " . $product->manufacturer;
        }
        
        if ($product->price) {
            $summary[] = "Price: $" . number_format($product->price, 2);
        }
        
        if ($product->device_category) {
            $summary[] = "Category: " . $product->device_category;
        }
        
        if ($product->port_family) {
            $summary[] = "Port type: " . $product->port_family;
        }
        
        if ($product->is_certified) {
            $summary[] = "Certified: Yes";
            if ($product->certifying_organization) {
                $summary[] = "Certified by: " . $product->certifying_organization;
            }
        }
        
        if ($product->numeric_rating) {
            $summary[] = "Rating: " . $product->numeric_rating . "/5";
        }
        
        if ($product->description) {
            $summary[] = "Description: " . Text::truncate($product->description, 200);
        }

        return implode("\n", $summary);
    }

    /**
     * Generate AI-powered explanation
     * 
     * @param array $answers
     * @param array $product
     * @return string
     */
    private function generateAiExplanation(array $answers, array $product): string
    {
        try {
            $prompt = "Explain in 1-2 sentences why this product is recommended for this user:\n\n" .
                      "User needs: " . $this->buildUserProfile($answers) . "\n" .
                      "Product: " . $this->buildProductSummary((object)$product) . "\n\n" .
                      "Focus on the key matching factors:";

            $response = $this->aiService->generateResponse($prompt, [
                'max_tokens' => 150,
                'temperature' => 0.5,
            ]);

            return trim($response);

        } catch (\Exception $e) {
            return $this->generateRuleBasedExplanation($answers, $product);
        }
    }

    /**
     * Generate rule-based explanation
     * 
     * @param array $answers
     * @param array $product
     * @return string
     */
    private function generateRuleBasedExplanation(array $answers, array $product): string
    {
        $reasons = [];

        // Device compatibility
        if (!empty($answers['device_type']) && !empty($product['device_category'])) {
            if (stripos($product['device_category'], $answers['device_type']) !== false) {
                $reasons[] = __('compatible with your {0}', $answers['device_type']);
            }
        }

        // Brand match
        if (!empty($answers['manufacturer']) && !empty($product['manufacturer'])) {
            if (stripos($product['manufacturer'], $answers['manufacturer']) !== false) {
                $reasons[] = __('made by {0}', $product['manufacturer']);
            }
        }

        // Port compatibility
        if (!empty($answers['port_type']) && !empty($product['port_family'])) {
            if (stripos($product['port_family'], $answers['port_type']) !== false) {
                $reasons[] = __('has the {0} port you need', $product['port_family']);
            }
        }

        // Budget fit
        if (!empty($answers['budget_range']) && !empty($product['price'])) {
            if (is_array($answers['budget_range'])) {
                $min = $answers['budget_range']['min'] ?? 0;
                $max = $answers['budget_range']['max'] ?? 9999;
                if ($product['price'] >= $min && $product['price'] <= $max) {
                    $reasons[] = __('within your budget');
                }
            }
        }

        // Certification
        if (!empty($answers['certification_required']) && $answers['certification_required'] === 'yes') {
            if (!empty($product['is_certified']) && $product['is_certified']) {
                $reasons[] = __('professionally certified');
            }
        }

        if (empty($reasons)) {
            return __('This product matches your general requirements.');
        }

        return __('Recommended because it is {0}.', implode(', ', $reasons));
    }

    /**
     * Get specific match reasons
     * 
     * @param \App\Model\Entity\Product $product
     * @param array $answers
     * @return array
     */
    private function getMatchReasons($product, array $answers): array
    {
        $reasons = [];

        if (!empty($answers['device_type'])) {
            $deviceType = strtolower($answers['device_type']);
            if (stripos($product->device_category ?? '', $deviceType) !== false ||
                stripos($product->title ?? '', $deviceType) !== false) {
                $reasons[] = 'device_compatibility';
            }
        }

        if (!empty($answers['manufacturer'])) {
            if (stripos($product->manufacturer ?? '', $answers['manufacturer']) !== false) {
                $reasons[] = 'brand_match';
            }
        }

        if (!empty($answers['port_type'])) {
            if (stripos($product->port_family ?? '', $answers['port_type']) !== false) {
                $reasons[] = 'port_compatibility';
            }
        }

        if ($product->is_certified) {
            $reasons[] = 'certified';
        }

        if ($product->numeric_rating >= 4.0) {
            $reasons[] = 'high_rating';
        }

        return $reasons;
    }

    /**
     * Calculate overall confidence from scored products
     * 
     * @param array $scoredProducts
     * @return float
     */
    private function calculateOverallConfidence(array $scoredProducts): float
    {
        if (empty($scoredProducts)) {
            return 0.0;
        }

        $totalScore = 0.0;
        foreach ($scoredProducts as $product) {
            $totalScore += $product['confidence_score'];
        }

        return $totalScore / count($scoredProducts);
    }

    /**
     * Generate cache key for matching request
     * 
     * @param array $answers
     * @param array $constraints
     * @return string
     */
    private function generateCacheKey(array $answers, array $constraints): string
    {
        $data = [
            'answers' => $answers,
            'constraints' => $constraints,
            'version' => '1.0',
        ];
        
        return 'quiz_match_' . md5(serialize($data));
    }

    /**
     * Fallback matching when AI is unavailable
     * 
     * @param array $answers
     * @param array $constraints
     * @return array
     */
    private function fallbackMatch(array $answers, array $constraints): array
    {
        try {
            $candidates = $this->getCandidateProducts($answers, $constraints);
            $scores = $this->productsTable->scoreWithAi($candidates, $answers);
            
            $products = [];
            foreach ($candidates as $product) {
                $score = $scores[$product->id] ?? 0.0;
                if ($score >= $this->config['confidence_threshold']) {
                    $products[] = [
                        'product' => $product,
                        'confidence_score' => $score,
                        'rule_based_score' => $score,
                        'ai_score' => null,
                        'explanation' => $this->generateRuleBasedExplanation($answers, $product->toArray()),
                        'match_reasons' => $this->getMatchReasons($product, $answers),
                    ];
                }
            }

            // Sort by score
            usort($products, function($a, $b) {
                return $b['confidence_score'] <=> $a['confidence_score'];
            });

            $products = array_slice($products, 0, $this->config['max_results']);

            return [
                'products' => $products,
                'total_matches' => count($products),
                'overall_confidence' => $this->calculateOverallConfidence($products),
                'processing_time' => 0.0,
                'method' => 'fallback',
            ];

        } catch (\Exception $e) {
            $this->log('Fallback matching also failed: ' . $e->getMessage(), 'error');
            
            return [
                'products' => [],
                'total_matches' => 0,
                'overall_confidence' => 0.0,
                'processing_time' => 0.0,
                'method' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }
}
