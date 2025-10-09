<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Controller\Component\MediaPickerTrait;
use App\Service\ImageProcessingService;
use App\Utility\ArchiveExtractor;
use Cake\Http\Response;

/**
 * ImageGalleries Controller
 *
 * @property \App\Model\Table\ImageGalleriesTable $ImageGalleries
 */
class ImageGalleriesController extends AppController
{
    use MediaPickerTrait;

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $session = $this->request->getSession();
        $viewType = $this->request->getQuery('view');

        // Handle view switching with session persistence
        if ($viewType) {
            $session->write('ImageGalleries.viewType', $viewType);
        } else {
            $viewType = $session->read('ImageGalleries.viewType', 'grid'); // Default to grid for galleries
        }

        $query = $this->ImageGalleries->find()
            ->select([
                'ImageGalleries.id',
                'ImageGalleries.name',
                'ImageGalleries.slug',
                'ImageGalleries.description',
                'ImageGalleries.preview_image',
                'ImageGalleries.is_published',
                'ImageGalleries.created',
                'ImageGalleries.modified',
                'ImageGalleries.created_by',
                'ImageGalleries.modified_by',
            ]);

        // Load images for both views - grid needs all for slideshow, list needs thumbnails
        $query->contain([
            'Images' => function ($q) {
                return $q->orderBy(['ImageGalleriesImages.position' => 'ASC']);
                // Load all images so slideshow shows complete gallery in grid view
                // and list view has images for thumbnails and popovers
            },
        ]);

        // Handle status filter
        $statusFilter = $this->request->getQuery('status');
        if ($statusFilter !== null) {
            $query->where(['ImageGalleries.is_published' => (bool)$statusFilter]);
        }

        // Handle search
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'ImageGalleries.name LIKE' => '%' . $search . '%',
                    'ImageGalleries.slug LIKE' => '%' . $search . '%',
                    'ImageGalleries.description LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        $imageGalleries = $this->paginate($query);

        // Handle AJAX requests
        if ($this->request->is('ajax')) {
            $this->set(compact('imageGalleries', 'viewType', 'search', 'statusFilter'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $this->set(compact('imageGalleries', 'viewType'));

        // Return appropriate template based on view type
        return $this->render($viewType === 'grid' ? 'index_grid' : 'index');
    }

    /**
     * View method
     *
     * @param string|null $id Image Gallery id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): ?Response
    {
        $imageGallery = $this->ImageGalleries->get($id, contain: [
            'Images' => [
                'sort' => ['ImageGalleriesImages.position' => 'ASC'],
            ],
            'Slugs',
        ]);
        $this->set(compact('imageGallery'));

        return null;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $imageGallery = $this->ImageGalleries->newEmptyEntity();
        if ($this->request->is('post')) {
            $imageGallery = $this->ImageGalleries->patchEntity($imageGallery, $this->request->getData());
            if ($this->ImageGalleries->save($imageGallery)) {
                // Handle file uploads if provided
                $uploadedFiles = $this->request->getUploadedFiles();
                $this->_processUploadsAndSetFlash($uploadedFiles, $imageGallery->id, 'saved');

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image gallery could not be saved. Please, try again.'));
        }
        $images = $this->ImageGalleries->Images->find('list', limit: 200)->all();
        $this->set(compact('imageGallery', 'images'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Image Gallery id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $imageGallery = $this->ImageGalleries->get($id, contain: ['Images']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $imageGallery = $this->ImageGalleries->patchEntity($imageGallery, $this->request->getData());

            // Handle file uploads if provided
            $uploadedFiles = $this->request->getUploadedFiles();
            if (!empty($uploadedFiles['image_files'])) {
                $this->_processUploadsAndSetFlash($uploadedFiles, $imageGallery->id, 'updated');
            }

            if ($this->ImageGalleries->save($imageGallery)) {
                if (empty($uploadedFiles['image_files'])) {
                    $this->Flash->success(__('The image gallery has been saved.'));
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image gallery could not be saved. Please, try again.'));
        }
        $images = $this->ImageGalleries->Images->find('list', limit: 200)->all();
        $this->set(compact('imageGallery', 'images'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Image Gallery id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $imageGallery = $this->ImageGalleries->get($id);
        if ($this->ImageGalleries->delete($imageGallery)) {
            $this->Flash->success(__('The image gallery has been deleted.'));
        } else {
            $this->Flash->error(__('The image gallery could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer(['action' => 'index']));
    }

    /**
     * Manage images in a gallery - drag and drop interface
     *
     * @param string|null $id Gallery id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function manageImages(?string $id = null): ?Response
    {
        $imageGallery = $this->ImageGalleries->get($id, contain: [
            'ImageGalleriesImages' => [
                'finder' => 'ordered',
                'Images' => [
                    'conditions' => [
                        'Images.image IS NOT' => null,
                        'Images.image !=' => '',
                    ],
                ],
            ],
        ]);

        $this->set(compact('imageGallery'));

        return null;
    }

    /**
     * Add images to a gallery (AJAX endpoint)
     *
     * @param string|null $id Gallery id.
     * @return \Cake\Http\Response JSON response
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function addImages(?string $id = null): Response
    {
        $this->request->allowMethod(['post']);

        $imageIds = $this->request->getData('image_ids', []);

        if (empty($imageIds)) {
            // For AJAX requests, return JSON
            if ($this->request->is('ajax')) {
                $response = [
                    'success' => false,
                    'message' => __('No images selected'),
                ];

                return $this->getResponse()
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode($response));
            }

            // For regular requests, redirect with flash message
            $this->Flash->error(__('No images selected'));

            return $this->redirect(['action' => 'manageImages', $id]);
        }

        $galleriesImagesTable = $this->fetchTable('ImageGalleriesImages');

        $added = 0;
        foreach ($imageIds as $imageId) {
            // Check if image is already in gallery
            $exists = $galleriesImagesTable->exists([
                'image_gallery_id' => $id,
                'image_id' => $imageId,
            ]);

            if (!$exists) {
                $position = $galleriesImagesTable->getNextPosition($id);
                $galleryImage = $galleriesImagesTable->newEntity([
                    'image_gallery_id' => $id,
                    'image_id' => $imageId,
                    'position' => $position,
                ]);

                if ($galleriesImagesTable->save($galleryImage)) {
                    $added++;
                }
            }
        }

        // For AJAX requests, return JSON
        if ($this->request->is('ajax')) {
            $response = [
                'success' => true,
                'message' => __('Added {0} images to gallery', $added),
                'added_count' => $added,
            ];

            return $this->getResponse()
                ->withType('application/json')
                ->withStringBody(json_encode($response));
        }

        // For regular requests, redirect with flash message
        if ($added > 0) {
            $this->Flash->success(__('Added {0} images to gallery', $added));
        } else {
            $this->Flash->warning(__('No new images were added (they may already be in the gallery)'));
        }

        return $this->redirect(['action' => 'manageImages', $id]);
    }

    /**
     * Remove image from gallery (AJAX endpoint)
     *
     * @param string|null $id Gallery id.
     * @param string|null $imageId Image id.
     * @return \Cake\Http\Response JSON response
     */
    public function removeImage(?string $id = null, ?string $imageId = null): Response
    {
        $this->request->allowMethod(['delete']);

        $galleriesImagesTable = $this->fetchTable('ImageGalleriesImages');

        $galleryImage = $galleriesImagesTable->find()
            ->where([
                'image_gallery_id' => $id,
                'image_id' => $imageId,
            ])
            ->first();

        if (!$galleryImage) {
            $response = [
                'success' => false,
                'message' => __('Image not found in gallery'),
            ];

            return $this->getResponse()
                ->withType('application/json')
                ->withStatus(404)
                ->withStringBody(json_encode($response));
        }

        if ($galleriesImagesTable->delete($galleryImage)) {
            $response = [
                'success' => true,
                'message' => __('Image removed from gallery'),
            ];
        } else {
            $response = [
                'success' => false,
                'message' => __('Failed to remove image from gallery'),
            ];
        }

        return $this->getResponse()
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }

    /**
     * Update image order in gallery (AJAX endpoint)
     *
     * @return \Cake\Http\Response JSON response
     */
    public function updateImageOrder(): Response
    {
        $this->request->allowMethod(['post']);

        $galleryId = $this->request->getData('gallery_id');
        $imageIds = $this->request->getData('image_ids', []);

        if (empty($galleryId) || empty($imageIds)) {
            $response = [
                'success' => false,
                'message' => __('Invalid data provided'),
            ];

            return $this->getResponse()
                ->withType('application/json')
                ->withStatus(400)
                ->withStringBody(json_encode($response));
        }

        $galleriesImagesTable = $this->fetchTable('ImageGalleriesImages');

        if ($galleriesImagesTable->reorderImages($galleryId, $imageIds)) {
            $response = [
                'success' => true,
                'message' => __('Image order updated'),
            ];
        } else {
            $response = [
                'success' => false,
                'message' => __('Failed to update image order'),
            ];
        }

        return $this->getResponse()
            ->withType('application/json')
            ->withStringBody(json_encode($response));
    }

    /**
     * Gallery picker for selecting galleries to insert into content
     * Uses MediaPickerTrait for DRY implementation
     *
     * @return \\Cake\\Http\\Response|null|void Renders view
     */
    public function picker(): ?Response
    {
        // Build query with trait helper
        $selectFields = [
            'ImageGalleries.id',
            'ImageGalleries.name',
            'ImageGalleries.slug',
            'ImageGalleries.description',
            'ImageGalleries.preview_image',
            'ImageGalleries.is_published',
            'ImageGalleries.created',
            'ImageGalleries.modified',
        ];

        $query = $this->buildPickerQuery($this->ImageGalleries, $selectFields, [
            'contain' => [
                'Images' => function ($q) {
                    return $q->select(['Images.id', 'Images.name', 'Images.image', 'Images.dir', 'Images.alt_text'])
                             ->limit(4) // Show first 4 images for preview
                             ->orderBy(['ImageGalleriesImages.position' => 'ASC']);
                },
            ],
        ]);

        // Handle search with trait helper
        $search = $this->request->getQuery('search');
        $searchFields = [
            'ImageGalleries.name',
            'ImageGalleries.slug',
            'ImageGalleries.description',
        ];
        $query = $this->handlePickerSearch($query, $search, $searchFields);

        // Setup pagination with trait helper
        $limit = $this->getRequestLimit(8, 24);
        $page = $this->getRequestPage();

        $galleries = $this->paginate($query, [
            'limit' => $limit,
            'page' => $page,
        ]);

        // Set variables for template (template expects $results)
        $results = $galleries;
        $this->set(compact('results', 'search'));
        $this->set('_serialize', ['results', 'search']);

        // Check if this is a search request that should only return results HTML
        $galleryOnly = $this->request->getQuery('gallery_only');
        if ($galleryOnly) {
            // For search requests, only return the results portion to avoid flicker
            $this->viewBuilder()->setTemplate('picker_results');
        } else {
            // For initial load, return the full template with search form
            $this->viewBuilder()->setTemplate('picker');
        }

        // Use AJAX view for modal content
        $this->viewBuilder()->setLayout('ajax');

        return null;
    }

    /**
     * Process uploaded files and set appropriate flash messages
     *
     * @param array $uploadedFiles Array of uploaded files
     * @param string $galleryId Gallery ID to associate files with
     * @param string $action Action being performed (saved|updated)
     * @return void
     */
    private function _processUploadsAndSetFlash(array $uploadedFiles, string $galleryId, string $action): void
    {
        if (empty($uploadedFiles['image_files'])) {
            $this->Flash->success(__('The image gallery has been {0}.', $action));

            return;
        }

        $uploadService = new ImageProcessingService(
            $this->fetchTable('Images'),
            $this->fetchTable('ImageGalleriesImages'),
            new ArchiveExtractor(),
        );

        $result = $uploadService->processUploadedFiles($uploadedFiles['image_files'], $galleryId);

        // Set flash message based on results
        if ($result['success_count'] > 0 && $result['error_count'] === 0) {
            $this->Flash->success(__(
                'Gallery {0} with {1} image(s) uploaded successfully.',
                $action,
                $result['success_count'],
            ));
        } elseif ($result['success_count'] > 0 && $result['error_count'] > 0) {
            $this->Flash->success(__(
                'Gallery {0} with {1} image(s) uploaded. {2} failed to upload.',
                $action,
                $result['success_count'],
                $result['error_count'],
            ));
        } elseif ($result['error_count'] > 0) {
            $this->Flash->warning(__(
                'Gallery {0}, but all {1} image(s) failed to upload.',
                $action,
                $result['error_count'],
            ));
        }
    }
}
