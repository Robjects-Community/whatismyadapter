<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\QuizSubmission;
use App\Service\Quiz\AiProductMatcherService;
use App\Service\Quiz\DecisionTreeService;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Exception;
use RuntimeException;

/**
 * API Quiz Controller
 *
 * JSON API endpoints for the AI-powered quiz system
 * Supports both Akinator-style and comprehensive quiz modes
 *
 * @property \App\Model\Table\ProductsTable $Products
 * @property \App\Model\Table\QuizSubmissionsTable $QuizSubmissions
 */
class QuizController extends AppController
{
    /**
     * AI Product Matcher Service
     *
     * @var \App\Service\Quiz\AiProductMatcherService
     */
    private AiProductMatcherService $productMatcher;

    /**
     * Decision Tree Service for Akinator mode
     *
     * @var \App\Service\Quiz\DecisionTreeService
     */
    private DecisionTreeService $decisionTree;

    /**
     * Initialize method
     */
    public function initialize(): void
    {
        parent::initialize();

        // Initialize model tables
        $this->Products = TableRegistry::getTableLocator()->get('Products');
        $this->QuizSubmissions = TableRegistry::getTableLocator()->get('QuizSubmissions');

        // JSON responses are handled via view class

        // Initialize AI services
        $this->productMatcher = new AiProductMatcherService();
        $this->decisionTree = new DecisionTreeService();

        // Set JSON view class for all actions
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * beforeFilter callback
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated access to quiz API endpoints
        $this->Authentication->addUnauthenticatedActions([
            'akinatorStart', 'akinatorNext', 'akinatorResult',
            'comprehensiveSubmit',
        ]);

        // CSRF is handled by ApiCsrfMiddleware
    }

    /**
     * Start Akinator quiz session
     *
     * POST /api/quiz/akinator/start.json
     */
    public function akinatorStart(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $context = $this->request->getData('context', []);
            $result = $this->decisionTree->start($context);

            $this->log("Akinator quiz started via API: {$result['session_id']}", 'info');

            $response = [
                'success' => true,
                'data' => $result,
            ];

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($response));
        } catch (Exception $e) {
            $this->log('Akinator start error: ' . $e->getMessage(), 'error');

            $response = [
                'success' => false,
                'error' => [
                    'code' => 'AKINATOR_START_FAILED',
                    'message' => __('Unable to start quiz session.'),
                    'details' => $this->getDebugDetails($e),
                ],
            ];

            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode($response));
        }
    }

    /**
     * Process next Akinator question
     *
     * POST /api/quiz/akinator/next.json
     */
    public function akinatorNext(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $sessionId = $this->request->getData('session_id');
            $state = $this->request->getData('state', []);
            $answer = $this->request->getData('answer');

            if (!$sessionId || !$answer) {
                throw new BadRequestException(__('Missing required parameters: session_id and answer.'));
            }

            // Ensure state has session_id
            if (empty($state['session_id'])) {
                $state['session_id'] = $sessionId;
            }

            $result = $this->decisionTree->next($state, $answer);

            $response = [
                'success' => true,
                'data' => $result,
            ];

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($response));
        } catch (BadRequestException $e) {
            $response = [
                'success' => false,
                'error' => [
                    'code' => 'INVALID_REQUEST',
                    'message' => $e->getMessage(),
                ],
            ];

            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode($response));
        } catch (Exception $e) {
            $this->log('Akinator next error: ' . $e->getMessage(), 'error');

            $response = [
                'success' => false,
                'error' => [
                    'code' => 'AKINATOR_PROCESS_FAILED',
                    'message' => __('Unable to process quiz answer.'),
                    'details' => $this->getDebugDetails($e),
                ],
            ];

            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode($response));
        }
    }

    /**
     * Get Akinator quiz result
     *
     * GET /api/quiz/akinator/result.json?session_id=xxx
     */
    public function akinatorResult(): Response
    {
        $this->request->allowMethod(['get']);

        try {
            $sessionId = $this->request->getQuery('session_id');

            if (!$sessionId) {
                throw new BadRequestException(__('Missing required parameter: session_id.'));
            }

            // Look up the submission
            $submission = $this->QuizSubmissions->find()
                ->where(['session_id' => $sessionId])
                ->orderBy(['created' => 'DESC'])
                ->first();

            if (!$submission) {
                throw new NotFoundException(__('Quiz result not found or expired.'));
            }

            // Get product details
            $productIds = $submission->matched_product_ids ?? [];
            $products = [];

            if (!empty($productIds)) {
                $products = $this->Products->find()
                    ->where(['Products.id IN' => $productIds])
                    ->contain(['Tags', 'Users'])
                    ->toArray();
            }

            $response = [
                'success' => true,
                'data' => [
                    'session_id' => $sessionId,
                    'submission_id' => $submission->id,
                    'quiz_type' => $submission->quiz_type,
                    'total_matches' => count($products),
                    'confidence_scores' => $submission->confidence_scores,
                    'recommendations' => $this->formatProductsForApi($products),
                    'completed_at' => $submission->created->toISOString(),
                ],
            ];

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($response));
        } catch (BadRequestException $e) {
            $response = [
                'success' => false,
                'error' => [
                    'code' => 'INVALID_REQUEST',
                    'message' => $e->getMessage(),
                ],
            ];

            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode($response));
        } catch (NotFoundException $e) {
            $response = [
                'success' => false,
                'error' => [
                    'code' => 'RESULT_NOT_FOUND',
                    'message' => $e->getMessage(),
                ],
            ];

            return $this->response
                ->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode($response));
        } catch (Exception $e) {
            $this->log('Akinator result error: ' . $e->getMessage(), 'error');

            $response = [
                'success' => false,
                'error' => [
                    'code' => 'RESULT_FETCH_FAILED',
                    'message' => __('Unable to retrieve quiz results.'),
                    'details' => $this->getDebugDetails($e),
                ],
            ];

            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode($response));
        }
    }

    /**
     * Submit comprehensive quiz
     *
     * POST /api/quiz/comprehensive/submit.json
     */
    public function comprehensiveSubmit(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $answers = $this->request->getData('answers', []);
            $sessionId = $this->request->getData('session_id') ?: Text::uuid();

            if (empty($answers)) {
                throw new BadRequestException(__('No quiz answers provided.'));
            }

            // Validate answers structure
            $this->validateAnswers($answers);

            // Get product recommendations using AI matcher
            $results = $this->productMatcher->match($answers, [
                'max_results' => $this->request->getData('max_results', 10),
                'confidence_threshold' => $this->request->getData('confidence_threshold', 0.6),
            ]);

            // Save submission
            $submission = $this->saveQuizSubmission($sessionId, 'comprehensive', $answers, $results);

            $response = [
                'success' => true,
                'data' => [
                    'session_id' => $sessionId,
                    'submission_id' => $submission->id,
                    'total_matches' => $results['total_matches'],
                    'overall_confidence' => $results['overall_confidence'],
                    'processing_time' => $results['processing_time'],
                    'method' => $results['method'],
                    'recommendations' => $this->formatRecommendationsForApi($results['products']),
                    'submitted_at' => $submission->created->toISOString(),
                ],
            ];

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($response));
        } catch (BadRequestException $e) {
            $response = [
                'success' => false,
                'error' => [
                    'code' => 'INVALID_REQUEST',
                    'message' => $e->getMessage(),
                ],
            ];

            return $this->response
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode($response));
        } catch (Exception $e) {
            $this->log('Comprehensive submit error: ' . $e->getMessage(), 'error');

            $response = [
                'success' => false,
                'error' => [
                    'code' => 'SUBMIT_FAILED',
                    'message' => __('Unable to process quiz submission.'),
                    'details' => $this->getDebugDetails($e),
                ],
            ];

            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode($response));
        }
    }

    /**
     * Validate quiz answers structure
     *
     * @param array $answers Quiz answers
     * @throws \Cake\Http\Exception\BadRequestException If validation fails
     */
    private function validateAnswers(array $answers): void
    {
        $requiredFields = ['device_type'];

        foreach ($requiredFields as $field) {
            if (empty($answers[$field])) {
                throw new BadRequestException(__('Missing required answer: {0}', $field));
            }
        }

        // Validate device_type is from allowed values
        $allowedDeviceTypes = ['laptop', 'phone', 'tablet', 'gaming', 'other'];
        if (!in_array($answers['device_type'], $allowedDeviceTypes)) {
            throw new BadRequestException(__('Invalid device type: {0}', $answers['device_type']));
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
    private function saveQuizSubmission(
        string $sessionId,
        string $quizType,
        array $answers,
        array $results,
    ): QuizSubmission {
        $identity = $this->getRequest()->getAttribute('identity');

        $submission = $this->QuizSubmissions->newEntity([
            'user_id' => $identity ? $identity->id : null,
            'session_id' => $sessionId,
            'quiz_type' => $quizType,
            'answers' => $answers,
            'matched_product_ids' => array_map(function ($p) {
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
                $results['method'],
            ),
            'analytics' => [
                'user_agent' => $this->request->getHeaderLine('User-Agent'),
                'ip_address' => $this->request->clientIp(),
                'timestamp' => time(),
                'processing_time' => $results['processing_time'],
                'method' => $results['method'],
                'api_request' => true,
            ],
        ]);

        if (!$this->QuizSubmissions->save($submission)) {
            throw new RuntimeException('Failed to save quiz submission');
        }

        return $submission;
    }

    /**
     * Format product recommendations for API response
     *
     * @param array $products Raw product recommendations from AI matcher
     * @return array Formatted recommendations
     */
    private function formatRecommendationsForApi(array $products): array
    {
        $formatted = [];

        foreach ($products as $item) {
            $product = $item['product'];

            $formatted[] = [
                'product' => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'manufacturer' => $product->manufacturer,
                    'price' => $product->price,
                    'currency' => $product->currency ?? 'USD',
                    'formatted_price' => $product->price ? '$' . number_format($product->price, 2) : null,
                    'image_url' => $product->image ?? null,
                    'rating' => $product->numeric_rating,
                    'certified' => (bool)$product->is_certified,
                    'device_category' => $product->device_category,
                    'port_family' => $product->port_family,
                    'url' => $this->Url->build([
                        'controller' => 'Products',
                        'action' => 'view',
                        $product->id,
                    ]),
                ],
                'confidence_score' => $item['confidence_score'],
                'rule_based_score' => $item['rule_based_score'] ?? null,
                'ai_score' => $item['ai_score'] ?? null,
                'explanation' => $item['explanation'] ?? '',
                'match_reasons' => $item['match_reasons'] ?? [],
            ];
        }

        return $formatted;
    }

    /**
     * Format products for API response (simpler format)
     *
     * @param array $products Product entities
     * @return array Formatted products
     */
    private function formatProductsForApi(array $products): array
    {
        $formatted = [];

        foreach ($products as $product) {
            $formatted[] = [
                'id' => $product->id,
                'title' => $product->title,
                'manufacturer' => $product->manufacturer,
                'price' => $product->price,
                'currency' => $product->currency ?? 'USD',
                'formatted_price' => $product->price ? '$' . number_format($product->price, 2) : null,
                'image_url' => $product->image ?? null,
                'rating' => $product->numeric_rating,
                'certified' => (bool)$product->is_certified,
                'device_category' => $product->device_category,
                'port_family' => $product->port_family,
                'url' => $this->Url->build([
                    'controller' => 'Products',
                    'action' => 'view',
                    $product->id,
                ]),
            ];
        }

        return $formatted;
    }

    /**
     * Get debug details for error responses
     *
     * @param \Exception $e Exception
     * @return array|null Debug details or null if debug is disabled
     */
    private function getDebugDetails(Exception $e): ?array
    {
        if (!Configure::read('debug')) {
            return null;
        }

        return [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => array_slice($e->getTrace(), 0, 5), // Limit trace depth
        ];
    }
}
