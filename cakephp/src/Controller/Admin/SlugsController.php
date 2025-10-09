<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;
use Exception;

/**
 * Slugs Controller
 *
 * Handles administration of URL slugs across the application.
 * Provides CRUD operations for managing slugs and their relationships
 * with various content types (Articles, etc.).
 *
 * @property \App\Model\Table\SlugsTable $Slugs
 * @method \App\Model\Entity\Slug[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SlugsController extends AppController
{
    /**
     * Index method
     *
     * Lists all slugs with filtering and search capabilities.
     * Handles both regular and AJAX requests, displaying related content information
     * for each slug.
     *
     * Features:
     * - Search filtering by slug text
     * - Status filtering by model type
     * - Efficient fetching of related content to avoid N+1 query issues
     * - AJAX support for dynamic updates
     *
     * @return \Cake\Http\Response|null Returns Response for AJAX requests, null otherwise
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status'); // This is now 'model' filter
        $search = $this->request->getQuery('search');

        // Get all unique model types from the slugs table
        $modelTypes = $this->Slugs->find()
            ->select(['model'])
            ->distinct('model')
            ->orderBy(['model' => 'ASC'])
            ->all()
            ->map(fn($row) => ucfirst($row->model))
            ->toArray();

        $query = $this->Slugs->find()
            ->select([
                'Slugs.id',
                'Slugs.model',
                'Slugs.foreign_key',
                'Slugs.slug',
                'Slugs.created',
            ])
            ->orderBy(['Slugs.created' => 'DESC']);

        if (!empty($statusFilter)) {
            $query->where(['Slugs.model' => $statusFilter]);
        }

        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Slugs.slug LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        // Paginate the slugs. $slugs will be a ResultSet (or similar traversable object).
        // This is crucial for PaginatorComponent to attach pagination metadata.
        $slugs = $this->paginate($query);

        // Optimize fetching related records to avoid N+1 queries
        $groupedSlugs = [];
        // Iterate directly over the ResultSet returned by paginate()
        foreach ($slugs as $slug) {
            $groupedSlugs[$slug->model][] = $slug;
        }

        $relatedData = [];
        foreach ($groupedSlugs as $modelName => $modelSlugs) {
            $foreignKeys = array_column($modelSlugs, 'foreign_key');

            // Skip if no foreign keys for this model (e.g., if pagination resulted in no slugs for a model)
            if (empty($foreignKeys)) {
                continue;
            }

            try {
                $relatedTable = $this->fetchTable($modelName);

                // Define select fields based on the model type
                $selectFields = ['id', 'title'];
                if ($modelName === 'Articles') {
                    $selectFields[] = 'kind';
                    $selectFields[] = 'is_published';
                }

                $relatedRecords = $relatedTable->find()
                    ->select($selectFields)
                    ->where(['id IN' => $foreignKeys])
                    ->all()
                    ->indexBy('id') // Index by ID for easy lookup
                    ->toArray();

                foreach ($modelSlugs as $slug) {
                    if (isset($relatedRecords[$slug->foreign_key])) {
                        $relatedRecord = $relatedRecords[$slug->foreign_key];
                        $relatedData[$slug->id] = [
                            'title' => $relatedRecord->title,
                            'controller' => $modelName, // 'Articles', 'Tags', etc.
                            'id' => $relatedRecord->id,
                        ];

                        // Add specific fields for Articles
                        if ($modelName === 'Articles') {
                            $relatedData[$slug->id]['kind'] = $relatedRecord->kind;
                            $relatedData[$slug->id]['is_published'] = $relatedRecord->is_published;
                        }
                    } else {
                        // Handle cases where the related record might have been deleted
                        $this->log(sprintf(
                            'Related record for slug %s (model: %s, foreign_key: %s) not found.',
                            $slug->id,
                            $modelName,
                            $slug->foreign_key,
                        ), 'warning');
                        $relatedData[$slug->id] = [
                            'title' => __('(Deleted)'),
                            'controller' => $modelName,
                            'id' => null, // Indicate that the record is missing
                        ];
                    }
                }
            } catch (Exception $e) {
                $this->log(sprintf(
                    'Failed to fetch related records for model %s: %s',
                    $modelName,
                    $e->getMessage(),
                ), 'error');
                // For all slugs associated with this problematic model, mark them as unretrievable
                foreach ($modelSlugs as $slug) {
                    $relatedData[$slug->id] = [
                        'title' => __('(Error loading)'),
                        'controller' => $modelName,
                        'id' => null,
                    ];
                }
            }
        }

        if ($this->request->is('ajax')) {
            // Pass the original $slugs (ResultSet) to the view
            $this->set(compact('slugs', 'search', 'relatedData', 'modelTypes', 'statusFilter'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        // Pass the original $slugs (ResultSet) to the view
        $this->set(compact('slugs', 'relatedData', 'modelTypes', 'statusFilter'));

        return null;
    }

    /**
     * View method
     *
     * Displays detailed information about a specific slug and its associated content.
     * Dynamically loads the related record based on the slug's model type.
     *
     * @param string|null $id The UUID of the slug to view
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When slug not found
     */
    public function view(?string $id = null): void
    {
        $slug = $this->Slugs->get($id);

        // Get the related record if possible
        $relatedRecord = null;
        if ($slug->model && $slug->foreign_key) {
            try {
                $relatedTable = $this->fetchTable($slug->model);

                // Build the query based on the model type
                $query = $relatedTable->find()
                    ->where(['id' => $slug->foreign_key]);

                // Add specific fields for Articles
                if ($slug->model === 'Articles') {
                    $query->select(['id', 'title', 'kind', 'slug', 'is_published']);
                } else {
                    $query->select(['id', 'title', 'slug']);
                }

                $relatedRecord = $query->first();

                if (!$relatedRecord) {
                    $this->Flash->warning(__(
                        'The related {0} record (ID: {1}) could not be found.',
                        $slug->model,
                        $slug->foreign_key,
                    ));
                    $this->log(sprintf(
                        'Related record not found for slug %s (model: %s, foreign_key: %s)',
                        $slug->id,
                        $slug->model,
                        $slug->foreign_key,
                    ), 'warning');
                }
            } catch (Exception $e) {
                $this->Flash->error(__('Unable to load related {0} record.', $slug->model));
                $this->log(sprintf(
                    'Failed to fetch related record for slug %s (model: %s, foreign_key: %s): %s',
                    $slug->id,
                    $slug->model,
                    $slug->foreign_key,
                    $e->getMessage(),
                ), 'error');
            }
        }

        // Get all slugs for this model/foreign_key combination
        $relatedSlugs = $this->Slugs->find()
            ->where([
                'model' => $slug->model,
                'foreign_key' => $slug->foreign_key,
                'id !=' => $slug->id,
            ])
            ->orderBy(['created' => 'DESC'])
            ->all();

        $this->set(compact('slug', 'relatedRecord', 'relatedSlugs'));
    }

    /**
     * Add method
     *
     * Creates a new slug record with associated content relationship.
     * Provides form with model selection and related content options.
     *
     * @return \Cake\Http\Response|null|void Redirects to index on success, renders view otherwise
     */
    public function add(): ?Response
    {
        $slug = $this->Slugs->newEmptyEntity();

        // Get all unique model types from the slugs table
        $modelTypes = $this->Slugs->find()
            ->select(['model'])
            ->distinct('model')
            ->orderBy(['model' => 'ASC'])
            ->all()
            ->map(fn($row) => $row->model)
            ->toArray();

        if ($this->request->is('post')) {
            $slug = $this->Slugs->patchEntity($slug, $this->request->getData());
            if ($this->Slugs->save($slug)) {
                $this->Flash->success(__('The slug has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The slug could not be saved. Please, try again.'));
        }

        // Get the selected model (either from form data or default to first model)
        $selectedModel = $this->request->getData('model') ?? ($modelTypes[0] ?? null);

        // If we have a selected model, get its records
        $relatedRecords = [];
        if ($selectedModel) {
            try {
                $relatedRecords = $this->fetchTable($selectedModel)
                    ->find('list', limit: 200)
                    ->all();
            } catch (Exception $e) {
                $this->Flash->error(__('Unable to load related records for {0}.', $selectedModel));
                $this->log(sprintf(
                    'Failed to fetch related records for model %s: %s',
                    $selectedModel,
                    $e->getMessage(),
                ), 'error');
            }
        }

        $this->set(compact('slug', 'modelTypes', 'relatedRecords', 'selectedModel'));

        return null;
    }

    /**
     * Edit method
     *
     * Modifies an existing slug record and its relationships.
     * Provides form with current values and content selection options.
     *
     * @param string|null $id The UUID of the slug to edit
     * @return \Cake\Http\Response|null|void Redirects to index on success, renders view otherwise
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When slug not found
     */
    public function edit(?string $id = null): ?Response
    {
        $slug = $this->Slugs->find()
            ->where(['id' => $id])
            ->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $slug = $this->Slugs->patchEntity($slug, $this->request->getData());
            if ($this->Slugs->save($slug)) {
                $this->Flash->success(__('The slug has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The slug could not be saved. Please, try again.'));
        }

        // Get related records based on the model type
        $relatedRecords = $this->fetchTable($slug->model)->find('list', limit: 200)->all();
        $this->set(compact('relatedRecords'));

        $this->set(compact('slug'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Slug id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $slug = $this->Slugs->get($id);
        if ($this->Slugs->delete($slug)) {
            $this->Flash->success(__('The slug has been deleted.'));
        } else {
            $this->Flash->error(__('The slug could not be deleted. Please, try again.'));
        }

        return $this->redirect(['prefix' => 'Admin', 'controller' => 'slugs', 'action' => 'index']);
    }
}
