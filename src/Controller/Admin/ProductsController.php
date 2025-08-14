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

        // Validate that IDs were provided
        if (empty($ids)) {
            $this->Flash->error(__('Please select products to verify.'));

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

        // Queue verification job for each product
        foreach ($validIds as $id) {
            $this->queueJob('ProductVerificationJob', [
                'product_id' => $id,
            ]);
        }

        $count = count($validIds);
        $this->Flash->success(__('Verification has been queued for {0} product(s).', $count));

        return $this->redirect(['action' => 'pendingReview']);
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
     *
     * @return \Cake\Http\Response|null
     */
    public function forms(): ?Response
    {
        // Load settings for product forms configuration
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');

        // Get current form configuration settings
        $formSettings = [
            'enable_public_submissions' => $settingsTable->
            findByName('products.enable_public_submissions')->first()?->value ?? 'false',
            'default_status' => $settingsTable->
            findByName('products.default_status')->first()?->value ?? 'pending',
            'require_admin_approval' => $settingsTable->
            findByName('products.require_admin_approval')->first()?->value ?? 'true',
            'allowed_file_types' => $settingsTable->
            findByName('products.allowed_file_types')->first()?->value ?? 'jpg,jpeg,png,gif',
            'max_file_size' => $settingsTable->
            findByName('products.max_file_size')->first()?->value ?? '5',
            'required_fields' => $settingsTable->
            findByName('products.required_fields')->first()?->value ?? 'title,description,manufacturer',
            'notification_email' => $settingsTable->
            findByName('products.notification_email')->first()?->value ?? '',
            'success_message' => $settingsTable->
            findByName('products.success_message')->first()?->value ??
            'Your product has been submitted and is awaiting review.',
        ];

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

                $fullSettingName = 'products.' . $settingName;

                // Find or create the setting
                $setting = $settingsTable->findByName($fullSettingName)->first();
                if (!$setting) {
                    $setting = $settingsTable->newEmptyEntity();
                    $setting->name = $fullSettingName;
                    $setting->value = (string)$settingValue;
                    $setting->description = $this->getSettingDescription($settingName);
                } else {
                    $setting->value = (string)$settingValue;
                }

                if ($settingsTable->save($setting)) {
                    $savedSettings++;
                } else {
                    $failedSettings[] = $settingName;
                }
            }

            if ($savedSettings > 0) {
                $this->clearContentCache();
                $this->Flash->success(__('Product form configuration has been \n
                updated. ({0} settings saved)', $savedSettings));
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

        $this->set(compact('formSettings', 'submissionStats', 'recentSubmissions'));

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
            default => 'Product form configuration setting'
        };
    }
}
