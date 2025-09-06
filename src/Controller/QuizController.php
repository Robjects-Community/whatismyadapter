<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Quiz\AiProductMatcherService;
use App\Service\Quiz\DecisionTreeService;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Exception;

/**
 * Quiz Controller
 * 
 * AI-powered interactive quiz to help users find suitable adapters and chargers
 * Supports both Akinator-style and comprehensive quiz modes
 *
 * @property \App\Model\Table\ProductsTable $Products
 * @property \App\Model\Table\QuizSubmissionsTable $QuizSubmissions
 */
class QuizController extends AppController
{
    /**
     * AI Product Matcher Service
     */
    private $productMatcher;

    /**
     * Decision Tree Service for Akinator mode
     */
    private $decisionTree;

    /**
     * Initialize method
     */
    public function initialize(): void
    {
        parent::initialize();
        
        // Initialize model tables
        $this->Products = TableRegistry::getTableLocator()->get('Products');
        $this->QuizSubmissions = TableRegistry::getTableLocator()->get('QuizSubmissions');
        
        // Initialize AI services
        $this->productMatcher = new AiProductMatcherService();
        $this->decisionTree = new DecisionTreeService();
    }

    /**
     * beforeFilter callback.
     *
     * Allow unauthenticated access to quiz actions
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated access to all quiz actions
        $this->Authentication->addUnauthenticatedActions([
            'index', 'akinator', 'comprehensive', 'submit', 'result',
            'take', 'preview' // Keep legacy actions for backward compatibility
        ]);
    }

    /**
     * Quiz index - Choose quiz type
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        // Check if quiz system is enabled
        if (!Configure::read('Quiz.enabled')) {
            $this->Flash->error(__('The quiz system is temporarily unavailable.'));
            return $this->redirect(['controller' => 'Products', 'action' => 'index']);
        }

        // Get quiz statistics for display
        $stats = $this->getQuizStatistics();
        
        // Get quiz configuration
        $config = Configure::read('Quiz');
        
        $this->set([
            'title' => __('Find Your Perfect Adapter'),
            'akinator_enabled' => $config['akinator']['enabled'] ?? true,
            'comprehensive_enabled' => $config['comprehensive']['enabled'] ?? true,
            'stats' => $stats,
        ]);

        return null;
    }

    /**
     * Akinator-style quiz
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function akinator(): ?Response
    {
        if (!Configure::read('Quiz.akinator.enabled')) {
            $this->Flash->error(__('Akinator quiz is not available.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->set([
            'title' => __('AI Adapter Genie'),
            'description' => __('Answer a few questions and let our AI find the perfect adapter for you.'),
            'max_questions' => Configure::read('Quiz.akinator.max_questions', 15),
        ]);

        return null;
    }

    /**
     * Comprehensive quiz form
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function comprehensive(): ?Response
    {
        if (!Configure::read('Quiz.comprehensive.enabled')) {
            $this->Flash->error(__('Comprehensive quiz is not available.'));
            return $this->redirect(['action' => 'index']);
        }

        // Get dynamic form options
        $manufacturers = $this->Products->find()
            ->select(['manufacturer'])
            ->where([
                'manufacturer IS NOT' => null,
                'manufacturer !=' => '',
                'is_published' => true,
            ])
            ->distinct(['manufacturer'])
            ->orderBy(['manufacturer' => 'ASC'])
            ->limit(20)
            ->toArray();

        $deviceCategories = $this->Products->find()
            ->select(['device_category'])
            ->where([
                'device_category IS NOT' => null,
                'device_category !=' => '',
                'is_published' => true,
            ])
            ->distinct(['device_category'])
            ->orderBy(['device_category' => 'ASC'])
            ->limit(20)
            ->toArray();

        $portTypes = $this->Products->find()
            ->select(['port_family'])
            ->where([
                'port_family IS NOT' => null,
                'port_family !=' => '',
                'is_published' => true,
            ])
            ->distinct(['port_family'])
            ->orderBy(['port_family' => 'ASC'])
            ->limit(15)
            ->toArray();

        $this->set([
            'title' => __('Comprehensive Adapter Finder'),
            'description' => __('Tell us about your needs and we\'ll find the perfect adapter.'),
            'manufacturers' => $manufacturers,
            'deviceCategories' => $deviceCategories,
            'portTypes' => $portTypes,
            'steps' => Configure::read('Quiz.comprehensive.steps', 6),
        ]);

        return null;
    }

    /**
     * Submit comprehensive quiz
     *
     * @return \Cake\Http\Response|null|void JSON response or redirect
     */
    public function submit(): ?Response
    {
        $this->request->allowMethod(['post']);

        try {
            $answers = $this->request->getData('answers', []);
            $quizType = $this->request->getData('quiz_type', 'comprehensive');
            $sessionId = $this->request->getData('session_id') ?: Text::uuid();

            // Validate answers
            if (empty($answers)) {
                throw new \InvalidArgumentException(__('No quiz answers provided.'));
            }

            // Get product recommendations
            $results = $this->productMatcher->match($answers, [
                'max_results' => Configure::read('Quiz.max_results', 10),
                'confidence_threshold' => Configure::read('Quiz.confidence_threshold', 0.6),
            ]);

            // Save submission
            $submission = $this->saveQuizSubmission($sessionId, $quizType, $answers, $results);

            if ($this->request->is('ajax')) {
                $response = [
                    'success' => true,
                    'submission_id' => $submission->id,
                    'total_matches' => $results['total_matches'],
                    'overall_confidence' => $results['overall_confidence'],
                    'recommendations' => $this->formatProductRecommendations($results['products']),
                    'processing_time' => $results['processing_time'],
                    'method' => $results['method'],
                ];

                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode($response));
            } else {
                return $this->redirect([
                    'action' => 'result',
                    '?' => ['submission' => $submission->id]
                ]);
            }

        } catch (\Exception $e) {
            $this->log('Quiz submission error: ' . $e->getMessage(), 'error');
            
            if ($this->request->is('ajax')) {
                $response = [
                    'success' => false,
                    'message' => __('Sorry, there was an error processing your quiz. Please try again.'),
                    'error' => Configure::read('debug') ? $e->getMessage() : null,
                ];

                return $this->response
                    ->withType('application/json')
                    ->withStatus(500)
                    ->withStringBody(json_encode($response));
            } else {
                $this->Flash->error(__('Sorry, there was an error processing your quiz. Please try again.'));
                return $this->redirect(['action' => 'comprehensive']);
            }
        }
    }

    /**
     * Display quiz results
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function result(): ?Response
    {
        $submissionId = $this->request->getQuery('submission');
        
        if (!$submissionId) {
            $this->Flash->error(__('Invalid quiz results request.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $submission = $this->QuizSubmissions->get($submissionId, [
                'contain' => ['Users']
            ]);

            // Get product details for recommendations
            $productIds = $submission->matched_product_ids ?? [];
            $products = [];
            
            if (!empty($productIds)) {
                $products = $this->Products->find()
                    ->where(['Products.id IN' => $productIds])
                    ->contain(['Tags', 'Users'])
                    ->toArray();
            }

            $this->set([
                'submission' => $submission,
                'products' => $products,
                'confidence' => $submission->confidence_scores['overall'] ?? 0,
                'title' => __('Your Adapter Recommendations'),
            ]);

            return null;

        } catch (\Exception $e) {
            $this->Flash->error(__('Quiz results not found or expired.'));
            return $this->redirect(['action' => 'index']);
        }
    }

    /**
     * Legacy take method for backward compatibility
     *
     * @return \Cake\Http\Response Redirects to new comprehensive quiz
     */
    public function take(): ?Response
    {
        return $this->redirect(['action' => 'comprehensive']);
    }

    /**
     * Legacy preview method for backward compatibility
     *
     * @return \Cake\Http\Response Redirects to index
     */
    public function preview(): ?Response
    {
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Get quiz statistics for display
     *
     * @return array Statistics data
     */
    private function getQuizStatistics(): array
    {
        try {
            $totalSubmissions = $this->QuizSubmissions->find()->count();
            $totalProducts = $this->Products->find()->where(['is_published' => true])->count();
            $avgConfidence = $this->QuizSubmissions->find()
                ->select(['avg_confidence' => 'AVG(JSON_EXTRACT(confidence_scores, "$.overall"))'])
                ->first();

            return [
                'total_submissions' => $totalSubmissions,
                'total_products' => $totalProducts,
                'avg_confidence' => round(($avgConfidence->avg_confidence ?? 0) * 100, 1),
            ];
        } catch (\Exception $e) {
            return [
                'total_submissions' => 0,
                'total_products' => 0,
                'avg_confidence' => 0,
            ];
        }
    }

    /**
     * Save quiz submission to database
     *
     * @param string $sessionId Session ID
     * @param string $quizType Quiz type
     * @param array $answers User answers
     * @param array $results Match results
     * @return \App\Model\Entity\QuizSubmission Saved submission
     */
    private function saveQuizSubmission(string $sessionId, string $quizType, array $answers, array $results)
    {
        $identity = $this->getRequest()->getAttribute('identity');
        
        $submission = $this->QuizSubmissions->newEntity([
            'user_id' => $identity ? $identity->id : null,
            'session_id' => $sessionId,
            'quiz_type' => $quizType,
            'answers' => $answers,
            'matched_product_ids' => array_map(function($p) {
                return $p['product']->id;
            }, $results['products']),
            'confidence_scores' => [
                'overall' => $results['overall_confidence'],
                'method' => $results['method'],
                'processing_time' => $results['processing_time'],
            ],
            'result_summary' => sprintf(
                '%d products matched with %.1f%% confidence using %s method',
                $results['total_matches'],
                $results['overall_confidence'] * 100,
                $results['method']
            ),
            'analytics' => [
                'user_agent' => $this->request->getHeaderLine('User-Agent'),
                'ip_address' => $this->request->clientIp(),
                'timestamp' => time(),
                'processing_time' => $results['processing_time'],
                'method' => $results['method'],
            ],
        ]);

        if (!$this->QuizSubmissions->save($submission)) {
            throw new \RuntimeException('Failed to save quiz submission');
        }

        return $submission;
    }

    /**
     * Format product recommendations for JSON response
     *
     * @param array $products Raw product recommendations
     * @return array Formatted recommendations
     */
    private function formatProductRecommendations(array $products): array
    {
        $formatted = [];

        foreach ($products as $item) {
            $product = $item['product'];
            
            $formatted[] = [
                'id' => $product->id,
                'title' => $product->title,
                'manufacturer' => $product->manufacturer,
                'price' => $product->price ? number_format($product->price, 2) : null,
                'currency' => $product->currency ?? 'USD',
                'image_url' => $product->image ?? null,
                'rating' => $product->numeric_rating,
                'certified' => (bool)$product->is_certified,
                'confidence_score' => round($item['confidence_score'] * 100, 1),
                'explanation' => $item['explanation'] ?? '',
                'match_reasons' => $item['match_reasons'] ?? [],
                'url' => $this->Url->build(['controller' => 'Products', 'action' => 'view', $product->id]),
            ];
        }

        return $formatted;
    }

    /**
     * Normalize database configuration
     *
     * @param array $dbConfig Database configuration
     * @return array Normalized configuration
     */
    private function normalizeDbConfig(array $dbConfig): array
    {
        $questions = [];
        
        if (isset($dbConfig['questions']) && is_array($dbConfig['questions'])) {
            foreach ($dbConfig['questions'] as $question) {
                $normalizedQuestion = [
                    'id' => $question['id'] ?? uniqid(),
                    'type' => 'multiple_choice', // Force multiple choice
                    'text' => $question['question'] ?? 'Question',
                    'required' => $question['required'] ?? true,
                    'weight' => $question['weight'] ?? 1,
                    'help_text' => $question['help_text'] ?? null,
                    'multiple' => $question['type'] === 'multiple_choice' && isset($question['multiple']) ? $question['multiple'] : false,
                    'options' => []
                ];

                // Convert options to expected format
                if (isset($question['options']) && is_array($question['options'])) {
                    foreach ($question['options'] as $option) {
                        $normalizedQuestion['options'][] = [
                            'key' => $option['id'] ?? $option['value'] ?? $option['key'] ?? uniqid(),
                            'label' => $option['label'] ?? $option['text'] ?? 'Option'
                        ];
                    }
                }

                $questions[] = $normalizedQuestion;
            }
        }

        return [
            'version' => 2,
            'quiz_info' => $dbConfig['quiz_info'] ?? [
                'title' => 'Smart Adapter & Charger Finder Quiz',
                'description' => 'Find the perfect adapter for your device',
                'estimated_time' => '2-3 minutes'
            ],
            'questions' => $questions,
            'display' => [
                'shuffle_questions' => false,
                'shuffle_options' => false,
                'show_progress' => true,
                'allow_back' => true
            ],
            'scoring' => $dbConfig['ai_matching_algorithm'] ?? [
                'method' => 'weighted_confidence',
                'minimum_match_score' => 0.6,
                'max_results' => 5
            ]
        ];
    }

    /**
     * Get minimal fallback configuration
     *
     * @return array Minimal configuration
     */
    private function getMinimalFallbackConfig(): array
    {
        return [
            'version' => 2,
            'quiz_info' => [
                'title' => 'Adapter Finder Quiz',
                'description' => 'Find the perfect adapter for your device',
                'estimated_time' => '1 minute'
            ],
            'questions' => [
                [
                    'id' => 'device_type',
                    'type' => 'multiple_choice',
                    'text' => 'What type of device do you need an adapter for?',
                    'required' => true,
                    'weight' => 10,
                    'options' => [
                        ['key' => 'laptop', 'label' => 'Laptop'],
                        ['key' => 'phone', 'label' => 'Phone'],
                        ['key' => 'tablet', 'label' => 'Tablet'],
                        ['key' => 'other', 'label' => 'Other']
                    ]
                ]
            ],
            'display' => [
                'shuffle_questions' => false,
                'shuffle_options' => false,
                'show_progress' => true,
                'allow_back' => true
            ],
            'scoring' => [
                'method' => 'simple',
                'max_results' => 5
            ]
        ];
    }

    /**
     * Handle quiz submission
     *
     * @param array $config Quiz configuration
     * @return \Cake\Http\Response|null
     */
    private function handleQuizSubmission(array $config): ?Response
    {
        $answers = $this->request->getData('answers', []);
        
        // Validate required questions
        $validationErrors = $this->validateQuizAnswers($answers, $config);
        
        if (!empty($validationErrors)) {
            if ($this->request->is('ajax')) {
                $response = [
                    'success' => false,
                    'errors' => $validationErrors,
                    'message' => __('Please answer all required questions.')
                ];

                $this->response = $this->response->withType('application/json');
                $this->set('response', $response);
                $this->set('_serialize', 'response');
                $this->viewBuilder()->setClassName('Json');

                return null;
            } else {
                foreach ($validationErrors as $error) {
                    $this->Flash->error($error);
                }
                return $this->redirect(['action' => 'take']);
            }
        }

        // Process quiz and get recommendations
        try {
            $recommendations = $this->processQuizResults($answers, $config);
            
            if ($this->request->is('ajax')) {
                $response = [
                    'success' => true,
                    'recommendations' => $recommendations,
                    'resultsHtml' => $this->renderQuizResults($recommendations)
                ];

                $this->response = $this->response->withType('application/json');
                $this->set('response', $response);
                $this->set('_serialize', 'response');
                $this->viewBuilder()->setClassName('Json');

                return null;
            } else {
                // Non-AJAX submission - redirect to results or set variables for results view
                $this->set(compact('recommendations', 'answers'));
                return $this->render('results');
            }
            
        } catch (Exception $e) {
            $this->log('Error processing quiz results: ' . $e->getMessage(), 'error');
            
            $errorMessage = __('Sorry, there was an error processing your quiz. Please try again.');
            
            if ($this->request->is('ajax')) {
                $response = [
                    'success' => false,
                    'message' => $errorMessage
                ];

                $this->response = $this->response->withType('application/json');
                $this->set('response', $response);
                $this->set('_serialize', 'response');
                $this->viewBuilder()->setClassName('Json');

                return null;
            } else {
                $this->Flash->error($errorMessage);
                return $this->redirect(['action' => 'take']);
            }
        }
    }

    /**
     * Validate quiz answers
     *
     * @param array $answers User answers
     * @param array $config Quiz configuration
     * @return array Validation errors
     */
    private function validateQuizAnswers(array $answers, array $config): array
    {
        $errors = [];
        $questions = $config['questions'] ?? [];

        foreach ($questions as $question) {
            if ($question['required'] ?? false) {
                $questionId = $question['id'];
                
                if (!isset($answers[$questionId]) || empty($answers[$questionId])) {
                    $errors[] = __('Please answer: {0}', $question['text']);
                }
            }
        }

        return $errors;
    }

    /**
     * Process quiz results and find matching products
     *
     * @param array $answers User answers
     * @param array $config Quiz configuration
     * @return array Recommendations
     */
    private function processQuizResults(array $answers, array $config): array
    {
        // Load Products table
        $this->loadModel('Products');
        
        // Start with published products
        $query = $this->Products->getPublishedProducts();
        
        // Apply filters based on answers
        $filters = $this->buildProductFilters($answers, $config);
        
        if (!empty($filters['manufacturers'])) {
            $manufacturerConditions = [];
            foreach ($filters['manufacturers'] as $manufacturer) {
                $manufacturerConditions[] = ['Products.manufacturer LIKE' => '%' . $manufacturer . '%'];
            }
            $query->where(['OR' => $manufacturerConditions]);
        }

        if (!empty($filters['tags'])) {
            $query->matching('Tags', function ($q) use ($filters) {
                return $q->where(['Tags.slug IN' => $filters['tags']]);
            });
        }

        if (!empty($filters['price_range'])) {
            $priceRange = $filters['price_range'];
            if (isset($priceRange['min'])) {
                $query->where(['Products.price >=' => $priceRange['min']]);
            }
            if (isset($priceRange['max'])) {
                $query->where(['Products.price <=' => $priceRange['max']]);
            }
        }

        // Get results
        $products = $query->limit(10)->toArray();

        return [
            'products' => $products,
            'filters_applied' => $filters,
            'total_found' => count($products),
            'confidence_score' => $this->calculateConfidenceScore($answers, $products),
            'reasoning' => $this->generateRecommendationReasoning($answers, $filters)
        ];
    }

    /**
     * Build product filters from quiz answers
     *
     * @param array $answers User answers
     * @param array $config Quiz configuration
     * @return array Product filters
     */
    private function buildProductFilters(array $answers, array $config): array
    {
        $filters = [
            'manufacturers' => [],
            'tags' => [],
            'price_range' => []
        ];

        // Map answers to product filters
        foreach ($answers as $questionId => $answer) {
            switch ($questionId) {
                case 'device_type':
                    $filters['tags'][] = $answer;
                    break;
                    
                case 'manufacturer':
                    $filters['manufacturers'][] = $answer;
                    break;
                    
                case 'budget':
                    $filters['price_range'] = $this->parseBudgetRange($answer);
                    break;
                    
                case 'port_type':
                    $filters['tags'][] = str_replace('-', '_', $answer);
                    break;
                    
                case 'features':
                    if (is_array($answer)) {
                        $filters['tags'] = array_merge($filters['tags'], $answer);
                    } else {
                        $filters['tags'][] = $answer;
                    }
                    break;
            }
        }

        return $filters;
    }

    /**
     * Parse budget range from answer
     *
     * @param string $budgetAnswer Budget answer
     * @return array Price range
     */
    private function parseBudgetRange(string $budgetAnswer): array
    {
        $ranges = [
            '5-20' => ['min' => 5, 'max' => 20],
            '20-50' => ['min' => 20, 'max' => 50],
            '50-100' => ['min' => 50, 'max' => 100],
            '100+' => ['min' => 100, 'max' => 999]
        ];

        return $ranges[$budgetAnswer] ?? [];
    }

    /**
     * Calculate confidence score for recommendations
     *
     * @param array $answers User answers
     * @param array $products Found products
     * @return float Confidence score
     */
    private function calculateConfidenceScore(array $answers, array $products): float
    {
        $totalQuestions = count($answers);
        $productsFound = count($products);
        
        if ($totalQuestions === 0) {
            return 0.0;
        }
        
        // Base confidence on completeness and results
        $completeness = min($totalQuestions / 8, 1.0); // Expecting up to 8 questions
        $availability = min($productsFound / 5, 1.0);  // Expecting up to 5 good results
        
        return round(($completeness * 0.6 + $availability * 0.4) * 100, 1);
    }

    /**
     * Generate recommendation reasoning
     *
     * @param array $answers User answers
     * @param array $filters Applied filters
     * @return array Reasoning text
     */
    private function generateRecommendationReasoning(array $answers, array $filters): array
    {
        $reasoning = [];

        if (!empty($filters['manufacturers'])) {
            $reasoning[] = __('Filtered by manufacturer: {0}', implode(', ', $filters['manufacturers']));
        }

        if (!empty($filters['tags'])) {
            $reasoning[] = __('Matched device compatibility: {0}', implode(', ', $filters['tags']));
        }

        if (!empty($filters['price_range'])) {
            $range = $filters['price_range'];
            $min = $range['min'] ?? 0;
            $max = $range['max'] ?? 999;
            $reasoning[] = __('Within your budget range: ${0} - ${1}', $min, $max);
        }

        if (empty($reasoning)) {
            $reasoning[] = __('Based on your responses, here are some general recommendations.');
        }

        return $reasoning;
    }

    /**
     * Render quiz results as HTML
     *
     * @param array $recommendations Quiz recommendations
     * @return string HTML content
     */
    private function renderQuizResults(array $recommendations): string
    {
        $products = $recommendations['products'] ?? [];
        $confidence = $recommendations['confidence_score'] ?? 0;
        $reasoning = $recommendations['reasoning'] ?? [];

        $html = '<div class="quiz-results-content">';
        
        // Confidence and summary
        $html .= '<div class="results-summary mb-4">';
        $html .= '<div class="confidence-score text-center mb-3">';
        $html .= '<div class="h2 text-primary">' . $confidence . '%</div>';
        $html .= '<p class="text-muted">Match Confidence</p>';
        $html .= '</div>';
        
        if (!empty($reasoning)) {
            $html .= '<div class="reasoning">';
            $html .= '<h6><i class="fas fa-lightbulb"></i> ' . __('Why these recommendations:') . '</h6>';
            $html .= '<ul>';
            foreach ($reasoning as $reason) {
                $html .= '<li>' . h($reason) . '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        $html .= '</div>';

        // Product recommendations
        if (!empty($products)) {
            $html .= '<div class="recommended-products">';
            $html .= '<h4 class="mb-3"><i class="fas fa-star"></i> ' . __('Recommended Products') . '</h4>';
            $html .= '<div class="row">';

            foreach (array_slice($products, 0, 6) as $product) {
                $html .= '<div class="col-md-6 col-lg-4 mb-3">';
                $html .= '<div class="card h-100">';
                if (!empty($product->image)) {
                    $html .= '<img src="' . h($product->image) . '" class="card-img-top" alt="' . h($product->alt_text ?: $product->title) . '" style="height: 150px; object-fit: cover;">';
                }
                $html .= '<div class="card-body">';
                $html .= '<h6 class="card-title">' . h($product->title) . '</h6>';
                $html .= '<p class="card-text text-muted small">' . $this->truncateText(h($product->description ?? ''), 80) . '</p>';
                if (!empty($product->manufacturer)) {
                    $html .= '<p class="mb-1 small"><strong>Manufacturer:</strong> ' . h($product->manufacturer) . '</p>';
                }
                if (!empty($product->price)) {
                    $html .= '<p class="mb-2"><strong class="text-success">$' . number_format($product->price, 2) . '</strong></p>';
                }
                $html .= '</div>';
                $html .= '<div class="card-footer">';
                $html .= '<a href="/products/view/' . $product->id . '" class="btn btn-primary btn-sm">View Details</a>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }

            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div class="no-results text-center py-4">';
            $html .= '<i class="fas fa-search fa-3x text-muted mb-3"></i>';
            $html .= '<h5>' . __('No matches found') . '</h5>';
            $html .= '<p class="text-muted">' . __('We couldn\'t find products matching your criteria, but you can browse our full catalog.') . '</p>';
            $html .= '<a href="/products" class="btn btn-primary">' . __('Browse All Products') . '</a>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Truncate text to specified length
     *
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @return string Truncated text
     */
    private function truncateText(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }
}
