<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\Product;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;

class ProductsController extends AppController
{
    /**
     * Dashboard method - Product overview and statistics
     *
     * @return void
     */
    public function dashboard(): void
    {
        // Basic statistics
        $totalProducts = $this->Products->find()->count();
        $publishedProducts = $this->Products->find()->where(['is_published' => true])->count();
        $pendingProducts = $this->Products->find()->where(['verification_status' => 'pending'])->count();
        $approvedProducts = $this->Products->find()->where(['verification_status' => 'approved'])->count();
        $featuredProducts = $this->Products->find()->where(['featured' => true])->count();

        // Recent products
        $recentProducts = $this->Products->find()
            ->contain(['Users', 'Tags'])
            ->orderBy(['Products.created' => 'DESC'])
            ->limit(10)
            ->toArray();

        // Top manufacturers
        $topManufacturers = $this->Products->find()
            ->select([
                'manufacturer',
                'count' => $this->Products->find()->func()->count('*'),
            ])
            ->where(['manufacturer IS NOT' => null, 'manufacturer !=' => ''])
            ->groupBy('manufacturer')
            ->orderBy(['count' => 'DESC'])
            ->limit(10)
            ->toArray();

        // Popular tags
        $popularTags = $this->Products->Tags->find()
            ->select([
                'Tags.title',
                'count' => $this->Products->Tags->find()->func()->count('ProductsTags.product_id'),
            ])
            ->leftJoinWith('Products')
            ->groupBy('Tags.id')
            ->orderBy(['count' => 'DESC'])
            ->limit(10)
            ->toArray();

        $this->set(compact(
            'totalProducts',
            'publishedProducts',
            'pendingProducts',
            'approvedProducts',
            'featuredProducts',
            'recentProducts',
            'topManufacturers',
            'popularTags',
        ));
    }

    /**
     * Index method - Product listing with search and filtering
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $query = $this->Products->find()
            ->select([
                'Products.id',
                'Products.user_id',
                'Products.title',
                'Products.slug',
                'Products.description',
                'Products.manufacturer',
                'Products.model_number',
                'Products.price',
                'Products.currency',
                'Products.image',
                'Products.alt_text',
                'Products.is_published',
                'Products.featured',
                'Products.verification_status',
                'Products.reliability_score',
                'Products.view_count',
                'Products.created',
                'Products.modified',
                'Users.id',
                'Users.username',
            ])
            ->leftJoinWith('Users')
            ->groupBy([
                'Products.id',
                'Products.user_id',
                'Products.title',
                'Products.slug',
                'Products.created',
                'Products.modified',
                'Users.id',
                'Users.username',
            ])
            ->orderBy(['Products.created' => 'DESC']);

        // Status filtering
        if ($statusFilter !== null) {
            if ($statusFilter === 'published') {
                $query->where(['Products.is_published' => true]);
            } elseif ($statusFilter === 'unpublished') {
                $query->where(['Products.is_published' => false]);
            } elseif (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
                $query->where(['Products.verification_status' => $statusFilter]);
            }
        }

        // Search functionality
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Products.title LIKE' => '%' . $search . '%',
                    'Products.description LIKE' => '%' . $search . '%',
                    'Products.manufacturer LIKE' => '%' . $search . '%',
                    'Products.model_number LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        // Featured filter
        if ($this->request->getQuery('featured')) {
            $query->where(['Products.featured' => true]);
        }

        $products = $this->paginate($query);

        if ($this->request->is('ajax')) {
            $this->set(compact('products', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $this->set(compact('products'));

        return null;
    }

    /**
     * Pending Review method - List products awaiting AI processing
     *
     * Lists products with verification_status = 'pending' which were manually
     * submitted (user_id IS NOT NULL) and awaiting AI processing.
     *
     * @return \Cake\Http\Response|null
     */
    public function pendingReview(): ?Response
    {
        // Base query for pending products with manual submission
        $query = $this->Products->find()
            ->select([
                'Products.id',
                'Products.user_id',
                'Products.title',
                'Products.slug',
                'Products.description',
                'Products.manufacturer',
                'Products.model_number',
                'Products.price',
                'Products.currency',
                'Products.image',
                'Products.alt_text',
                'Products.is_published',
                'Products.featured',
                'Products.verification_status',
                'Products.reliability_score',
                'Products.view_count',
                'Products.created',
                'Products.modified',
                'Users.id',
                'Users.username',
            ])
            ->leftJoinWith('Users')
            ->where(['Products.verification_status' => 'pending'])
            ->andWhere(['Products.user_id IS NOT' => null])
            ->groupBy([
                'Products.id',
                'Products.user_id',
                'Products.title',
                'Products.slug',
                'Products.created',
                'Products.modified',
                'Users.id',
                'Users.username',
            ])
            ->orderBy(['Products.created' => 'DESC']);

        // Search functionality
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Products.title LIKE' => '%' . $search . '%',
                    'Products.description LIKE' => '%' . $search . '%',
                    'Products.manufacturer LIKE' => '%' . $search . '%',
                    'Products.model_number LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        // Featured filter
        if ($this->request->getQuery('featured')) {
            $query->where(['Products.featured' => true]);
        }

        // Manufacturer filter
        $manufacturer = $this->request->getQuery('manufacturer');
        if (!empty($manufacturer)) {
            $query->where(['Products.manufacturer LIKE' => '%' . $manufacturer . '%']);
        }

        // User filter
        $userId = $this->request->getQuery('user_id');
        if (!empty($userId)) {
            $query->where(['Products.user_id' => $userId]);
        }

        // Date range filters
        $dateFrom = $this->request->getQuery('date_from');
        if (!empty($dateFrom)) {
            $query->where(['Products.created >=' => $dateFrom]);
        }

        $dateTo = $this->request->getQuery('date_to');
        if (!empty($dateTo)) {
            $query->where(['Products.created <=' => $dateTo]);
        }

        $products = $this->paginate($query);

        // Get users list for filter dropdown
        $usersList = $this->Products->Users->find('list', [
            'keyField' => 'id',
            'valueField' => 'username',
        ])->where(['active' => 1])->toArray();

        // Handle AJAX requests
        if ($this->request->is('ajax')) {
            $this->set(compact('products', 'search', 'manufacturer', 'userId', 'dateFrom', 'dateTo'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('pending_review_results');
        }

        $this->set(compact('products', 'usersList', 'search', 'manufacturer', 'userId', 'dateFrom', 'dateTo'));

        return null;
    }

    /**
     * Index2 method - Product listing with search and filtering
     *
     * @return \Cake\Http\Response|null
     */
    public function index2(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $query = $this->Products->find()
            ->select([
                'Products.id',
                'Products.user_id',
                'Products.title',
                'Products.slug',
                'Products.description',
                'Products.manufacturer',
                'Products.model_number',
                'Products.price',
                'Products.currency',
                'Products.image',
                'Products.alt_text',
                'Products.is_published',
                'Products.featured',
                'Products.verification_status',
                'Products.reliability_score',
                'Products.view_count',
                'Products.created',
                'Products.modified',
                'Users.id',
                'Users.username',
            ])
            ->leftJoinWith('Users')
            ->groupBy([
                'Products.id',
                'Products.user_id',
                'Products.title',
                'Products.slug',
                'Products.created',
                'Products.modified',
                'Users.id',
                'Users.username',
            ])
            ->orderBy(['Products.created' => 'DESC']);

        // Status filtering
        if ($statusFilter !== null) {
            if ($statusFilter === 'published') {
                $query->where(['Products.is_published' => true]);
            } elseif ($statusFilter === 'unpublished') {
                $query->where(['Products.is_published' => false]);
            } elseif (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
                $query->where(['Products.verification_status' => $statusFilter]);
            }
        }

        // Search functionality
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Products.title LIKE' => '%' . $search . '%',
                    'Products.description LIKE' => '%' . $search . '%',
                    'Products.manufacturer LIKE' => '%' . $search . '%',
                    'Products.model_number LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        // Featured filter
        if ($this->request->getQuery('featured')) {
            $query->where(['Products.featured' => true]);
        }

        $products = $this->paginate($query);

        if ($this->request->is('ajax')) {
            $this->set(compact('products', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $this->set(compact('products'));

        return null;
    }

    /**
     * View method - Display single product details
     *
     * @param string|null $id Product id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $product = $this->Products->get($id, [
            'contain' => [
                'Users',
                'Tags',
                'Articles',
            ],
        ]);

        if (!$product) {
            throw new RecordNotFoundException(__('Product not found'));
        }

        // Get related products
        $relatedProducts = $this->Products->getRelatedProducts($id, 5);

        $this->set(compact('product', 'relatedProducts'));
    }

    /**
     * View2 method - Display single product details (copy)
     *
     * @param string|null $id Product id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view2(?string $id = null): void
    {
        $product = $this->Products->get($id, [
            'contain' => [
                'Users',
                'Tags',
                'Articles',
            ],
        ]);

        if (!$product) {
            throw new RecordNotFoundException(__('Product not found'));
        }

        // Get related products
        $relatedProducts = $this->Products->getRelatedProducts($id, 5);

        $this->set(compact('product', 'relatedProducts'));
    }

    /**
     * Add2 method - Create new product (copy)
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add2(): ?Response
    {
        $product = $this->Products->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $this->getRequest()->getAttribute('identity')->id;

            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags'],
            ]);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads');
            if (!empty($imageUploads['image_uploads'])) {
                $product->imageUploads = $imageUploads['image_uploads'];
            }

            if ($this->Products->save($product)) {
                $this->clearContentCache();
                $this->Flash->success(__('The product has been saved.'));

                // Queue verification job
                $this->queueJob('ProductVerificationJob', [
                    'product_id' => $product->id,
                ]);

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        // Get form options
        $users = $this->Products->Users->find('list', ['limit' => 200])->all();
        $articles = $this->Products->Articles
            ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
            ->where(['is_published' => true])
            ->orderBy(['title' => 'ASC']);
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();
        $token = $this->request->getAttribute('csrfToken');

        $this->set(compact('product', 'users', 'articles', 'tags', 'token'));

        return null;
    }

    /**
     * Edit2 method - Modify existing product (copy)
     *
     * @param string|null $id Product ID.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit2(?string $id = null): ?Response
    {
        $product = $this->Products->get($id, [
            'contain' => ['Tags'],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            // TODO: create a reliability score
            // $originalScore = $product->reliability_score;


            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags'],
            ]);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads') ?? [];
            if (!empty($imageUploads['image_uploads'])) {
                $product->imageUploads = $imageUploads['image_uploads'];
            }

            // Handle image unlinking
            $unlinkedImages = $this->request->getData('unlink_images') ?? [];
            $product->unlinkedImages = $unlinkedImages;

            $saveOptions = [];
            if (isset($data['regenerateTags'])) {
                $saveOptions['regenerateTags'] = $data['regenerateTags'];
            }

            if ($this->Products->save($product, $saveOptions)) {
                $this->clearContentCache();
                $this->Flash->success(__('The product has been saved.'));

                // Re-verify if significant changes were made
                if ($this->hasSignificantChanges($product)) {
                    $this->queueJob('ProductVerificationJob', [
                        'product_id' => $product->id,
                    ]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        $users = $this->Products->Users->find('list', ['limit' => 200])->all();
        $articles = $this->Products->Articles
            ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
            ->where(['is_published' => true])
            ->orderby(['title' => 'ASC']);
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();

        $this->set(compact('product', 'users', 'articles', 'tags'));

        return null;
    }

    /**
     * Add Beautiful method - Create new product with beautiful AI-powered form
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function addBeautiful(): ?Response
    {
        $product = $this->Products->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $this->getRequest()->getAttribute('identity')->id;
            $data['verification_status'] = 'pending';

            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags'],
            ]);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads');
            if (!empty($imageUploads['image_uploads'])) {
                $product->imageUploads = $imageUploads['image_uploads'];
            }

            if ($this->Products->save($product)) {
                $this->clearContentCache();
                $this->Flash->success(__('Your amazing product has been saved! 🎉'));

                // Queue verification job
                $this->queueJob('ProductVerificationJob', [
                    'product_id' => $product->id,
                ]);

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Oops! Something went wrong. Please try again.'));
        }

        // Get form options
        $users = $this->Products->Users->find('list', ['limit' => 200])->all();
        $articles = $this->Products->Articles
            ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
            ->where(['is_published' => true])
            ->orderby(['title' => 'ASC']);
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();
        $token = $this->request->getAttribute('csrfToken');

        $this->set(compact('product', 'users', 'articles', 'tags', 'token'));

        return null;
    }

    /**
     * AI Score method - Calculate AI score for product data
     *
     * @return \Cake\Http\Response JSON response with AI score and feedback
     */
    public function aiScore(): Response
    {
        $this->request->allowMethod(['post']);

        $data = $this->request->getData();

        // Calculate AI score based on product completeness and quality
        $score = $this->calculateProductScore($data);

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($score));
    }

    /**
     * Calculate product quality score and provide feedback
     *
     * @param array $data Product data from form
     * @return array Score and feedback array
     */
    private function calculateProductScore(array $data): array
    {
        $score = 0;
        $feedback = [];

        // Title analysis (max 30 points)
        if (!empty($data['title'])) {
            $titleLen = strlen(trim($data['title']));
            if ($titleLen >= 10 && $titleLen <= 60) {
                $score += 30;
                $feedback[] = '✅ Perfect title length';
            } elseif ($titleLen > 0) {
                $score += 18;
                if ($titleLen < 10) {
                    $feedback[] = '⚠️ Title could be more descriptive';
                }
                if ($titleLen > 60) {
                    $feedback[] = '⚠️ Title might be too long';
                }
            }
        }

        // Description analysis (max 25 points)
        if (!empty($data['description'])) {
            $descLen = strlen(trim($data['description']));
            if ($descLen >= 50) {
                $score += 25;
                $feedback[] = '✅ Comprehensive description provided';
            } else {
                $score += 12;
                $feedback[] = '⚠️ Description could be more detailed';
            }
        } else {
            $feedback[] = '❌ Missing product description';
        }

        // Brand/manufacturer (max 15 points)
        if (!empty($data['manufacturer'])) {
            $score += 15;
            $feedback[] = '✅ Brand information included';
        } else {
            $feedback[] = '💡 Consider adding brand/manufacturer';
        }

        // Price information (max 15 points)
        if (!empty($data['price']) && is_numeric($data['price']) && $data['price'] > 0) {
            $score += 15;
            $feedback[] = '✅ Pricing information provided';
        } else {
            $feedback[] = '💡 Price helps buyers make decisions';
        }

        // Categories/Tags (max 10 points)
        if (!empty($data['tags']) && is_array($data['tags']) && count($data['tags']) > 0) {
            $score += 10;
            $feedback[] = '✅ Product properly categorized';
        } else {
            $feedback[] = '💡 Tags help people find your product';
        }

        // Image (max 5 points)
        if (!empty($data['image_uploaded'])) {
            $score += 5;
            $feedback[] = '✅ Product image uploaded';
        } else {
            $feedback[] = '📷 Images make your listing more appealing';
        }

        // Generate overall feedback
        $overall = '';
        if ($score >= 80) {
            $overall = '🎉 Outstanding! Your listing looks professional and complete.';
        } elseif ($score >= 60) {
            $overall = '👍 Great work! Just a few tweaks could make it even better.';
        } elseif ($score >= 40) {
            $overall = '🚀 Good start! Adding more details will boost visibility.';
        } else {
            $overall = '💪 Keep going! Every detail you add helps buyers find you.';
        }

        return [
            'score' => $score,
            'maxScore' => 100,
            'feedback' => $feedback,
            'overall' => $overall,
            'percentage' => round($score / 100 * 100),
        ];
    }

    /**
     * Add method - Create new product
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $product = $this->Products->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $this->getRequest()->getAttribute('identity')->id;

            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags'],
            ]);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads');
            if (!empty($imageUploads['image_uploads'])) {
                $product->imageUploads = $imageUploads['image_uploads'];
            }

            if ($this->Products->save($product)) {
                $this->clearContentCache();
                $this->Flash->success(__('The product has been saved.'));

                // Queue verification job
                $this->queueJob('ProductVerificationJob', [
                    'product_id' => $product->id,
                ]);

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        // Get form options
        $users = $this->Products->Users->find('list', ['limit' => 200])->all();
        $articles = $this->Products->Articles
            ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
            ->where(['is_published' => true])
            ->orderby(['title' => 'ASC']);
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();
        $token = $this->request->getAttribute('csrfToken');

        $this->set(compact('product', 'users', 'articles', 'tags', 'token'));

        return null;
    }

    /**
     * Edit method - Modify existing product
     *
     * @param string|null $id Product ID.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $product = $this->Products->get($id, [
            'contain' => ['Tags'],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            // TODO: create a reliability score
            // $originalScore = $product->reliability_score;

            $product = $this->Products->patchEntity($product, $data, [
                'associated' => ['Tags'],
            ]);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads') ?? [];
            if (!empty($imageUploads['image_uploads'])) {
                $product->imageUploads = $imageUploads['image_uploads'];
            }

            // Handle image unlinking
            $unlinkedImages = $this->request->getData('unlink_images') ?? [];
            $product->unlinkedImages = $unlinkedImages;

            $saveOptions = [];
            if (isset($data['regenerateTags'])) {
                $saveOptions['regenerateTags'] = $data['regenerateTags'];
            }

            if ($this->Products->save($product, $saveOptions)) {
                $this->clearContentCache();
                $this->Flash->success(__('The product has been saved.'));

                // Re-verify if significant changes were made
                if ($this->hasSignificantChanges($product)) {
                    $this->queueJob('ProductVerificationJob', [
                        'product_id' => $product->id,
                    ]);
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }

        $users = $this->Products->Users->find('list', ['limit' => 200])->all();
        $articles = $this->Products->Articles
            ->find('list', ['keyField' => 'id', 'valueField' => 'title'])
            ->where(['is_published' => true])
            ->orderby(['title' => 'ASC']);
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();

        $this->set(compact('product', 'users', 'articles', 'tags'));

        return null;
    }

    /**
     * Delete method - Remove product
     *
     * @param string|null $id Product ID.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $product = $this->Products->get($id);

        if ($this->Products->delete($product)) {
            $this->clearContentCache();
            $this->Flash->success(__('The product has been deleted.'));
        } else {
            $this->Flash->error(__('The product could not be deleted. Please, try again.'));
        }

        $this->redirect(['action' => 'index']);
    }

    /**
     * Verify method - Manual verification trigger
     * This method queues a job to verify the product.
     *
     * @param mixed $id
     */
    public function verify(mixed $id = null): void
    {
        $this->request->allowMethod(['post']);

        $this->queueJob('ProductVerificationJob', [
            'product_id' => $id,
        ]);

        $this->Flash->success(__('Product verification has been queued.'));

        $this->redirect(['action' => 'view', $id]);
    }

    /**
     * Toggle featured status
     *
     * @param string|null $id Product ID.
     * @return void
     */
    public function toggleFeatured(?string $id = null): void
    {
        $this->request->allowMethod(['post']);

        $product = $this->Products->get($id);
        $product->featured = !$product->featured;

        if ($this->Products->save($product)) {
            $this->clearContentCache();
            $status = $product->featured ? 'featured' : 'unfeatured';
            $this->Flash->success(__('Product has been {0}.', $status));
        } else {
            $this->Flash->error(__('Could not update product status.'));
        }

        $this->redirect($this->referer(['action' => 'index']));
    }

    /**
     * Toggle published status
     *
     * @param string|null $id Product ID.
     * @return void
     */
    public function togglePublished(?string $id = null): void
    {
        $this->request->allowMethod(['post']);

        $product = $this->Products->get($id);
        $product->is_published = !$product->is_published;

        if ($this->Products->save($product)) {
            $this->clearContentCache();
            $status = $product->is_published ? 'published' : 'unpublished';
            $this->Flash->success(__('Product has been {0}.', $status));
        } else {
            $this->Flash->error(__('Could not update product status.'));
        }

        $this->redirect($this->referer(['action' => 'index']));
    }

    /**
     * Approve product verification status
     *
     * @param string|null $id Product ID.
     * @return void
     */
    public function approve(?string $id = null): void
    {
        $this->request->allowMethod(['post']);

        $product = $this->Products->get($id);
        $product->verification_status = 'approved';

        if ($this->Products->save($product)) {
            $this->clearContentCache();
            $this->Flash->success(__('Product has been approved.'));
        } else {
            $this->Flash->error(__('Could not approve product.'));
        }

        $this->redirect($this->referer(['action' => 'pendingReview']));
    }

    /**
     * Reject product verification status
     *
     * @param string|null $id Product ID.
     * @return void
     */
    public function reject(?string $id = null): void
    {
        $this->request->allowMethod(['post']);

        $product = $this->Products->get($id);
        $product->verification_status = 'rejected';

        if ($this->Products->save($product)) {
            $this->clearContentCache();
            $this->Flash->success(__('Product has been rejected.'));
        } else {
            $this->Flash->error(__('Could not reject product.'));
        }

        $this->redirect($this->referer(['action' => 'pendingReview']));
    }

    /**
     * Check if product has significant changes requiring re-verification
     *
     * @param \App\Model\Entity\Product $product Product entity.
     */
    private function hasSignificantChanges(Product $product): bool
    {
        return $product->isDirty(['title', 'description', 'manufacturer', 'model_number']);
    }

    /**
     * Queue a background job using the proper Message format
     */
    private function queueJob(string $jobClass, array $data): void
    {
        // For now, just log that a job would be queued
        // In production, this would integrate with the actual queue system
        $this->log(sprintf('Would queue job %s with data: %s', $jobClass, json_encode($data)), 'info');
    }

    /**
     * Clear content cache after operations
     */
    private function clearContentCache(): void
    {
        Cache::clear('content');
    }

    /**
     * Bulk verify products - Queue verification jobs for selected products
     *
     * @return \Cake\Http\Response
     */
    public function bulkVerify(): Response
    {
        $this->request->allowMethod(['post']);

        $ids = (array)$this->request->getData('ids', []);
        $returnUrl = $this->request->getData('returnUrl', ['action' => 'pendingReview']);

        // Use new validation method
        $validIds = $this->validateProductIds($ids);
        if (empty($validIds)) {
            $this->Flash->error(__('Please select valid products to verify.'));

            return $this->redirect($returnUrl);
        }

        // Queue verification job for each product
        foreach ($validIds as $id) {
            $this->queueJob('ProductVerificationJob', [
                'product_id' => $id,
            ]);
        }

        $count = count($validIds);
        $this->Flash->success(__('Verification has been queued for {0} product(s).', $count));
        $this->logBulkAction('bulk_verify_standalone', $validIds, ['queued_count' => $count]);

        return $this->redirect($returnUrl);
    }

    /**
     * Bulk approve products - Set verification_status to 'approved' for selected products
     *
     * @return \Cake\Http\Response
     */
    public function bulkApprove(): Response
    {
        $this->request->allowMethod(['post']);

        $ids = (array)$this->request->getData('ids', []);

        // Validate that IDs were provided
        if (empty($ids)) {
            $this->Flash->error(__('Please select products to approve.'));

            return $this->redirect($this->referer(['action' => 'pendingReview']));
        }

        // Basic sanity check - validate IDs are strings/UUIDs
        $validIds = [];
        foreach ($ids as $id) {
            if (is_string($id) && !empty(trim($id))) {
                $validIds[] = trim($id);
            }
        }

        if (empty($validIds)) {
            $this->Flash->error(__('Invalid product IDs provided.'));

            return $this->redirect($this->referer(['action' => 'pendingReview']));
        }

        try {
            // Update all selected products to approved status
            $updatedCount = $this->Products->updateAll(
                ['verification_status' => 'approved'],
                ['id IN' => $validIds],
            );

            $this->clearContentCache();
            $this->Flash->success(__('Successfully approved {0} product(s).', $updatedCount));
        } catch (Exception $e) {
            $this->log('Bulk approve error: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('An error occurred while approving products. Please try again.'));
        }

        return $this->redirect(['action' => 'pendingReview']);
    }

    /**
     * Bulk reject products - Set verification_status to 'rejected' for selected products
     *
     * @return \Cake\Http\Response
     */
    public function bulkReject(): Response
    {
        $this->request->allowMethod(['post']);

        $ids = (array)$this->request->getData('ids', []);

        // Validate that IDs were provided
        if (empty($ids)) {
            $this->Flash->error(__('Please select products to reject.'));

            return $this->redirect($this->referer(['action' => 'pendingReview']));
        }

        // Basic sanity check - validate IDs are strings/UUIDs
        $validIds = [];
        foreach ($ids as $id) {
            if (is_string($id) && !empty(trim($id))) {
                $validIds[] = trim($id);
            }
        }

        if (empty($validIds)) {
            $this->Flash->error(__('Invalid product IDs provided.'));

            return $this->redirect($this->referer(['action' => 'pendingReview']));
        }

        try {
            // Update all selected products to rejected status
            $updatedCount = $this->Products->updateAll(
                ['verification_status' => 'rejected'],
                ['id IN' => $validIds],
            );

            $this->clearContentCache();
            $this->Flash->success(__('Successfully rejected {0} product(s).', $updatedCount));
        } catch (Exception $e) {
            $this->log('Bulk reject error: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('An error occurred while rejecting products. Please try again.'));
        }

        return $this->redirect(['action' => 'pendingReview']);
    }

    /**
     * Forms Configuration method - Manage frontend product submission forms
     *
     * This method provides an admin interface to configure frontend product submission forms.
     * It allows admins to:
     * - Enable/disable public product submissions
     * - Configure form fields and validation
     * - Set default status for user-submitted products
     * - Manage submission workflow settings
     * - Configure quiz-based adapter finder
     *
     * @return \Cake\Http\Response|null
     */
    public function forms(): ?Response
    {
        // Load settings for product forms configuration
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');

        // Define schema for all product form settings
        $settingsSchema = [
            'enable_public_submissions' => [
                'default' => '0',
                'type' => 'bool',
                'description' => 'Allow public users to submit products via frontend forms',
            ],
            'require_admin_approval' => [
                'default' => '1',
                'type' => 'bool',
                'description' => 'Whether user-submitted products require admin approval before publication',
            ],
            'default_status' => [
                'default' => 'pending',
                'type' => 'select',
                'options' => ['pending' => 'Pending Review', 'approved' => 'Approved', 'rejected' => 'Rejected'],
                'description' => 'Default verification status for user-submitted products',
            ],
            'max_file_size' => [
                'default' => '5',
                'type' => 'numeric',
                'description' => 'Maximum file size in MB for product image uploads',
            ],
            'allowed_file_types' => [
                'default' => 'jpg,jpeg,png,gif,webp',
                'type' => 'text',
                'description' => 'Comma-separated list of allowed file extensions for product images',
            ],
            'required_fields' => [
                'default' => 'title,description,manufacturer',
                'type' => 'text',
                'description' => 'Comma-separated list of required form fields',
            ],
            'notification_email' => [
                'default' => '0',
                'type' => 'text',
                'description' => 'Email address to notify when new products are submitted (use 0 to disable)',
            ],
            'success_message' => [
                'default' => 'Your product has been submitted and is awaiting review. ' .
                    'Thank you for contributing to our adapter database!',
                'type' => 'textarea',
                'description' => 'Message shown to users after successful product submission',
            ],
            'quiz_enabled' => [
                'default' => '0',
                'type' => 'bool',
                'description' => 'Enable quiz-based adapter finder to help users discover suitable adapters',
            ],
            'quiz_config_json' => [
                'default' => '{}',
                'type' => 'textarea',
                'description' => 'JSON configuration for quiz questions, branching logic, and scoring algorithm',
            ],
            'quiz_results_page' => [
                'default' => '0',
                'type' => 'select-page',
                'description' => 'Page to redirect users to after quiz completion (0 = disabled)',
            ],
        ];

        // Get current form configuration settings
        $formSettings = [];
        foreach ($settingsSchema as $key => $schema) {
            $setting = $settingsTable->find()
                ->where(['category' => 'Products', 'key_name' => $key])
                ->first();

            if ($setting) {
                $value = $setting->value;
                // Convert bool values for template compatibility
                if ($schema['type'] === 'bool') {
                    $formSettings[$key] = $value === '1' || $value === 1 || $value === true ? 'true' : 'false';
                } else {
                    $formSettings[$key] = $value;
                }
            } else {
                // Use default value
                if ($schema['type'] === 'bool') {
                    $formSettings[$key] = $schema['default'] === '1' ? 'true' : 'false';
                } else {
                    $formSettings[$key] = $schema['default'];
                }
            }
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            // Validate and save each setting
            $savedSettings = 0;
            $failedSettings = [];

            foreach ($data as $settingName => $settingValue) {
                // Skip CSRF token and other non-setting fields
                if (in_array($settingName, ['_csrfToken'])) {
                    continue;
                }

                // Only process known settings
                if (!isset($settingsSchema[$settingName])) {
                    continue;
                }

                $schema = $settingsSchema[$settingName];

                // Normalize value based on type
                $normalizedValue = $this->normalizeSettingValue($settingValue, $schema);

                // Find existing setting or create new one
                $setting = $settingsTable->find()
                    ->where(['category' => 'Products', 'key_name' => $settingName])
                    ->first();

                if (!$setting) {
                    $setting = $settingsTable->newEmptyEntity();
                    $setting->category = 'Products';
                    $setting->key_name = $settingName;
                    $setting->value_type = $schema['type'];
                    $setting->description = $schema['description'];
                    $setting->ordering = 100; // Default ordering

                    // Set data field for select options if applicable
                    if (isset($schema['options'])) {
                        $setting->data = json_encode($schema['options']);
                    }
                }

                $setting->value = (string)$normalizedValue;

                if ($settingsTable->save($setting)) {
                    $savedSettings++;
                } else {
                    $failedSettings[] = $settingName;
                }
            }

            if ($savedSettings > 0) {
                $this->clearContentCache();
                $this->Flash->success(
                    __('Product form configuration has been updated. ({0} settings saved)', $savedSettings),
                );
            }

            if (!empty($failedSettings)) {
                $this->Flash->error(__('Failed to save some settings: {0}', implode(', ', $failedSettings)));
            }

            return $this->redirect(['action' => 'forms']);
        }

        // Get statistics about user submissions
        $submissionStats = [
            'total_submissions' => $this->Products->find()->where(['user_id IS NOT' => null])->count(),
            'pending_submissions' => $this->Products->find()->where([
                'user_id IS NOT' => null,
                'verification_status' => 'pending',
            ])->count(),
            'approved_submissions' => $this->Products->find()->where([
                'user_id IS NOT' => null,
                'verification_status' => 'approved',
            ])->count(),
            'rejected_submissions' => $this->Products->find()->where([
                'user_id IS NOT' => null,
                'verification_status' => 'rejected',
            ])->count(),
        ];

        // Recent user submissions for preview
        $recentSubmissions = $this->Products->find()
            ->contain(['Users'])
            ->where(['Products.user_id IS NOT' => null])
            ->orderBy(['Products.created' => 'DESC'])
            ->limit(10)
            ->toArray();

        // Load ProductFormFields for dynamic form management
        $productFormFieldsTable = TableRegistry::getTableLocator()->get('ProductFormFields');
        $productFormFields = $productFormFieldsTable->find('all')
            ->orderBy(['display_order' => 'ASC'])
            ->toArray();

        $this->set(compact('formSettings', 'submissionStats', 'recentSubmissions', 'productFormFields'));

        return null;
    }

    /**
     * Get description for form configuration settings
     *
     * @param string $settingName Setting name without prefix
     * @return string Description for the setting
     */
    private function getSettingDescription(string $settingName): string
    {
        return match ($settingName) {
            'enable_public_submissions' => 'Allow public users to submit products via frontend forms',
            'default_status' => 'Default verification status for user-submitted products',
            'require_admin_approval' => 'Whether user-submitted products require admin approval before publication',
            'allowed_file_types' => 'Comma-separated list of allowed file extensions for product images',
            'max_file_size' => 'Maximum file size in MB for product image uploads',
            'required_fields' => 'Comma-separated list of required form fields',
            'notification_email' => 'Email address to notify when new products are submitted',
            'success_message' => 'Message shown to users after successful product submission',
            'quiz_enabled' => 'Enable quiz-based adapter finder to help users discover suitable adapters',
            'quiz_config_json' => 'JSON configuration for quiz questions, branching logic, and scoring algorithm',
            'quiz_results_page' => 'Page to redirect users to after quiz completion (0 = disabled)',
            default => 'Product form configuration setting'
        };
    }

    /**
     * Normalize setting value based on schema type
     *
     * @param mixed $value The raw value from form submission
     * @param array $schema The setting schema definition
     * @return string The normalized value ready for database storage
     */
    private function normalizeSettingValue(mixed $value, array $schema): string
    {
        $type = $schema['type'];
        $default = $schema['default'];

        switch ($type) {
            case 'bool':
                // Handle various boolean representations
                if (in_array(strtolower((string)$value), ['true', '1', 'on', 'yes'], true)) {
                    return '1';
                }

                return '0';

            case 'numeric':
                // Ensure value is numeric, otherwise use default
                if (is_numeric($value)) {
                    return (string)$value;
                }

                return $default;

            case 'select':
                // Validate against allowed options
                if (isset($schema['options']) && array_key_exists($value, $schema['options'])) {
                    return (string)$value;
                }

                return $default;

            case 'text':
            case 'textarea':
                // Handle special cases for "disableable" fields
                $disableableFields = ['notification_email'];
                if (in_array(array_search($schema, array_column([], 'description')), $disableableFields)) {
                    if (empty($value) || trim($value) === '') {
                        return '0'; // Use '0' to represent disabled
                    }
                }

                // For other text fields, use the value or default if empty
                $trimmedValue = trim((string)$value);
                if (empty($trimmedValue)) {
                    return $default;
                }

                return $trimmedValue;

            case 'select-page':
                // Validate page ID or allow 0 for disabled
                if (is_numeric($value) && (int)$value >= 0) {
                    return (string)(int)$value;
                }

                return $default;

            default:
                return (string)$value ?: $default;
        }
    }

    /**
     * Bulk edit dispatcher - handles various bulk actions
     *
     * @return \Cake\Http\Response
     */
    public function bulkEdit(): Response
    {
        $this->request->allowMethod(['post']);

        $action = $this->request->getData('bulk_action');
        $selectedIds = (array)$this->request->getData('selected', []);
        $returnUrl = $this->request->getData('returnUrl', ['action' => 'index']);

        if (empty($selectedIds)) {
            $this->Flash->error(__('Please select at least one product.'));

            return $this->redirect($returnUrl);
        }

        // Log the bulk action
        $this->logBulkAction('bulk_edit_dispatch', $selectedIds, ['action' => $action]);

        switch ($action) {
            case 'verify':
                return $this->bulkVerifyInternal($selectedIds, $returnUrl);
            case 'approve':
                return $this->bulkApproveInternal($selectedIds, $returnUrl);
            case 'reject':
                return $this->bulkRejectInternal($selectedIds, $returnUrl);
            case 'publish':
                return $this->bulkTogglePublished($selectedIds, true, $returnUrl);
            case 'unpublish':
                return $this->bulkTogglePublished($selectedIds, false, $returnUrl);
            case 'feature':
                return $this->bulkToggleFeatured($selectedIds, true, $returnUrl);
            case 'unfeature':
                return $this->bulkToggleFeatured($selectedIds, false, $returnUrl);
            case 'delete':
                return $this->bulkDelete($selectedIds, $returnUrl);
            default:
                $this->Flash->error(__('Unknown bulk action: {0}', $action));

                return $this->redirect($returnUrl);
        }
    }

    /**
     * Bulk verification with custom selected IDs - wrapper for existing method
     *
     * @param array $selectedIds Array of product IDs
     * @param mixed $returnUrl URL to redirect to after completion
     * @return \Cake\Http\Response
     */
    private function bulkVerifyInternal(array $selectedIds, mixed $returnUrl): Response
    {
        // Set up request data to work with existing method
        $this->request = $this->request->withData('ids', $selectedIds);
        $this->request = $this->request->withData('returnUrl', $returnUrl);

        return $this->bulkVerify();
    }

    /**
     * Bulk approve with custom selected IDs - reuse existing logic
     *
     * @param array $selectedIds Array of product IDs
     * @param mixed $returnUrl URL to redirect to after completion
     * @return \Cake\Http\Response
     */
    private function bulkApproveInternal(array $selectedIds, mixed $returnUrl): Response
    {
        // Validate IDs
        $validIds = $this->validateProductIds($selectedIds);
        if (empty($validIds)) {
            $this->Flash->error(__('Invalid product IDs provided.'));

            return $this->redirect($returnUrl);
        }

        try {
            $updatedCount = $this->Products->updateAll(
                ['verification_status' => 'approved'],
                ['id IN' => $validIds],
            );

            $this->clearContentCache();
            $this->Flash->success(__('Successfully approved {0} product(s).', $updatedCount));
            $this->logBulkAction('bulk_approve', $validIds, ['updated_count' => $updatedCount]);
        } catch (Exception $e) {
            $this->log('Bulk approve error: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('An error occurred while approving products. Please try again.'));
        }

        return $this->redirect($returnUrl);
    }

    /**
     * Bulk reject with custom selected IDs - reuse existing logic
     *
     * @param array $selectedIds Array of product IDs
     * @param mixed $returnUrl URL to redirect to after completion
     * @return \Cake\Http\Response
     */
    private function bulkRejectInternal(array $selectedIds, mixed $returnUrl): Response
    {
        // Validate IDs
        $validIds = $this->validateProductIds($selectedIds);
        if (empty($validIds)) {
            $this->Flash->error(__('Invalid product IDs provided.'));

            return $this->redirect($returnUrl);
        }

        try {
            $updatedCount = $this->Products->updateAll(
                ['verification_status' => 'rejected'],
                ['id IN' => $validIds],
            );

            $this->clearContentCache();
            $this->Flash->success(__('Successfully rejected {0} product(s).', $updatedCount));
            $this->logBulkAction('bulk_reject', $validIds, ['updated_count' => $updatedCount]);
        } catch (Exception $e) {
            $this->log('Bulk reject error: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('An error occurred while rejecting products. Please try again.'));
        }

        return $this->redirect($returnUrl);
    }

    /**
     * Bulk toggle published status
     *
     * @param array $selectedIds Array of product IDs (optional if from request data)
     * @param bool $publishStatus True to publish, false to unpublish (optional if from request data)
     * @param mixed $returnUrl URL to redirect to after completion (optional if from request data)
     * @return \Cake\Http\Response
     */
    public function bulkTogglePublished(?array $selectedIds = null, ?bool $publishStatus = null, mixed $returnUrl = null): Response
    {
        $this->request->allowMethod(['post']);

        // Get parameters from method call or request data
        $selectedIds = $selectedIds ?? (array)$this->request->getData('selected', []);
        $publishStatus = $publishStatus ?? (bool)$this->request->getData('publish_status', true);
        $returnUrl = $returnUrl ?? $this->request->getData('returnUrl', ['action' => 'index']);

        // Validate IDs
        $validIds = $this->validateProductIds($selectedIds);
        if (empty($validIds)) {
            $this->Flash->error(__('Invalid or missing product IDs.'));

            return $this->redirect($returnUrl);
        }

        try {
            $updatedCount = $this->Products->updateAll(
                ['is_published' => $publishStatus],
                ['id IN' => $validIds],
            );

            $this->clearContentCache();
            $status = $publishStatus ? __('published') : __('unpublished');
            $this->Flash->success(__('Successfully {0} {1} product(s).', $status, $updatedCount));
            $this->logBulkAction('bulk_toggle_published', $validIds, [
                'publish_status' => $publishStatus,
                'updated_count' => $updatedCount,
            ]);
        } catch (Exception $e) {
            $this->log('Bulk toggle published error: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('An error occurred while updating product status. Please try again.'));
        }

        return $this->redirect($returnUrl);
    }

    /**
     * Bulk toggle featured status
     *
     * @param array $selectedIds Array of product IDs (optional if from request data)
     * @param bool $featuredStatus True to feature, false to unfeature (optional if from request data)
     * @param mixed $returnUrl URL to redirect to after completion (optional if from request data)
     * @return \Cake\Http\Response
     */
    public function bulkToggleFeatured(?array $selectedIds = null, ?bool $featuredStatus = null, mixed $returnUrl = null): Response
    {
        $this->request->allowMethod(['post']);

        // Get parameters from method call or request data
        $selectedIds = $selectedIds ?? (array)$this->request->getData('selected', []);
        $featuredStatus = $featuredStatus ?? (bool)$this->request->getData('featured_status', true);
        $returnUrl = $returnUrl ?? $this->request->getData('returnUrl', ['action' => 'index']);

        // Validate IDs
        $validIds = $this->validateProductIds($selectedIds);
        if (empty($validIds)) {
            $this->Flash->error(__('Invalid or missing product IDs.'));

            return $this->redirect($returnUrl);
        }

        try {
            $updatedCount = $this->Products->updateAll(
                ['featured' => $featuredStatus],
                ['id IN' => $validIds],
            );

            $this->clearContentCache();
            $status = $featuredStatus ? __('featured') : __('unfeatured');
            $this->Flash->success(__('Successfully {0} {1} product(s).', $status, $updatedCount));
            $this->logBulkAction('bulk_toggle_featured', $validIds, [
                'featured_status' => $featuredStatus,
                'updated_count' => $updatedCount,
            ]);
        } catch (Exception $e) {
            $this->log('Bulk toggle featured error: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('An error occurred while updating product status. Please try again.'));
        }

        return $this->redirect($returnUrl);
    }

    /**
     * Bulk delete products
     *
     * @param array $selectedIds Array of product IDs (optional if from request data)
     * @param mixed $returnUrl URL to redirect to after completion (optional if from request data)
     * @return \Cake\Http\Response
     */
    public function bulkDelete(?array $selectedIds = null, mixed $returnUrl = null): Response
    {
        $this->request->allowMethod(['post']);

        // Get parameters from method call or request data
        $selectedIds = $selectedIds ?? (array)$this->request->getData('selected', []);
        $returnUrl = $returnUrl ?? $this->request->getData('returnUrl', ['action' => 'index']);

        // Validate IDs
        $validIds = $this->validateProductIds($selectedIds);
        if (empty($validIds)) {
            $this->Flash->error(__('Invalid or missing product IDs.'));

            return $this->redirect($returnUrl);
        }

        $deletedCount = 0;
        $failedCount = 0;

        // Use connection transaction for bulk delete
        $connection = $this->Products->getConnection();
        $connection->transactional(function () use ($validIds, &$deletedCount, &$failedCount): void {
            foreach ($validIds as $id) {
                try {
                    $product = $this->Products->get($id);
                    if ($this->Products->delete($product)) {
                        $deletedCount++;
                    } else {
                        $failedCount++;
                    }
                } catch (Exception $e) {
                    $this->log("Failed to delete product {$id}: " . $e->getMessage(), 'error');
                    $failedCount++;
                }
            }
        });

        $this->clearContentCache();

        if ($deletedCount > 0) {
            $this->Flash->success(__('Successfully deleted {0} product(s).', $deletedCount));
        }
        if ($failedCount > 0) {
            $this->Flash->error(__('Failed to delete {0} product(s).', $failedCount));
        }

        $this->logBulkAction('bulk_delete', $validIds, [
            'deleted_count' => $deletedCount,
            'failed_count' => $failedCount,
        ]);

        return $this->redirect($returnUrl);
    }

    /**
     * Bulk update fields - advanced mass edit
     *
     * @return \Cake\Http\Response
     */
    public function bulkUpdateFields(): Response
    {
        $this->request->allowMethod(['post']);

        $selectedIds = (array)$this->request->getData('selected', []);
        $returnUrl = $this->request->getData('returnUrl', ['action' => 'index']);

        // Validate IDs
        $validIds = $this->validateProductIds($selectedIds);
        if (empty($validIds)) {
            $this->Flash->error(__('Invalid or missing product IDs.'));

            return $this->redirect($returnUrl);
        }

        // Define allowed fields and their validation
        $allowedFields = [
            'verification_status' => ['pending', 'approved', 'rejected'],
            'is_published' => ['0', '1'],
            'featured' => ['0', '1'],
            'manufacturer' => 'string',
            'price' => 'numeric',
            'currency' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY'],
        ];

        // Extract and validate update data
        $updateData = [];
        foreach ($allowedFields as $field => $validation) {
            $value = $this->request->getData($field);
            if ($value !== null && $value !== '') {
                if (is_array($validation) && !in_array($value, $validation)) {
                    $this->Flash->error(__('Invalid value for field {0}: {1}', $field, $value));

                    return $this->redirect($returnUrl);
                } elseif ($validation === 'numeric' && !is_numeric($value)) {
                    $this->Flash->error(__('Invalid numeric value for field {0}: {1}', $field, $value));

                    return $this->redirect($returnUrl);
                } elseif ($validation === 'string' && empty(trim($value))) {
                    continue; // Skip empty strings
                }

                // Convert boolean-like fields
                if (in_array($field, ['is_published', 'featured'])) {
                    $updateData[$field] = (bool)$value;
                } elseif ($field === 'price') {
                    $updateData[$field] = (float)$value;
                } else {
                    $updateData[$field] = trim($value);
                }
            }
        }

        if (empty($updateData)) {
            $this->Flash->error(__('No valid updates provided.'));

            return $this->redirect($returnUrl);
        }

        try {
            $updatedCount = $this->Products->updateAll($updateData, ['id IN' => $validIds]);
            $this->clearContentCache();
            $this->Flash->success(__('Successfully updated {0} product(s) with {1} field(s).', $updatedCount, count($updateData)));
            $this->logBulkAction('bulk_update_fields', $validIds, [
                'fields' => array_keys($updateData),
                'updated_count' => $updatedCount,
            ]);
        } catch (Exception $e) {
            $this->log('Bulk update fields error: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('An error occurred while updating products. Please try again.'));
        }

        return $this->redirect($returnUrl);
    }

    /**
     * Validate and filter product IDs
     *
     * @param array $ids Array of product IDs to validate
     * @return array Array of valid UUIDs
     */
    private function validateProductIds(array $ids): array
    {
        $validIds = [];
        foreach ($ids as $id) {
            if (is_string($id) && !empty(trim($id)) && preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $id)) {
                $validIds[] = trim($id);
            }
        }

        return array_unique($validIds);
    }

    /**
     * Log bulk actions for audit trail with checksum verification
     *
     * @param string $action Action name
     * @param array $ids Array of product IDs
     * @param array $payload Additional data
     */
    private function logBulkAction(string $action, array $ids, array $payload = []): void
    {
        $userId = $this->getRequest()->getAttribute('identity')?->id ?? 'anonymous';
        $timestamp = date('c');

        $logData = [
            'timestamp' => $timestamp,
            'user_id' => $userId,
            'action' => $action,
            'ids' => array_values(array_unique(array_map('trim', $ids))),
            'payload' => $payload,
            'result' => $payload, // For compatibility
        ];

        // Sort IDs for consistent logging
        sort($logData['ids']);

        $jsonString = json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $checksum = hash('sha256', $jsonString);
        $logLine = "[sha256={$checksum}] {$jsonString}";

        $this->log($logLine, 'info', ['scope' => 'admin_actions']);
    }
}
