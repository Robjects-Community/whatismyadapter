<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\View\JsonView;

/**
 * API Quiz Controller
 * Handles JSON API endpoints for the quiz system
 */
class QuizController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('Products');
        $this->loadModel('QuizSubmissions');

        // Set up API response handling
        $this->loadComponent('RequestHandler');
        $this->viewBuilder()->setClassName(JsonView::class);
    }

    /**
     * API endpoint to start a new quiz session
     * GET /api/quiz/start/{type}
     */
    public function start($type = 'comprehensive')
    {
        $this->request->allowMethod(['get']);

        if (!in_array($type, ['comprehensive', 'akinator'])) {
            throw new BadRequestException('Invalid quiz type. Use "comprehensive" or "akinator".');
        }

        $sessionId = $this->getRequest()->getSession()->id();

        // Get quiz structure based on type
        if ($type === 'akinator') {
            $quizStructure = $this->getAkinatorStructure();
            $firstQuestion = $quizStructure['questions'][0];
        } else {
            $quizStructure = $this->getComprehensiveStructure();
            $firstQuestion = $quizStructure['questions'][0];
        }

        $response = [
            'success' => true,
            'data' => [
                'session_id' => $sessionId,
                'quiz_type' => $type,
                'quiz_info' => $quizStructure['info'],
                'first_question' => $firstQuestion,
                'total_questions' => count($quizStructure['questions']),
                'started_at' => date('c')
            ]
        ];

        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', 'response');
    }

    /**
     * API endpoint to get next question
     * POST /api/quiz/next-question
     */
    public function nextQuestion()
    {
        $this->request->allowMethod(['post']);

        $requestData = $this->request->getData();
        $quizType = $requestData['quiz_type'] ?? 'comprehensive';
        $currentAnswers = $requestData['answers'] ?? [];
        $currentQuestionId = $requestData['current_question_id'] ?? null;

        if ($quizType === 'akinator') {
            $nextQuestion = $this->getNextAkinatorQuestion($currentAnswers, $currentQuestionId);
        } else {
            $nextQuestion = $this->getNextComprehensiveQuestion($currentAnswers, $currentQuestionId);
        }

        $progress = $this->calculateProgress($currentAnswers, $quizType);
        $confidence = $this->calculateConfidence($currentAnswers);

        $response = [
            'success' => true,
            'data' => [
                'next_question' => $nextQuestion,
                'progress' => $progress,
                'confidence' => $confidence,
                'is_complete' => $nextQuestion === null
            ]
        ];

        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', 'response');
    }

    /**
     * API endpoint to submit quiz and get results
     * POST /api/quiz/submit
     */
    public function submit()
    {
        $this->request->allowMethod(['post']);

        $requestData = $this->request->getData();
        $quizType = $requestData['quiz_type'] ?? 'comprehensive';
        $answers = $requestData['answers'] ?? [];
        $sessionId = $requestData['session_id'] ?? $this->getRequest()->getSession()->id();

        if (empty($answers)) {
            throw new BadRequestException('No quiz answers provided.');
        }

        // Find matching products using AI algorithm
        $matches = $this->Products->findMatchingProducts($answers);

        // Calculate overall metrics
        $totalMatches = count($matches);
        $overallConfidence = $totalMatches > 0 ? 
            array_sum(array_column($matches, 'confidence_score')) / $totalMatches : 0.0;

        // Save submission to database
        $submissionId = $this->saveQuizSubmission([
            'session_id' => $sessionId,
            'quiz_type' => $quizType,
            'quiz_data' => $answers,
            'recommendations' => $matches,
            'confidence_score' => $overallConfidence,
            'ip_address' => $this->getRequest()->clientIp(),
            'user_agent' => $this->getRequest()->getHeaderLine('User-Agent'),
            'completed' => true
        ]);

        $response = [
            'success' => true,
            'data' => [
                'submission_id' => $submissionId,
                'total_matches' => $totalMatches,
                'overall_confidence' => round($overallConfidence, 3),
                'recommendations' => array_map(function($match) {
                    return [
                        'product' => [
                            'id' => $match['product']->id,
                            'title' => $match['product']->title,
                            'manufacturer' => $match['product']->manufacturer,
                            'port_type' => $match['product']->port_type,
                            'price' => $match['product']->price,
                            'formatted_price' => $match['product']->getFormattedPrice(),
                            'rating' => $match['product']->rating,
                            'star_rating' => $match['product']->getStarRating(),
                            'certified' => $match['product']->certified,
                            'image_url' => $match['product']->image_url,
                            'features' => $match['product']->features,
                            'device_compatibility' => $match['product']->device_compatibility
                        ],
                        'confidence_score' => round($match['confidence_score'], 3),
                        'explanation' => $match['explanation']
                    ];
                }, $matches),
                'submitted_at' => date('c')
            ]
        ];

        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', 'response');
    }

    /**
     * API endpoint to get product details
     * GET /api/products/{id}
     */
    public function product($id = null)
    {
        $this->request->allowMethod(['get']);

        if (!$id) {
            throw new BadRequestException('Product ID is required.');
        }

        try {
            $product = $this->Products->get($id, ['finder' => 'approved']);
        } catch (\Exception $e) {
            throw new NotFoundException('Product not found.');
        }

        // Increment view count
        $this->Products->updateAll(['views' => $product->views + 1], ['id' => $id]);

        $response = [
            'success' => true,
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'manufacturer' => $product->manufacturer,
                    'port_type' => $product->port_type,
                    'form_factor' => $product->form_factor,
                    'device_cat' => $product->device_cat,
                    'device_compatibility' => $product->device_compatibility,
                    'price' => $product->price,
                    'formatted_price' => $product->getFormattedPrice(),
                    'rating' => $product->rating,
                    'star_rating' => $product->getStarRating(),
                    'certified' => $product->certified,
                    'status' => $product->status,
                    'image_url' => $product->image_url,
                    'features' => $product->features,
                    'views' => $product->views + 1,
                    'created' => $product->created,
                    'modified' => $product->modified
                ]
            ]
        ];

        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', 'response');
    }

    /**
     * API endpoint to search products
     * GET /api/products/search?q={query}&manufacturer={manufacturer}&port_type={port}
     */
    public function search()
    {
        $this->request->allowMethod(['get']);

        $query = $this->request->getQuery('q', '');
        $manufacturer = $this->request->getQuery('manufacturer');
        $portType = $this->request->getQuery('port_type');
        $minPrice = $this->request->getQuery('min_price');
        $maxPrice = $this->request->getQuery('max_price');
        $certified = $this->request->getQuery('certified');
        $limit = min(20, max(1, (int)$this->request->getQuery('limit', 10)));

        $productsQuery = $this->Products->find('approved');

        // Apply search filters
        if (!empty($query)) {
            $productsQuery->where([
                'OR' => [
                    'title LIKE' => "%{$query}%",
                    'manufacturer LIKE' => "%{$query}%",
                    'device_compatibility LIKE' => "%{$query}%"
                ]
            ]);
        }

        if ($manufacturer) {
            $productsQuery->where(['manufacturer' => $manufacturer]);
        }

        if ($portType) {
            $productsQuery->where(['port_type' => $portType]);
        }

        if ($minPrice !== null) {
            $productsQuery->where(['price >=' => (float)$minPrice]);
        }

        if ($maxPrice !== null) {
            $productsQuery->where(['price <=' => (float)$maxPrice]);
        }

        if ($certified !== null) {
            $productsQuery->where(['certified' => (bool)$certified]);
        }

        $products = $productsQuery
            ->orderBy(['rel_score' => 'DESC', 'rating' => 'DESC'])
            ->limit($limit)
            ->toArray();

        $response = [
            'success' => true,
            'data' => [
                'products' => array_map(function($product) {
                    return [
                        'id' => $product->id,
                        'title' => $product->title,
                        'manufacturer' => $product->manufacturer,
                        'port_type' => $product->port_type,
                        'price' => $product->price,
                        'formatted_price' => $product->getFormattedPrice(),
                        'rating' => $product->rating,
                        'star_rating' => $product->getStarRating(),
                        'certified' => $product->certified,
                        'image_url' => $product->image_url,
                        'device_compatibility' => $product->device_compatibility
                    ];
                }, $products),
                'total_found' => count($products),
                'search_params' => [
                    'query' => $query,
                    'manufacturer' => $manufacturer,
                    'port_type' => $portType,
                    'price_range' => [$minPrice, $maxPrice],
                    'certified' => $certified,
                    'limit' => $limit
                ]
            ]
        ];

        $this->set(compact('response'));
        $this->viewBuilder()->setOption('serialize', 'response');
    }

    /**
     * Get Akinator quiz structure
     */
    private function getAkinatorStructure(): array
    {
        return [
            'info' => [
                'title' => 'AI Adapter Genie',
                'description' => 'Think of your device and I'll guess the adapter you need!',
                'style' => 'akinator',
                'max_questions' => 10
            ],
            'questions' => [
                [
                    'id' => 'q1',
                    'question' => 'Is your device something you can hold in one hand?',
                    'type' => 'binary',
                    'options' => [
                        ['id' => 'yes', 'label' => 'Yes'],
                        ['id' => 'no', 'label' => 'No']
                    ],
                    'yes_next' => 'q2_handheld',
                    'no_next' => 'q2_large'
                ],
                [
                    'id' => 'q2_handheld',
                    'question' => 'Does your device make phone calls?',
                    'type' => 'binary',
                    'depends_on' => ['q1' => 'yes'],
                    'options' => [
                        ['id' => 'yes', 'label' => 'Yes'],
                        ['id' => 'no', 'label' => 'No']
                    ],
                    'yes_next' => 'q3_phone',
                    'no_next' => 'q3_non_phone'
                ],
                [
                    'id' => 'q2_large',
                    'question' => 'Is it primarily used for computing or work?',
                    'type' => 'binary',
                    'depends_on' => ['q1' => 'no'],
                    'options' => [
                        ['id' => 'yes', 'label' => 'Yes'],
                        ['id' => 'no', 'label' => 'No']
                    ],
                    'yes_next' => 'q3_computer',
                    'no_next' => 'q3_entertainment'
                ]
                // Additional questions would continue here...
            ]
        ];
    }

    /**
     * Get comprehensive quiz structure
     */
    private function getComprehensiveStructure(): array
    {
        return [
            'info' => [
                'title' => 'Comprehensive Device Quiz',
                'description' => 'Answer detailed questions for precise recommendations',
                'style' => 'comprehensive',
                'total_steps' => 6
            ],
            'questions' => [
                [
                    'id' => 'device_type',
                    'step' => 1,
                    'question' => 'What type of device do you need a charger for?',
                    'type' => 'single_choice',
                    'required' => true,
                    'options' => [
                        ['id' => 'laptop', 'label' => 'Laptop/MacBook'],
                        ['id' => 'phone', 'label' => 'Phone/Mobile Device'],
                        ['id' => 'tablet', 'label' => 'Tablet/iPad'],
                        ['id' => 'gaming', 'label' => 'Gaming Console'],
                        ['id' => 'earbuds', 'label' => 'Earbuds/Headphones'],
                        ['id' => 'other', 'label' => 'Other Device']
                    ]
                ],
                [
                    'id' => 'manufacturer',
                    'step' => 2,
                    'question' => 'What's the manufacturer of your device?',
                    'type' => 'single_choice',
                    'required' => true,
                    'options' => [
                        ['id' => 'apple', 'label' => 'Apple'],
                        ['id' => 'samsung', 'label' => 'Samsung'],
                        ['id' => 'dell', 'label' => 'Dell'],
                        ['id' => 'hp', 'label' => 'HP'],
                        ['id' => 'lenovo', 'label' => 'Lenovo'],
                        ['id' => 'asus', 'label' => 'ASUS'],
                        ['id' => 'other', 'label' => 'Other/Not sure']
                    ]
                ]
                // Additional questions would continue here...
            ]
        ];
    }

    /**
     * Save quiz submission to database
     */
    private function saveQuizSubmission(array $data): int
    {
        $submission = $this->QuizSubmissions->newEntity($data);

        if ($this->QuizSubmissions->save($submission)) {
            return $submission->id;
        }

        return 0;
    }

    /**
     * Get next Akinator question based on current state
     */
    private function getNextAkinatorQuestion(array $answers, ?string $currentId): ?array
    {
        $questions = $this->getAkinatorStructure()['questions'];

        if (empty($answers) || !$currentId) {
            return $questions[0] ?? null;
        }

        $lastAnswer = $answers[$currentId] ?? null;
        if (!$lastAnswer) {
            return null;
        }

        // Find current question and determine next
        foreach ($questions as $question) {
            if ($question['id'] === $currentId) {
                $nextId = $lastAnswer === 'yes' ? $question['yes_next'] : $question['no_next'];

                // Find next question
                foreach ($questions as $nextQuestion) {
                    if ($nextQuestion['id'] === $nextId) {
                        return $nextQuestion;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get next comprehensive question
     */
    private function getNextComprehensiveQuestion(array $answers, ?string $currentId): ?array
    {
        $questions = $this->getComprehensiveStructure()['questions'];

        if (empty($answers)) {
            return $questions[0] ?? null;
        }

        $currentIndex = 0;
        foreach ($questions as $index => $question) {
            if ($question['id'] === $currentId) {
                $currentIndex = $index;
                break;
            }
        }

        $nextIndex = $currentIndex + 1;
        return $questions[$nextIndex] ?? null;
    }

    /**
     * Calculate progress based on quiz type
     */
    private function calculateProgress(array $answers, string $quizType): array
    {
        $answeredCount = count($answers);

        if ($quizType === 'akinator') {
            $estimatedTotal = 8;
            return [
                'answered' => $answeredCount,
                'estimated_total' => $estimatedTotal,
                'percentage' => min(100, round(($answeredCount / $estimatedTotal) * 100))
            ];
        } else {
            $totalSteps = 6;
            return [
                'answered' => $answeredCount,
                'total_steps' => $totalSteps,
                'percentage' => min(100, round(($answeredCount / $totalSteps) * 100))
            ];
        }
    }

    /**
     * Calculate confidence based on answers
     */
    private function calculateConfidence(array $answers): float
    {
        $baseConfidence = 0.3;
        $incrementPerAnswer = 0.08;

        return min(0.95, $baseConfidence + (count($answers) * $incrementPerAnswer));
    }
}
?>