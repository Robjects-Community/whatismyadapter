<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Controller\Component\MediaPickerTrait;
use App\Service\ImageProcessingService;
use App\Utility\ArchiveExtractor;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\JsonView;
use Exception;
// use Cake\Http\Exception\BadRequestException; // Not strictly needed if handling error codes directly

/**
 * Images Controller
 *
 * Manages CRUD operations for images and handles image selection for the Trumbowyg and Markdown-It editors.
 *
 * @property \App\Model\Table\ImagesTable $Images
 */
class ImagesController extends AppController
{
    use MediaPickerTrait;

    /**
     * Specifies the view classes supported by this controller.
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * Lists images with support for standard and AJAX requests.
     *
     * @return \Cake\Http\Response The response object containing the rendered view.
     */
    public function index(): Response
    {
        $session = $this->request->getSession();
        $viewType = $this->request->getQuery('view');

        // Check if view type is provided in the query, otherwise use session value or default to 'list'
        if ($viewType) {
            $session->write('Images.viewType', $viewType);
        } else {
            $viewType = $session->read('Images.viewType', 'grid');
        }

        $query = $this->Images->find()
            ->select([
                'Images.id',
                'Images.name',
                'Images.image',
                'Images.dir',
                'Images.alt_text',
                'Images.keywords',
                'Images.created',
                'Images.modified',
            ]);

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'name LIKE' => '%' . $search . '%',
                    'alt_text LIKE' => '%' . $search . '%',
                    'keywords LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $images = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('images', 'viewType', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('images', 'viewType'));

        return $this->render($viewType === 'grid' ? 'index_grid' : 'index');
    }

    /**
     * Displays details of a specific image.
     *
     * @param string|null $id Image id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $image = $this->Images->get($id, contain: []);
        $this->set(compact('image'));
    }

    /**
     * Handles the selection of images for the Trumbowyg editor.
     *
     * This method sets up pagination for the images with a maximum limit of 8 per page.
     * It allows searching through images based on their name, alt text, or keywords.
     * The search query is retrieved from the request's query parameters.
     *
     * If a search term is provided, it filters the images accordingly.
     * The filtered images are then paginated and set to be available in the view.
     *
     * Additionally, it checks if only the gallery should be loaded based on the 'gallery_only'
     * query parameter. If true, it sets the template to 'image_gallery' and uses a minimal layout.
     * Otherwise, it uses a minimal layout without changing the template.
     *
     * @return void
     */
    public function imageSelect(): void
    {
        $limit = min((int)$this->request->getQuery('limit', 12), 24);
        $this->paginate = [
            'limit' => $limit,
            'maxLimit' => 24,
            'order' => ['Images.created' => 'DESC'],
        ];

        $query = $this->Images->find();
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Images.name LIKE' => '%' . $search . '%',
                    'Images.alt_text LIKE' => '%' . $search . '%',
                    'Images.keywords LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        $images = $this->paginate($query);
        $this->set(compact('images', 'search'));

        // Check if this is a search request that should only return results HTML
        $galleryOnly = $this->request->getQuery('gallery_only');
        if ($galleryOnly) {
            // For search requests, only return the results portion to avoid flicker
            $this->viewBuilder()->setTemplate('image_select_results');
        } else {
            // For initial load, return the full template with search form
            $this->viewBuilder()->setTemplate('image_select');
        }

        $this->viewBuilder()->setLayout('ajax');
    }

    /**
     * Adds a new image.
     *
     * This method handles the creation of a new image entity. It uses the 'create'
     * validation ruleset when processing the submitted form data. On successful save,
     * it redirects to the index action. If the save fails, it displays an error message.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $image = $this->Images->newEmptyEntity();
        if ($this->request->is('post')) {
            $image = $this->Images->patchEntity($image, $this->request->getData(), ['validate' => 'create']);
            if ($this->Images->save($image)) {
                $this->Flash->success(__('The image has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image could not be saved. Please, try again.'));
        }
        $this->set(compact('image'));

        return null;
    }

    /**
     * Handles bulk upload of images, showing the upload form for GET requests
     * and processing AJAX uploads for POST requests.
     *
     * @return \Cake\Http\Response|null Returns Response for AJAX requests, void for GET
     */
    public function bulkUpload(): ?Response
    {
        $this->request->allowMethod(['get', 'post']);
        $response = $this->getResponse(); // Use getResponse() to get the current response object

        if ($this->request->is('get')) {
            // Render the bulk_upload.php template by default for GET
            return null;
        }

        // AJAX POST request handling
        // JsonView will be used automatically due to viewClasses() and Accept header

        $uploadedFile = $this->request->getUploadedFile('image');

        // Default error
        $apiResponse = [
            'success' => false,
            'message' => __('An unexpected error occurred.'),
        ];
        $statusCode = 500;

        if (!$uploadedFile) {
            $apiResponse['message'] = __('No file was uploaded or file key "image" is missing.');
            $statusCode = 400; // Bad Request
        } else {
            try {
                $uploadService = new ImageProcessingService(
                    $this->Images,
                    $this->fetchTable('ImageGalleriesImages'),
                    new ArchiveExtractor(),
                );

                $result = $uploadService->processUploadedFiles([$uploadedFile]);

                if ($result['success']) {
                    if ($result['success_count'] === 1) {
                        // Single image uploaded successfully
                        $image = $result['created_images'][0];
                        $apiResponse = [
                            'success' => true,
                            'message' => __('Image "{0}" uploaded successfully.', $image['name']),
                            'image' => $image,
                        ];
                        $statusCode = 201; // Created
                    } else {
                        // Multiple images from archive
                        $apiResponse = [
                            'success' => true,
                            'message' => $result['message'],
                            'images' => $result['created_images'],
                            'total_processed' => $result['total_processed'],
                            'success_count' => $result['success_count'],
                            'error_count' => $result['error_count'],
                        ];

                        if ($result['error_count'] > 0) {
                            $apiResponse['errors'] = $result['errors'];
                        }

                        $statusCode = 201; // Created
                    }
                } else {
                    // Failed to process
                    $apiResponse = [
                        'success' => false,
                        'message' => $result['message'],
                        'errors' => $result['errors'],
                    ];
                    $statusCode = 422; // Unprocessable Entity
                }
            } catch (Exception $e) {
                $this->log("Bulk upload: Processing failed: {$e->getMessage()}", 'error');
                $apiResponse = [
                    'success' => false,
                    'message' => __('Failed to process the uploaded file.'),
                ];
                $statusCode = 500;
            }
        }

        // Suggestion 3: Use setOption('serialize', ...)
        $this->set($apiResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($apiResponse));

        return $response->withStatus($statusCode); // Suggestion 4: Return appropriate HTTP status
    }

    /**
     * Edits an existing image.
     *
     * @param string|null $id Image id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $image = $this->Images->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $image = $this->Images->patchEntity($image, $this->request->getData(), ['validate' => 'update']);
            if ($this->Images->save($image)) {
                $this->Flash->success(__('The image has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image could not be saved. Please, try again.'));
        }
        $this->set(compact('image'));

        return null;
    }

    /**
     * Deletes an image.
     *
     * @param string|null $id Image id.
     * @return \Cake\Http\Response Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $image = $this->Images->get($id);
        if ($this->Images->delete($image)) {
            $this->Flash->success(__('The image has been deleted.'));
        } else {
            $this->Flash->error(__('The image could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer(['action' => 'index']));
    }

    /**
     * Deletes an image that was uploaded via bulk uploader.
     * Intended to be called by Dropzone's removedfile event.
     *
     * @param string|null $id Image id.
     * @return \Cake\Http\Response
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     * @throws \Cake\Http\Exception\MethodNotAllowedException If not a DELETE request.
     */
    public function deleteUploadedImage(?string $id = null): Response
    {
        $this->request->allowMethod(['delete']); // Suggestion 9: New action
        $response = $this->getResponse();

        $apiResponse = [
            'success' => false,
            'message' => __('Image could not be deleted.'),
        ];
        $statusCode = 500;

        if (!$id) {
            $apiResponse['message'] = __('No image ID provided.');
            $statusCode = 400;
        } else {
            try {
                $image = $this->Images->get($id);
                if ($this->Images->delete($image)) {
                    // Note: Ensure your ImagesTable->delete() or afterDelete event handles file system deletion.
                    $apiResponse = [
                        'success' => true,
                        'message' => __('Image "{0}" deleted successfully from server.', $image->name),
                    ];
                    $statusCode = 200; // OK
                } else {
                    $apiResponse['message'] = __('The image database record could not be deleted.');
                    // Errors might be on $image->getErrors() if the delete was blocked by rules.
                    if ($image->hasErrors()) {
                        $apiResponse['errors'] = $image->getErrors();
                        $statusCode = 422; // If rule-based delete failure
                    }
                }
            } catch (NotFoundException $e) {
                $apiResponse['message'] = __('Image not found.');
                $statusCode = 404; // Not Found
            } catch (Exception $e) {
                $this->log("Error_deleting_uploaded_image_{$id}: " . $e->getMessage(), 'error');
                $apiResponse['message'] = __('An unexpected error occurred while deleting the image.');
                $statusCode = 500;
            }
        }

        $this->set($apiResponse);
        $this->viewBuilder()->setOption('serialize', array_keys($apiResponse));

        return $response->withStatus($statusCode);
    }

    /**
     * Image picker for selecting images to add to galleries
     * Uses MediaPickerTrait for DRY implementation
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function picker(): ?Response
    {
        $galleryId = $this->request->getQuery('gallery_id');
        $viewType = $this->request->getQuery('view', 'grid');

        // Build query with trait helper
        $selectFields = [
            'Images.id',
            'Images.name',
            'Images.alt_text',
            'Images.keywords',
            'Images.image',
            'Images.dir',
            'Images.size',
            'Images.mime',
            'Images.created',
            'Images.modified',
        ];

        $query = $this->buildPickerQuery($this->Images, $selectFields);

        // Apply exclusion filter if gallery_id provided
        if ($galleryId) {
            $query = $this->applyPickerExclusion(
                $query,
                $this->fetchTable('ImageGalleriesImages'),
                'image_gallery_id',
                $galleryId,
                'image_id',
            );
        }

        // Handle search with trait helper
        $search = $this->request->getQuery('search');
        $searchFields = [
            'Images.name',
            'Images.alt_text',
            'Images.keywords',
        ];
        $query = $this->handlePickerSearch($query, $search, $searchFields);

        $images = $this->paginate($query);

        // Handle AJAX requests with trait helper
        $ajaxResponse = $this->handlePickerAjaxResponse($images, $search, 'picker_search_results');
        if ($ajaxResponse) {
            $this->set(compact('galleryId', 'viewType'));

            return $ajaxResponse;
        }

        $this->set(compact('images', 'galleryId', 'viewType'));

        // Return appropriate template based on view type
        return $this->render($viewType === 'grid' ? 'picker_grid' : 'picker');
    }
}
