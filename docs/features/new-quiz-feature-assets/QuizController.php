<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\BadRequestException;

/**
 * Quiz Controller
 * Handles the main quiz interface and logic
 */
class QuizController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('Products');
        $this->loadModel('QuizSubmissions');

        // Load components for session and request handling
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
    }

    /**
     * Quiz homepage - shows quiz type selection
     */
    public function index()
    {
        $this->set('title', 'AI Adapter Finder Quiz');

        // Get some statistics for the homepage
        $totalProducts = $this->Products->find('approved')->count();
        $totalManufacturers = $this->Products->find('approved')
            ->select(['manufacturer'])
            ->distinct(['manufacturer'])
            ->count();

        $this->set(compact('totalProducts', 'totalManufacturers'));
    }

    /**
     * Start Akinator-style quiz
     */
    public function akinator()
    {
        $this->set('title', 'Akinator Style Quiz');
        $this->set('quizType', 'akinator');

        // Load quiz questions from JSON or database
        $questions = $this->loadAkinatorQuestions();
        $this->set('questions', $questions);
    }

    /**
     * Start comprehensive quiz
     */
    public function comprehensive()
    {
        $this->set('title', 'Comprehensive Device Quiz');
        $this->set('quizType', 'comprehensive');

        // Load comprehensive quiz structure
        $questions = $this->loadComprehensiveQuestions();
        $this->set('questions', $questions);

        // Get dynamic options from database
        $manufacturers = $this->Products->find('approved')
            ->select(['manufacturer'])
            ->distinct(['manufacturer'])
            ->where(['manufacturer IS NOT' => null])
            ->toArray();

        $portTypes = $this->Products->find('approved')
            ->select(['port_type'])
            ->distinct(['port_type'])
            ->where(['port_type IS NOT' => null])
            ->toArray();

        $this->set(compact('manufacturers', 'portTypes'));
    }

    /**
     * Process quiz submission and show results
     */
    public function results()
    {
        $this->request->allowMethod(['post', 'get']);

        if ($this->request->is('post')) {
            $quizData = $this->request->getData();
            $quizType = $quizData['quiz_type'] ?? 'comprehensive';

            // Process the quiz data and find matching products
            $results = $this->processQuizSubmission($quizData, $quizType);

            // Save submission to database
            $this->saveQuizSubmission($quizData, $results, $quizType);

            $this->set([
                'results' => $results,
                'quizType' => $quizType,
                'quizData' => $quizData
            ]);
        } else {
            // Handle GET request (maybe from a saved link)
            throw new NotFoundException('Quiz results not found.');
        }
    }

    /**
     * Load Akinator questions from configuration
     */
    private function loadAkinatorQuestions(): array
    {
        // This could be loaded from database or configuration file
        return [
            [
                'id' => 'q1',
                'question' => 'Is your device something you can hold in one hand?',
                'type' => 'binary',
                'yes_next' => 'q2_handheld',
                'no_next' => 'q2_large'
            ],
            [
                'id' => 'q2_handheld',
                'question' => 'Does your device make phone calls?',
                'type' => 'binary',
                'depends_on' => ['q1' => 'yes'],
                'yes_next' => 'q3_phone',
                'no_next' => 'q3_non_phone'
            ],
            [
                'id' => 'q2_large',
                'question' => 'Is it primarily used for computing or work?',
                'type' => 'binary',
                'depends_on' => ['q1' => 'no'],
                'yes_next' => 'q3_computer',
                'no_next' => 'q3_entertainment'
            ],
            [
                'id' => 'q3_phone',
                'question' => 'Does it have a round home button on the front?',
                'type' => 'binary',
                'depends_on' => ['q2_handheld' => 'yes'],
                'yes_next' => 'result_old_iphone',
                'no_next' => 'q4_modern_phone'
            ],
            [
                'id' => 'q4_modern_phone',
                'question' => 'Does your phone charge with USB-C?',
                'type' => 'binary',
                'depends_on' => ['q3_phone' => 'no'],
                'yes_next' => 'result_android_usbc',
                'no_next' => 'result_iphone_lightning'
            ],
            [
                'id' => 'q3_computer',
                'question' => 'Is it made by Apple?',
                'type' => 'binary',
                'depends_on' => ['q2_large' => 'yes'],
                'yes_next' => 'q4_mac',
                'no_next' => 'result_pc_laptop'
            ],
            [
                'id' => 'q4_mac',
                'question' => 'Does it charge with a magnetic connector?',
                'type' => 'binary',
                'depends_on' => ['q3_computer' => 'yes'],
                'yes_next' => 'result_macbook_magsafe',
                'no_next' => 'result_macbook_usbc'
            ]
        ];
    }

    /**
     * Load comprehensive quiz questions
     */
    private function loadComprehensiveQuestions(): array
    {
        return [
            [
                'id' => 'device_type',
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
                'question' => "What's the manufacturer of your device?",
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
            ],
            [
                'id' => 'port_type',
                'question' => 'What type of charging port does your device have?',
                'type' => 'single_choice',
                'required' => true,
                'options' => [
                    ['id' => 'usbc', 'label' => 'USB-C'],
                    ['id' => 'lightning', 'label' => 'Lightning (iPhone/iPad)'],
                    ['id' => 'microusb', 'label' => 'Micro USB'],
                    ['id' => 'magsafe', 'label' => 'MagSafe (MacBook)'],
                    ['id' => 'magsafe2', 'label' => 'MagSafe 2 (MacBook)'],
                    ['id' => 'barrel', 'label' => 'Barrel connector (Laptop)'],
                    ['id' => 'unsure', 'label' => "I'm not sure"]
                ]
            ],
            [
                'id' => 'power_needs',
                'question' => 'What are your power requirements?',
                'type' => 'single_choice',
                'required' => false,
                'options' => [
                    ['id' => 'low', 'label' => 'Low Power (5W-18W) - Phones, small devices'],
                    ['id' => 'medium', 'label' => 'Medium Power (20W-65W) - Tablets, ultrabooks'],
                    ['id' => 'high', 'label' => 'High Power (70W+) - Gaming laptops, workstations'],
                    ['id' => 'unknown', 'label' => "I don't know"]
                ]
            ],
            [
                'id' => 'budget',
                'question' => "What's your budget range?",
                'type' => 'range_slider',
                'required' => true,
                'min' => 10,
                'max' => 200,
                'default' => [25, 75],
                'currency' => 'USD'
            ],
            [
                'id' => 'features',
                'question' => 'What additional features are important to you?',
                'type' => 'multiple_choice',
                'required' => false,
                'options' => [
                    ['id' => 'fast_charging', 'label' => 'Fast charging support'],
                    ['id' => 'portable', 'label' => 'Compact/portable design'],
                    ['id' => 'multiport', 'label' => 'Multiple charging ports'],
                    ['id' => 'certified', 'label' => 'Official certification (MFi, etc.)'],
                    ['id' => 'wireless', 'label' => 'Wireless charging capability']
                ]
            ]
        ];
    }

    /**
     * Process quiz submission and find matching products
     */
    private function processQuizSubmission(array $quizData, string $quizType): array
    {
        // Find matching products using AI algorithm
        $matches = $this->Products->findMatchingProducts($quizData);

        // Calculate overall confidence
        $overallConfidence = 0.0;
        $totalMatches = count($matches);

        if ($totalMatches > 0) {
            $overallConfidence = array_sum(array_column($matches, 'confidence_score')) / $totalMatches;
        }

        return [
            'matches' => $matches,
            'total_matches' => $totalMatches,
            'overall_confidence' => $overallConfidence,
            'quiz_type' => $quizType,
            'recommendations_generated' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Save quiz submission to database for analytics
     */
    private function saveQuizSubmission(array $quizData, array $results, string $quizType): void
    {
        $submission = $this->QuizSubmissions->newEmptyEntity();

        $submissionData = [
            'user_id' => $this->getRequest()->getSession()->read('Auth.User.id'),
            'session_id' => $this->getRequest()->getSession()->id(),
            'quiz_type' => $quizType,
            'quiz_data' => $quizData,
            'recommendations' => $results,
            'confidence_score' => $results['overall_confidence'],
            'ip_address' => $this->getRequest()->clientIp(),
            'user_agent' => $this->getRequest()->getHeaderLine('User-Agent'),
            'completed' => true
        ];

        $submission = $this->QuizSubmissions->patchEntity($submission, $submissionData);
        $this->QuizSubmissions->save($submission);
    }

    /**
     * AJAX endpoint for getting next question in Akinator mode
     */
    public function nextQuestion()
    {
        $this->request->allowMethod(['post']);

        if ($this->request->is('ajax')) {
            $this->autoRender = false;

            $currentAnswers = $this->request->getData('answers', []);
            $questions = $this->loadAkinatorQuestions();

            // Determine next question based on current answers
            $nextQuestion = $this->determineNextAkinatorQuestion($currentAnswers, $questions);

            $response = [
                'success' => true,
                'question' => $nextQuestion,
                'progress' => $this->calculateProgress($currentAnswers, $questions),
                'confidence' => $this->calculateCurrentConfidence($currentAnswers)
            ];

            $this->response = $this->response->withType('application/json');
            $this->response = $this->response->withStringBody(json_encode($response));
            return $this->response;
        }

        throw new BadRequestException('Invalid request');
    }

    /**
     * Determine next Akinator question based on current answers
     */
    private function determineNextAkinatorQuestion(array $currentAnswers, array $questions): ?array
    {
        if (empty($currentAnswers)) {
            return $questions[0] ?? null;
        }

        $lastQuestionId = array_key_last($currentAnswers);
        $lastAnswer = $currentAnswers[$lastQuestionId];

        // Find current question
        $currentQuestion = null;
        foreach ($questions as $question) {
            if ($question['id'] === $lastQuestionId) {
                $currentQuestion = $question;
                break;
            }
        }

        if (!$currentQuestion) {
            return null;
        }

        // Determine next question ID
        $nextQuestionId = $lastAnswer === 'yes' ? 
            $currentQuestion['yes_next'] : 
            $currentQuestion['no_next'];

        // Check if we've reached a result
        if (strpos($nextQuestionId, 'result_') === 0) {
            return ['type' => 'result', 'result_id' => $nextQuestionId];
        }

        // Find and return next question
        foreach ($questions as $question) {
            if ($question['id'] === $nextQuestionId) {
                return $question;
            }
        }

        return null;
    }

    /**
     * Calculate quiz progress percentage
     */
    private function calculateProgress(array $currentAnswers, array $questions): array
    {
        $answeredCount = count($currentAnswers);
        $estimatedTotal = 8; // Average questions in Akinator flow

        return [
            'current' => $answeredCount,
            'estimated_total' => $estimatedTotal,
            'percentage' => min(100, round(($answeredCount / $estimatedTotal) * 100))
        ];
    }

    /**
     * Calculate current confidence based on answers
     */
    private function calculateCurrentConfidence(array $currentAnswers): float
    {
        $baseConfidence = 0.3;
        $incrementPerAnswer = 0.08;

        return min(0.95, $baseConfidence + (count($currentAnswers) * $incrementPerAnswer));
    }
}
?>