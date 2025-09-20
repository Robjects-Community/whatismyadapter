<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Product;
use App\Service\ProductFormFieldService;
use App\Service\ReliabilityService;
use Cake\Cache\Cache;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Products Controller - Frontend
 *
 * @property \App\Model\Table\ProductsTable $Products
 */
class ProductsController extends AppController
{
    /**
     * beforeFilter callback.
     *
     * Allow unauthenticated access to certain public actions
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated access to index, view, and quiz actions
        $this->Authentication->addUnauthenticatedActions(['index', 'view', 'quiz']);
    }

    /**
     * Index method - Browse published products
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        // Get only published products for public viewing
        $query = $this->Products->getPublishedProducts();

        // Search functionality
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query = $this->Products->searchProducts($search);
        }

        // Filtering options
        $manufacturer = $this->request->getQuery('manufacturer');
        if (!empty($manufacturer)) {
            $query->where(['Products.manufacturer LIKE' => '%' . $manufacturer . '%']);
        }

        $tag = $this->request->getQuery('tag');
        if (!empty($tag)) {
            $query->matching('Tags', function ($q) use ($tag) {
                return $q->where(['Tags.slug' => $tag]);
            });
        }

        $featured = $this->request->getQuery('featured');
        if ($featured) {
            $query->where(['Products.featured' => true]);
        }

        $products = $this->paginate($query);

        // Get filter options for the sidebar
        $manufacturers = $this->Products->find()
            ->select(['manufacturer'])
            ->where([
                'manufacturer IS NOT' => null,
                'manufacturer !=' => '',
                'is_published' => true,
            ])
            ->groupBy(['manufacturer'])
            ->orderBy(['manufacturer' => 'ASC'])
            ->toArray();

        $tags = $this->Products->Tags->find()
            ->matching('Products', function ($q) {
                return $q->where(['Products.is_published' => true]);
            })
            ->orderBy(['Tags.title' => 'ASC'])
            ->limit(20)
            ->toArray();

        if ($this->request->is('ajax')) {
            $this->set(compact('products', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $this->set(compact('products', 'manufacturers', 'tags', 'search', 'manufacturer', 'tag', 'featured'));

        return null;
    }

    /**
     * View method - Display single product details
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): ?Response
    {
        $product = $this->Products->get($id, contain: ['Users', 'Articles', 'Tags']);

        // Only show published products to public
        if (!$product->is_published) {
            throw new NotFoundException(__('Product not found.'));
        }

        // Increment view count
        $this->Products->incrementViewCount($id);
        // Get related products
        $relatedProducts = $this->Products->getRelatedProducts($id, 4);

        $this->set(compact('product', 'relatedProducts'));

        return null;
    }

    /**
     * Quiz method - Interactive adapter finder with reliability scoring
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function quiz(): ?Response
    {
        // Get quiz settings
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');

        $quizEnabled = $settingsTable->find()
            ->where(['category' => 'Products', 'key_name' => 'quiz_enabled'])
            ->first();

        $quizConfig = $settingsTable->find()
            ->where(['category' => 'Products', 'key_name' => 'quiz_config_json'])
            ->first();

        // Enable quiz by default if not configured
        if (!$quizEnabled || $quizEnabled->value !== '1') {
            // For this implementation, we'll use a hardcoded quiz structure
            // In production, this would be configured via admin settings
        }

        $config = [];
        if ($quizConfig && !empty($quizConfig->value)) {
            $config = json_decode($quizConfig->value, true) ?: [];
        }

        // If no config exists, create a default product form quiz
        if (empty($config['questions'])) {
            $config = $this->getDefaultQuizConfig();
        }

        $quizQuestions = $config['questions'] ?? [];

        // Handle quiz submission
        if ($this->request->is('post')) {
            if ($this->request->is('ajax')) {
                $answers = $this->request->getData('answers', []);
                $recommendations = $this->processQuizAnswers($answers, $config);

                // Convert answers to product data for reliability scoring
                $productData = $this->convertAnswersToProductData($answers, $config);

                // Get reliability scoring using our new service
                $reliabilityService = new ReliabilityService();
                $scoringResult = $reliabilityService->computeProvisionalScore('Products', $productData);

                $response = [
                    'success' => true,
                    'resultsHtml' => $this->renderQuizResults($recommendations, $scoringResult),
                    'reliability' => $scoringResult,
                ];

                $this->response = $this->response->withType('application/json');
                $this->set('_serialize', ['response']);
                $this->set(compact('response'));

                return null;
            } else {
                $answers = $this->request->getData('answers', []);
                $recommendations = $this->processQuizAnswers($answers, $config);

                $this->set(compact('config', 'answers', 'recommendations'));

                return $this->render('quiz_results');
            }
        }

        $this->set(compact('config', 'quizQuestions'));

        return null;
    }

    /**
     * Edit method - Admin-style edit for products in frontend scope
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null Renders view or redirects
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $product = $this->Products->get($id, contain: ['Users', 'Articles', 'Tags']);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags'],
            ]);

            if ($this->Products->save($product)) {
                $this->Flash->success(__('The product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        // Prepare lists for form selects
        $users = $this->Products->Users->find('list')->toArray();
        $articles = $this->Products->Articles->find('list')->toArray();
        $tags = $this->Products->Tags->find('list')->toArray();

        $this->set(compact('product', 'users', 'articles', 'tags'));

        return null;
    }

    /**
     * Delete method - Remove a product (POST/DELETE only)
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null Redirects to index
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $product = $this->Products->get($id);
        if ($this->Products->delete($product)) {
            $this->Flash->success(__('The product has been deleted.'));
        } else {
            $this->Flash->error(__('The product could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Add method - User product submission form with dynamic fields and AI suggestions
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        // Check if public submissions are enabled
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');

        $publicSubmissionsEnabled = $settingsTable->find()
            ->where(['category' => 'Products', 'key_name' => 'enable_public_submissions'])
            ->first();

        if (!$publicSubmissionsEnabled || $publicSubmissionsEnabled->value !== '1') {
            $this->Flash->error(__('Public product submissions are currently not enabled.'));

            return $this->redirect(['action' => 'index']);
        }

        // Get dynamic form configuration
        $formFieldService = new ProductFormFieldService();
        $formFields = $formFieldService->getActiveFormFields();
        $fieldGroups = $formFieldService->getFieldGroups();
        $formSettings = $this->getProductFormSettings();

        $product = $this->Products->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // Set user ID and submission defaults
            $identity = $this->getRequest()->getAttribute('identity');
            $data['user_id'] = $identity ? $identity->id : null;
            $data['is_published'] = false; // Always start as unpublished
            $data['verification_status'] = $formSettings['default_status'] ?? 'pending';
            $data['featured'] = false;

            // Validate using dynamic form field service
            $validationErrors = $formFieldService->validateFormData($data);

            if (!empty($validationErrors)) {
                foreach ($validationErrors as $errors) {
                    foreach ($errors as $error) {
                        $this->Flash->error($error);
                    }
                }
            } else {
                $product = $this->Products->patchEntity($product, $data, [
                    'associated' => ['Tags'],
                ]);

                // Handle image upload
                $imageUploads = $this->request->getUploadedFiles('image_uploads');
                if (!empty($imageUploads['image_uploads'])) {
                    $product->imageUploads = $imageUploads['image_uploads'];
                }

                if ($this->Products->save($product)) {
                    $this->clearContentCache();

                    $successMessage = $formSettings['success_message'] ??
                        'Your product has been submitted and is awaiting review.';
                    $this->Flash->success(__($successMessage));

                    // Send notification email if configured
                    $this->sendSubmissionNotification($product, $formSettings);

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The product could not be saved. Please, try again.'));
            }
        }

        // Get form options
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();

        $this->set(compact('product', 'tags', 'formSettings', 'formFields', 'fieldGroups'));

        // Use dynamic template if available, fall back to static
        if ($this->viewBuilder()->getTemplatePath() === null) {
            try {
                return $this->render('add-dynamic');
            } catch (Exception $e) {
                // Fall back to static form if dynamic template not found
                return $this->render('add');
            }
        }

        return null;
    }

    /**
     * Get product form settings from database
     */
    private function getProductFormSettings(): array
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');

        $settings = $settingsTable->find()
            ->where(['category' => 'Products'])
            ->toArray();

        $formSettings = [];
        foreach ($settings as $setting) {
            $formSettings[$setting->key_name] = $setting->value;
        }

        return $formSettings;
    }

    /**
     * Process quiz answers and return product recommendations
     */
    private function processQuizAnswers(array $answers, array $config): array
    {
        if (empty($config['questions']) || empty($config['scoring'])) {
            return [];
        }

        $scores = [];
        $reasoning = [];

        // Process each answer against scoring rules
        foreach ($answers as $answer) {
            if (isset($config['scoring'][$answer])) {
                $rules = $config['scoring'][$answer];
                foreach ($rules as $rule) {
                    if (strpos($rule, 'tag:') === 0) {
                        $tagSlug = substr($rule, 4);
                        $scores['tags'][] = $tagSlug;
                        $reasoning[] = "Based on your selection, we recommend products tagged with '{$tagSlug}'";
                    } elseif (strpos($rule, 'manufacturer:') === 0) {
                        $manufacturer = substr($rule, 13);
                        $scores['manufacturers'][] = $manufacturer;
                        $reasoning[] = "Your needs align with products from {$manufacturer}";
                    }
                }
            }
        }

        // Find matching products
        $query = $this->Products->getPublishedProducts();

        if (!empty($scores['tags'])) {
            $query->matching('Tags', function ($q) use ($scores) {
                return $q->where(['Tags.slug IN' => $scores['tags']]);
            });
        }

        if (!empty($scores['manufacturers'])) {
            $manufacturerConditions = [];
            foreach ($scores['manufacturers'] as $manufacturer) {
                $manufacturerConditions[] = ['Products.manufacturer LIKE' => '%' . $manufacturer . '%'];
            }
            $query->where(['OR' => $manufacturerConditions]);
        }

        $products = $query->limit(10)->toArray();

        return [
            'products' => $products,
            'reasoning' => $reasoning,
            'scores' => $scores,
        ];
    }

    /**
     * Send notification email about new product submission
     *
     * @param \App\Model\Entity\Product $product The product entity
     * @param array $formSettings Form configuration settings
     * @return void
     */
    private function sendSubmissionNotification(Product $product, array $formSettings): void
    {
        $notificationEmail = $formSettings['notification_email'] ?? '';

        if (empty($notificationEmail) || $notificationEmail === '0') {
            return;
        }

        // In a real implementation, you would send an email here
        // For now, just log it
        $this->log(sprintf(
            'New product submission: "%s" by user %s (ID: %s). Notification should be sent to: %s',
            $product->title,
            $product->user->username ?? 'Unknown',
            $product->user_id ?? 'Unknown',
            $notificationEmail,
        ), 'info');
    }

    /**
     * Clear content cache after operations
     */
    private function clearContentCache(): void
    {
        Cache::clear('content');
    }

    /**
     * Get default quiz configuration for product form assistance
     */
    private function getDefaultQuizConfig(): array
    {
        return [
            'questions' => [
                [
                    'question' => 'What type of product are you looking to add?',
                    'type' => 'radio',
                    'options' => [
                        'network_adapter' => 'Network Adapter',
                        'usb_adapter' => 'USB Adapter',
                        'power_adapter' => 'Power Adapter',
                        'display_adapter' => 'Display/Video Adapter',
                        'audio_adapter' => 'Audio Adapter',
                        'storage_adapter' => 'Storage Adapter',
                        'other' => 'Other',
                    ],
                ],
                [
                    'question' => 'What is the primary use case for this product?',
                    'type' => 'radio',
                    'options' => [
                        'enterprise' => 'Enterprise/Business',
                        'consumer' => 'Consumer/Home Use',
                        'gaming' => 'Gaming',
                        'industrial' => 'Industrial/Professional',
                        'educational' => 'Educational',
                    ],
                ],
                [
                    'question' => 'What performance level does this product target?',
                    'type' => 'radio',
                    'options' => [
                        'entry' => 'Entry Level',
                        'mainstream' => 'Mainstream',
                        'high_performance' => 'High Performance',
                        'professional' => 'Professional/Server Grade',
                    ],
                ],
                [
                    'question' => 'Do you have technical specifications available?',
                    'type' => 'radio',
                    'options' => [
                        'detailed' => 'Yes, detailed technical specs',
                        'basic' => 'Yes, basic specifications',
                        'limited' => 'Limited information',
                        'none' => 'No technical specs available',
                    ],
                ],
                [
                    'question' => 'Is this product certified or tested to industry standards?',
                    'type' => 'radio',
                    'options' => [
                        'certified' => 'Yes, certified (IEEE, ANSI, ISO, etc.)',
                        'tested' => 'Yes, tested but not certified',
                        'unknown' => 'Unknown/Not sure',
                        'no_testing' => 'No testing information',
                    ],
                ],
            ],
            'scoring' => [
                'network_adapter' => ['tag:networking', 'manufacturer:Intel'],
                'enterprise' => ['tag:enterprise', 'tag:business'],
                'high_performance' => ['tag:performance'],
                'certified' => ['tag:certified'],
            ],
        ];
    }

    /**
     * Convert quiz answers to product data for reliability scoring
     */
    private function convertAnswersToProductData(array $answers, array $config): array
    {
        $productData = [
            'title' => '',
            'description' => '',
            'manufacturer' => '',
            'model_number' => '',
            'price' => null,
            'currency' => 'USD',
            'technical_specifications' => '',
            'testing_standard' => '',
            'certifying_organization' => '',
            'numeric_rating' => null,
            'is_certified' => false,
            'image' => '',
            'alt_text' => '',
        ];

        // Map quiz answers to product fields
        foreach ($answers as $questionIndex => $answer) {
            switch ($questionIndex) {
                case 0: // Product type
                    $productData['title'] = $this->getProductTitleFromType($answer);
                    $productData['description'] = $this->getDescriptionFromType($answer);
                    break;

                case 1: // Use case
                    $productData['description'] .= ' ' . $this->getUseCaseDescription($answer);
                    break;

                case 2: // Performance level
                    $productData['manufacturer'] = $this->getManufacturerFromPerformance($answer);
                    $productData['numeric_rating'] = $this->getRatingFromPerformance($answer);
                    break;

                case 3: // Technical specifications
                    if ($answer === 'detailed' || $answer === 'basic') {
                        $productData['technical_specifications'] = $this->getDefaultTechnicalSpecs($answer);
                    }
                    break;

                case 4: // Certification
                    if ($answer === 'certified') {
                        $productData['is_certified'] = true;
                        $productData['testing_standard'] = 'IEEE 802.3';
                        $productData['certifying_organization'] = 'IEEE';
                    } elseif ($answer === 'tested') {
                        $productData['testing_standard'] = 'Internal Testing';
                    }
                    break;
            }
        }

        return array_filter($productData, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Generate product title from type
     */
    private function getProductTitleFromType(string $type): string
    {
        $titles = [
            'network_adapter' => 'Professional Network Adapter',
            'usb_adapter' => 'USB Connectivity Adapter',
            'power_adapter' => 'Power Supply Adapter',
            'display_adapter' => 'Display Connector Adapter',
            'audio_adapter' => 'Audio Interface Adapter',
            'storage_adapter' => 'Storage Connection Adapter',
            'other' => 'Specialized Adapter',
        ];

        return $titles[$type] ?? 'Professional Adapter';
    }

    /**
     * Generate description from product type
     */
    private function getDescriptionFromType(string $type): string
    {
        $descriptions = [
            'network_adapter' => 'High-performance network connectivity solution',
            'usb_adapter' => 'Reliable USB connectivity and data transfer adapter',
            'power_adapter' => 'Efficient power conversion and supply adapter',
            'display_adapter' => 'Professional display connectivity solution',
            'audio_adapter' => 'High-quality audio interface adapter',
            'storage_adapter' => 'Fast and reliable storage connectivity adapter',
            'other' => 'Specialized connectivity and interface solution',
        ];

        return $descriptions[$type] ?? 'Professional adapter solution';
    }

    /**
     * Get use case description
     */
    private function getUseCaseDescription(string $useCase): string
    {
        $descriptions = [
            'enterprise' => 'designed for enterprise and business environments.',
            'consumer' => 'optimized for home and consumer use.',
            'gaming' => 'engineered for gaming and high-performance applications.',
            'industrial' => 'built for industrial and professional applications.',
            'educational' => 'designed for educational and institutional use.',
        ];

        return $descriptions[$useCase] ?? '';
    }

    /**
     * Get manufacturer based on performance level
     */
    private function getManufacturerFromPerformance(string $performance): string
    {
        $manufacturers = [
            'entry' => 'Generic Manufacturer',
            'mainstream' => 'TechCorp',
            'high_performance' => 'Intel Corporation',
            'professional' => 'Intel Corporation',
        ];

        return $manufacturers[$performance] ?? 'TechCorp';
    }

    /**
     * Get rating based on performance level
     */
    private function getRatingFromPerformance(string $performance): ?float
    {
        $ratings = [
            'entry' => 3.5,
            'mainstream' => 4.0,
            'high_performance' => 4.5,
            'professional' => 4.8,
        ];

        return $ratings[$performance] ?? null;
    }

    /**
     * Get default technical specifications
     */
    private function getDefaultTechnicalSpecs(string $level): string
    {
        if ($level === 'detailed') {
            return json_encode([
                'interface' => 'PCIe 3.0',
                'speed' => '1Gbps',
                'ports' => 1,
                'power' => '5W',
                'temperature' => '0-70°C',
            ]);
        } elseif ($level === 'basic') {
            return json_encode([
                'interface' => 'Standard',
                'speed' => 'Standard',
            ]);
        }

        return '';
    }

    /**
     * Render quiz results with reliability scoring
     */
    private function renderQuizResults(array $recommendations, array $scoringResult): string
    {
        $html = '<div class="quiz-results-content">';

        // Reliability Score Section
        $score = round($scoringResult['total_score'] * 100, 1);
        $completeness = round($scoringResult['completeness_percent'], 1);
        $severity = $scoringResult['ui']['severity'];

        $html .= '<div class="reliability-summary mb-4">';
        $html .= '<div class="card border-' . $this->getSeverityBootstrapClass($severity) . '">';
        $html .= '<div class="card-header bg-' . $this->getSeverityBootstrapClass($severity) . ' text-white">';
        $html .= '<h5 class="mb-0"><i class="fas fa-chart-line"></i> Product Information Quality</h5>';
        $html .= '</div>';
        $html .= '<div class="card-body">';
        $html .= '<div class="row">';
        $html .= '<div class="col-md-6">';
        $html .= '<div class="text-center">';
        $html .= '<div class="h2 text-' . $this->getSeverityBootstrapClass($severity) . '">' . $score . '%</div>';
        $html .= '<p class="text-muted">Reliability Score</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="col-md-6">';
        $html .= '<div class="text-center">';
        $html .= '<div class="h2 text-info">' . $completeness . '%</div>';
        $html .= '<p class="text-muted">Information Complete</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Suggestions
        if (!empty($scoringResult['suggestions'])) {
            $html .= '<hr>';
            $html .= '<h6><i class="fas fa-lightbulb"></i> Recommendations to Improve:</h6>';
            $html .= '<ul class="list-unstyled">';
            foreach ($scoringResult['suggestions'] as $suggestion) {
                $html .= '<li class="mb-2"><i class="fas fa-arrow-right text-primary"></i> ' . h($suggestion) . '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Product recommendations
        if (!empty($recommendations['products'])) {
            $html .= '<div class="recommended-products">';
            $html .= '<h4 class="mb-3"><i class="fas fa-star"></i> Recommended Products</h4>';
            $html .= '<div class="row">';

            foreach (array_slice($recommendations['products'], 0, 6) as $product) {
                $html .= '<div class="col-md-6 col-lg-4 mb-3">';
                $html .= '<div class="card h-100">';
                if (!empty($product->image)) {
                    $altText = h($product->alt_text ?: $product->title);
                    $html .= '<img src="' . h($product->image) . '" class="card-img-top" '
                        . 'alt="' . $altText . '" style="height: 150px; object-fit: cover;">';
                }
                $html .= '<div class="card-body">';
                $html .= '<h6 class="card-title">' . h($product->title) . '</h6>';
                $html .= '<p class="card-text text-muted small">'
                    . $this->truncate(h($product->description), 80) . '</p>';
                if (!empty($product->manufacturer)) {
                    $html .= '<p class="mb-1 small"><strong>Manufacturer:</strong> '
                        . h($product->manufacturer) . '</p>';
                }
                if (!empty($product->price)) {
                    $html .= '<p class="mb-2"><strong class="text-success">$'
                        . number_format($product->price, 2) . '</strong></p>';
                }
                $html .= '</div>';
                $html .= '<div class="card-footer">';
                $html .= '<a href="/products/view/' . $product->id . '" '
                    . 'class="btn btn-primary btn-sm">View Details</a>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
            }

            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div class="no-results text-center py-4">';
            $html .= '<i class="fas fa-search fa-3x text-muted mb-3"></i>';
            $html .= '<h5>No exact matches found</h5>';
            $html .= '<p class="text-muted">Based on your answers, we couldn\'t find specific '
                . 'matching products, but you can browse our full catalog.</p>';
            $html .= '<a href="/products" class="btn btn-primary">Browse All Products</a>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get Bootstrap class for severity
     */
    private function getSeverityBootstrapClass(string $severity): string
    {
        $classes = [
            'success' => 'success',
            'warning' => 'warning',
            'info' => 'info',
            'danger' => 'danger',
        ];

        return $classes[$severity] ?? 'secondary';
    }

    /**
     * Truncate text helper
     */
    private function truncate(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }
}
