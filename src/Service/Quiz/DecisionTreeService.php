<?php
declare(strict_types=1);

namespace App\Service\Quiz;

use App\Service\Quiz\AiProductMatcherService;
use App\Service\Api\Anthropic\AnthropicApiService;
use Cake\ORM\TableRegistry;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Log\LogTrait;
use Cake\Utility\Hash;
use Cake\Utility\Text;

/**
 * Decision Tree Service
 * 
 * Manages Akinator-style decision tree navigation for product recommendations
 */
class DecisionTreeService
{
    use LogTrait;

    /**
     * Decision tree structure
     */
    private array $tree;

    /**
     * Configuration settings
     */
    private array $config;

    /**
     * Product matcher service
     */
    private $productMatcher;

    /**
     * AI service for intelligent question generation
     */
    private $aiService;

    /**
     * Constructor
     * 
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        $this->config = $config + Configure::read('Quiz.akinator') + [
            'max_questions' => 15,
            'confidence_threshold' => 0.85,
            'min_products_threshold' => 3,
            'cache_ttl' => 3600,
        ];

        $this->loadDecisionTree();
        $this->productMatcher = new AiProductMatcherService();
        
        // Initialize AI service for smart question generation
        try {
            $this->aiService = new AnthropicApiService();
            $this->log('AI service initialized for intelligent question generation', 'info');
        } catch (\Exception $e) {
            $this->log('AI service initialization failed, using fallback questions: ' . $e->getMessage(), 'warning');
            $this->aiService = null;
        }
    }

    /**
     * Start a new Akinator session
     * 
     * @param array $context Optional context information
     * @return array Initial question and session data
     */
    public function start(array $context = []): array
    {
        $sessionId = Text::uuid();
        
        // Initialize session state
        $state = [
            'session_id' => $sessionId,
            'current_node' => 'root',
            'answers' => [],
            'visited_nodes' => ['root'],
            'question_count' => 0,
            'confidence' => 0.0,
            'context' => $context,
            'started_at' => time(),
        ];

        // Get first question
        $firstQuestion = $this->getQuestionForNode('root', $state);
        
        if (!$firstQuestion) {
            throw new \RuntimeException('Unable to load first question from decision tree');
        }

        // Store session state
        $this->storeSessionState($sessionId, $state);

        $this->log("Akinator session started: {$sessionId}", 'info');

        return [
            'session_id' => $sessionId,
            'question' => $firstQuestion,
            'progress' => [
                'current' => 1,
                'total' => $this->config['max_questions'],
                'percentage' => 0,
            ],
            'confidence' => 0.0,
        ];
    }

    /**
     * Process next question based on answer
     * 
     * @param array $state Current session state
     * @param string $answer User's answer
     * @return array Next question or terminal result
     */
    public function next(array $state, string $answer): array
    {
        // Validate session
        if (empty($state['session_id'])) {
            throw new \InvalidArgumentException('Invalid session state');
        }

        // Initialize missing state fields
        if (!isset($state['question_count'])) {
            $state['question_count'] = 0;
        }
        if (!isset($state['confidence'])) {
            $state['confidence'] = 0.0;
        }
        if (!isset($state['visited_nodes'])) {
            $state['visited_nodes'] = [];
        }
        if (!isset($state['answers'])) {
            $state['answers'] = [];
        }

        // Update state with answer
        $state['answers'][$state['current_node']] = $answer;
        $state['question_count']++;
        
        // Determine next node
        $nextNode = $this->getNextNode($state['current_node'], $answer);
        
        // Check if we should terminate
        if ($this->shouldTerminate($state, $nextNode)) {
            return $this->generateResult($state);
        }

        // Update state for next question
        $state['current_node'] = $nextNode;
        $state['visited_nodes'][] = $nextNode;
        
        // Calculate current confidence
        $state['confidence'] = $this->calculateConfidence($state);

        // Get next question
        $nextQuestion = $this->getQuestionForNode($nextNode, $state);
        
        if (!$nextQuestion) {
            // No more questions, generate result
            return $this->generateResult($state);
        }

        // Store updated state
        $this->storeSessionState($state['session_id'], $state);

        return [
            'session_id' => $state['session_id'],
            'question' => $nextQuestion,
            'progress' => [
                'current' => $state['question_count'] + 1,
                'total' => $this->config['max_questions'],
                'percentage' => round(($state['question_count'] / $this->config['max_questions']) * 100),
            ],
            'confidence' => $state['confidence'],
        ];
    }

    /**
     * Check if we should terminate the quiz
     * 
     * @param array $state Current state
     * @param string|null $nextNode Next node ID
     * @return bool True if should terminate
     */
    public function isTerminal(array $state, string $nextNode = null): bool
    {
        return $this->shouldTerminate($state, $nextNode);
    }

    /**
     * Serialize state for persistence
     * 
     * @param array $state State array
     * @return string Serialized state
     */
    public function serializeState(array $state): string
    {
        return base64_encode(json_encode($state));
    }

    /**
     * Deserialize state from persistence
     * 
     * @param string $serialized Serialized state
     * @return array State array
     */
    public function deserializeState(string $serialized): array
    {
        $decoded = base64_decode($serialized);
        $state = json_decode($decoded, true);
        
        if (!is_array($state)) {
            throw new \InvalidArgumentException('Invalid serialized state');
        }
        
        return $state;
    }

    /**
     * Load decision tree configuration
     * 
     * @return void
     */
    private function loadDecisionTree(): void
    {
        $cacheKey = 'akinator_tree_v2_multichoice';
        $cached = Cache::read($cacheKey, 'quiz');
        
        if ($cached !== false && is_array($cached)) {
            $this->tree = $cached;
            return;
        }

        // Load from configuration or build default tree
        $treePath = Configure::read('Quiz.akinator.tree_path');
        if ($treePath && file_exists($treePath)) {
            $this->tree = include $treePath;
        } else {
            $this->tree = $this->buildDefaultTree();
        }

        // Validate tree structure
        $this->validateTree();

        // Cache the tree
        Cache::write($cacheKey, $this->tree, 'quiz');
    }

    /**
     * Build default decision tree
     * 
     * @return array Decision tree structure
     */
    private function buildDefaultTree(): array
    {
        return [
            'nodes' => [
                'root' => [
                    'id' => 'root',
                    'question' => __('What type of device do you need to connect?'),
                    'type' => 'choice',
                    'options' => [
                        ['id' => 'laptop', 'label' => __('Laptop/Computer')],
                        ['id' => 'phone', 'label' => __('Phone/Mobile Device')],
                        ['id' => 'tablet', 'label' => __('Tablet')],
                        ['id' => 'gaming', 'label' => __('Gaming Console')],
                        ['id' => 'other', 'label' => __('Other Device')],
                    ],
                    'weight' => 10,
                ],
                'laptop' => [
                    'id' => 'laptop',
                    'question' => __('What brand is your laptop?'),
                    'type' => 'choice',
                    'options' => [
                        ['id' => 'apple', 'label' => __('Apple (MacBook)')],
                        ['id' => 'dell', 'label' => __('Dell')],
                        ['id' => 'hp', 'label' => __('HP')],
                        ['id' => 'lenovo', 'label' => __('Lenovo')],
                        ['id' => 'other_laptop', 'label' => __('Other Brand')],
                    ],
                    'weight' => 8,
                ],
                'apple' => [
                    'id' => 'apple',
                    'question' => __('What type of charging port does your MacBook have?'),
                    'type' => 'choice',
                    'options' => [
                        ['id' => 'usbc', 'label' => __('USB-C')],
                        ['id' => 'magsafe', 'label' => __('MagSafe (magnetic)')],
                        ['id' => 'lightning', 'label' => __('Lightning')],
                        ['id' => 'unsure', 'label' => __('Not sure')],
                    ],
                    'weight' => 6,
                ],
                'phone' => [
                    'id' => 'phone',
                    'question' => __('Is your phone an iPhone or Android?'),
                    'type' => 'choice',
                    'options' => [
                        ['id' => 'iphone', 'label' => __('iPhone')],
                        ['id' => 'android', 'label' => __('Android')],
                        ['id' => 'other_phone', 'label' => __('Other')],
                    ],
                    'weight' => 8,
                ],
                'iphone' => [
                    'id' => 'iphone',
                    'question' => __('Which iPhone model do you have?'),
                    'type' => 'choice',
                    'options' => [
                        ['id' => 'iphone15_plus', 'label' => __('iPhone 15 or newer')],
                        ['id' => 'iphone14_12', 'label' => __('iPhone 12-14')],
                        ['id' => 'iphone11_older', 'label' => __('iPhone 11 or older')],
                        ['id' => 'unsure', 'label' => __('Not sure')],
                    ],
                    'weight' => 6,
                ],
                'android' => [
                    'id' => 'android',
                    'question' => __('What type of charging port does your Android have?'),
                    'type' => 'choice',
                    'options' => [
                        ['id' => 'usbc', 'label' => __('USB-C')],
                        ['id' => 'micro_usb', 'label' => __('Micro USB')],
                        ['id' => 'unsure', 'label' => __('Not sure')],
                    ],
                    'weight' => 6,
                ],
            ],
            'edges' => [
                'root' => [
                    'laptop' => 'laptop',
                    'phone' => 'phone',
                    'tablet' => 'tablet',
                    'gaming' => 'gaming',
                    'other' => 'other_device',
                ],
                'laptop' => [
                    'apple' => 'apple',
                    'dell' => 'dell_laptop',
                    'hp' => 'hp_laptop',
                    'lenovo' => 'lenovo_laptop',
                    'other_laptop' => 'generic_laptop',
                ],
                'phone' => [
                    'iphone' => 'iphone',
                    'android' => 'android',
                    'other_phone' => 'generic_phone',
                ],
                'apple' => [
                    'usbc' => 'result',
                    'magsafe' => 'result',
                    'lightning' => 'result',
                    'unsure' => 'apple_help',
                ],
                'iphone' => [
                    'iphone15_plus' => 'result', // iPhone 15+ with USB-C
                    'iphone14_12' => 'result',   // iPhone 12-14 with Lightning
                    'iphone11_older' => 'result', // iPhone 11- with Lightning
                    'unsure' => 'iphone_help',
                ],
                'android' => [
                    'usbc' => 'result',
                    'micro_usb' => 'result',
                    'unsure' => 'android_help',
                ],
            ],
            'terminals' => [
                'result',
                'apple_help',
                'android_help',
                'iphone_help',
                'dell_laptop',
                'hp_laptop',
                'lenovo_laptop',
                'generic_laptop',
                'generic_phone',
                'tablet',
                'gaming',
                'other_device',
            ],
        ];
    }

    /**
     * Validate tree structure
     * 
     * @throws \RuntimeException If tree is invalid
     */
    private function validateTree(): void
    {
        if (empty($this->tree['nodes']) || empty($this->tree['edges'])) {
            throw new \RuntimeException('Invalid decision tree: missing nodes or edges');
        }

        if (!isset($this->tree['nodes']['root'])) {
            throw new \RuntimeException('Invalid decision tree: missing root node');
        }
    }

    /**
     * Get question for a specific node
     * 
     * @param string $nodeId Node ID
     * @param array $state Current state
     * @return array|null Question data
     */
    private function getQuestionForNode(string $nodeId, array $state): ?array
    {
        // Try AI service first for intelligent questions
        if ($this->aiService && $nodeId === 'root') {
            $aiQuestion = $this->generateAIQuestion($state);
            if ($aiQuestion) {
                return $aiQuestion;
            }
        }
        
        // Always fall back to static tree questions
        if (!isset($this->tree['nodes'][$nodeId])) {
            // If no static tree question, generate a fallback
            return $this->generateFallbackQuestion($state, []);
        }

        $node = $this->tree['nodes'][$nodeId];
        
        return [
            'id' => $node['id'],
            'text' => $node['question'],
            'type' => $node['type'],
            'options' => $node['options'] ?? [],
            'weight' => $node['weight'] ?? 1,
        ];
    }

    /**
     * Generate AI-powered question based on current state
     * 
     * @param array $state Current quiz state
     * @return array|null Generated question
     */
    private function generateAIQuestion(array $state): ?array
    {
        try {
            // Get remaining products based on current answers
            $remainingProducts = $this->getRemainingProducts($state);
            
            if (empty($remainingProducts) || count($remainingProducts) <= 3) {
                return null; // Should terminate
            }
            
            // Use AI to generate the next question
            $question = $this->aiService->generateNextQuestion($state, $remainingProducts);
            
            if ($question) {
                $this->log('AI generated question: ' . $question['text'], 'debug');
                return $question;
            }
            
            // Fall back to predefined questions if AI fails
            return $this->generateFallbackQuestion($state, $remainingProducts);
            
        } catch (\Exception $e) {
            $this->log('Error generating AI question: ' . $e->getMessage(), 'error');
            return $this->generateFallbackQuestion($state, []);
        }
    }

    /**
     * Get products that still match current answers
     * 
     * @param array $state Current quiz state
     * @return array Remaining products
     */
    private function getRemainingProducts(array $state): array
    {
        try {
            $Products = TableRegistry::getTableLocator()->get('Products');
            $query = $Products->find()
                ->where(['is_published' => true])
                ->limit(1000); // Reasonable limit
                
            $products = $query->toArray();
            
            // Filter products based on current answers
            if (!empty($state['answers'])) {
                $products = array_filter($products, function($product) use ($state) {
                    return $this->productMatchesAnswers($product, $state['answers']);
                });
            }
            
            return array_values($products);
            
        } catch (\Exception $e) {
            $this->log('Error getting remaining products: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Check if product matches current answers
     * 
     * @param array $product Product data
     * @param array $answers Current answers
     * @return bool Whether product matches
     */
    private function productMatchesAnswers(array $product, array $answers): bool
    {
        foreach ($answers as $questionId => $answer) {
            // Simple rule-based filtering based on answers
            if (strpos($questionId, 'laptop') !== false) {
                if ($answer === 'yes' && stripos($product['device_category'] ?? '', 'laptop') === false) {
                    return false;
                }
                if ($answer === 'no' && stripos($product['device_category'] ?? '', 'laptop') !== false) {
                    return false;
                }
            }
            
            if (strpos($questionId, 'apple') !== false) {
                if ($answer === 'yes' && stripos($product['manufacturer'] ?? '', 'apple') === false) {
                    return false;
                }
                if ($answer === 'no' && stripos($product['manufacturer'] ?? '', 'apple') !== false) {
                    return false;
                }
            }
            
            if (strpos($questionId, 'usbc') !== false || strpos($questionId, 'usb-c') !== false) {
                if ($answer === 'yes' && stripos($product['port_family'] ?? '', 'usbc') === false) {
                    return false;
                }
                if ($answer === 'no' && stripos($product['port_family'] ?? '', 'usbc') !== false) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Generate fallback question when AI is not available
     * 
     * @param array $state Current state
     * @param array $products Remaining products
     * @return array|null Fallback question
     */
    private function generateFallbackQuestion(array $state, array $products): ?array
    {
        $questionCount = count($state['answers'] ?? []);
        
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
        } else {
            // Use a cycling approach or final question if we run out
            $question = $fallbackQuestions[$questionCount % count($fallbackQuestions)];
        }
        
        return [
            'id' => 'fallback_' . $questionCount,
            'text' => $question['text'],
            'type' => 'binary',
            'category' => $question['category'],
            'options' => [
                ['id' => 'yes', 'label' => 'Yes'],
                ['id' => 'no', 'label' => 'No']
            ]
        ];
    }

    /**
     * Get next node based on current node and answer
     * 
     * @param string $currentNode Current node ID
     * @param string $answer User's answer
     * @return string|null Next node ID
     */
    private function getNextNode(string $currentNode, string $answer): ?string
    {
        if (!isset($this->tree['edges'][$currentNode])) {
            return null;
        }

        $edges = $this->tree['edges'][$currentNode];
        return $edges[$answer] ?? null;
    }

    /**
     * Check if should terminate the quiz
     * 
     * @param array $state Current state
     * @param string|null $nextNode Next node ID
     * @return bool
     */
    private function shouldTerminate(array $state, ?string $nextNode): bool
    {
        // No next node available
        if (!$nextNode) {
            return true;
        }

        // Reached terminal node
        if (in_array($nextNode, $this->tree['terminals'] ?? [])) {
            return true;
        }

        // Maximum questions reached
        if ($state['question_count'] >= $this->config['max_questions']) {
            return true;
        }

        // High confidence reached
        if ($state['confidence'] >= $this->config['confidence_threshold']) {
            return true;
        }

        return false;
    }

    /**
     * Calculate current confidence based on answers
     * 
     * @param array $state Current state
     * @return float Confidence score (0.0 to 1.0)
     */
    private function calculateConfidence(array $state): float
    {
        $totalWeight = 0;
        $answeredWeight = 0;

        foreach ($state['answers'] as $nodeId => $answer) {
            $node = $this->tree['nodes'][$nodeId] ?? null;
            if ($node) {
                $weight = $node['weight'] ?? 1;
                $totalWeight += $weight;
                $answeredWeight += $weight;
            }
        }

        // Base confidence increases with each answer
        $baseConfidence = 0.3;
        $progressMultiplier = min(1.0, $state['question_count'] / 10);
        $weightedConfidence = $totalWeight > 0 ? ($answeredWeight / $totalWeight) : 0;

        return min(1.0, $baseConfidence + ($progressMultiplier * 0.4) + ($weightedConfidence * 0.3));
    }

    /**
     * Generate final result
     * 
     * @param array $state Final state
     * @return array Quiz result
     */
    private function generateResult(array $state): array
    {
        $this->log("Generating Akinator result for session: {$state['session_id']}", 'info');

        // Convert answers to standardized format for product matching
        $normalizedAnswers = $this->normalizeAnswers($state['answers']);
        
        // Get product recommendations
        $matchResult = $this->productMatcher->match($normalizedAnswers, [
            'max_results' => 5,
            'confidence_threshold' => 0.4, // Lower threshold for Akinator
        ]);

        $result = [
            'session_id' => $state['session_id'],
            'completed' => true,
            'final_confidence' => $state['confidence'],
            'questions_asked' => $state['question_count'],
            'total_matches' => $matchResult['total_matches'],
            'overall_confidence' => $matchResult['overall_confidence'],
            'recommendations' => $matchResult['products'],
            'method' => 'akinator',
            'completed_at' => time(),
        ];

        // Clean up session state
        $this->clearSessionState($state['session_id']);

        return $result;
    }

    /**
     * Normalize Akinator answers to standard quiz format
     * 
     * @param array $answers Raw answers from decision tree
     * @return array Normalized answers
     */
    private function normalizeAnswers(array $answers): array
    {
        $normalized = [];

        // Extract device type
        if (isset($answers['root'])) {
            $normalized['device_type'] = $answers['root'];
        }

        // Extract manufacturer
        $manufacturers = ['apple', 'dell', 'hp', 'lenovo', 'samsung', 'google'];
        foreach ($answers as $answer) {
            if (in_array($answer, $manufacturers)) {
                $normalized['manufacturer'] = $answer;
                break;
            }
        }

        // Extract port type
        $ports = ['usbc', 'lightning', 'micro_usb', 'magsafe'];
        foreach ($answers as $answer) {
            if (in_array($answer, $ports)) {
                $normalized['port_type'] = $answer;
                break;
            }
        }

        // Infer budget based on device type and brand
        if (!empty($normalized['manufacturer'])) {
            if ($normalized['manufacturer'] === 'apple') {
                $normalized['budget_range'] = ['min' => 30, 'max' => 100];
            } else {
                $normalized['budget_range'] = ['min' => 15, 'max' => 60];
            }
        }

        return $normalized;
    }

    /**
     * Store session state
     * 
     * @param string $sessionId Session ID
     * @param array $state State data
     */
    private function storeSessionState(string $sessionId, array $state): void
    {
        $cacheKey = "akinator_session_{$sessionId}";
        Cache::write($cacheKey, $state, 'quiz');
    }

    /**
     * Retrieve session state
     * 
     * @param string $sessionId Session ID
     * @return array|null State data
     */
    private function getSessionState(string $sessionId): ?array
    {
        $cacheKey = "akinator_session_{$sessionId}";
        $state = Cache::read($cacheKey, 'quiz');
        
        return $state !== false ? $state : null;
    }

    /**
     * Clear session state
     * 
     * @param string $sessionId Session ID
     */
    private function clearSessionState(string $sessionId): void
    {
        $cacheKey = "akinator_session_{$sessionId}";
        Cache::delete($cacheKey, 'quiz');
    }
}
