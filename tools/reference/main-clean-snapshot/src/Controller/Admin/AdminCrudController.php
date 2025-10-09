<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\ORM\Query;
use Cake\ORM\Table;

/**
 * AdminCrudController Base Class
 * 
 * Abstract base class that provides common CRUD functionality for admin controllers.
 * Eliminates ~500 lines of duplicated code across 12+ admin controllers.
 */
abstract class AdminCrudController extends AppController
{
    /**
     * The primary model this controller works with
     * @var \Cake\ORM\Table
     */
    protected Table $modelClass;

    /**
     * Default fields to select in index queries
     * @var array
     */
    protected array $indexFields = [];

    /**
     * Default fields to search in
     * @var array  
     */
    protected array $searchFields = [];

    /**
     * Default contain associations for queries
     * @var array
     */
    protected array $defaultContain = [];

    /**
     * Cache keys to clear after save/delete operations
     * @var array
     */
    protected array $cacheKeys = [];

    /**
     * Default order for index queries
     * @var array
     */
    protected array $defaultOrder = [];

    /**
     * Default conditions for index queries
     * @var array
     */
    protected array $defaultConditions = [];

    /**
     * Initialize method
     * Subclasses should call parent::initialize() and set up their specific properties
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->setupModelClass();
    }

    /**
     * Setup the model class - must be implemented by subclasses
     *
     * @return void
     */
    abstract protected function setupModelClass(): void;

    /**
     * Clear configured cache keys
     *
     * @return void
     */
    protected function clearCache(): void
    {
        foreach ($this->cacheKeys as $key) {
            Cache::clear($key);
        }
        
        // Also clear generic content cache
        Cache::clear('content');
    }

    /**
     * Build the base query for index listing
     *
     * @return \Cake\ORM\Query
     */
    protected function buildIndexQuery(): Query
    {
        $query = $this->modelClass->find();

        // Add default select fields if specified
        if (!empty($this->indexFields)) {
            $query->select($this->indexFields);
        }

        // Add default contain associations
        if (!empty($this->defaultContain)) {
            $query->contain($this->defaultContain);
        }

        // Add default conditions
        if (!empty($this->defaultConditions)) {
            $query->where($this->defaultConditions);
        }

        // Add default ordering
        if (!empty($this->defaultOrder)) {
            $query->orderBy($this->defaultOrder);
        }

        return $query;
    }

    /**
     * Apply status filter to query
     *
     * @param \Cake\ORM\Query $query
     * @param string|null $statusFilter
     * @return \Cake\ORM\Query
     */
    protected function applyStatusFilter(Query $query, ?string $statusFilter): Query
    {
        if ($statusFilter !== null) {
            // Handle boolean published status
            if ($statusFilter === '1' || $statusFilter === 'published') {
                $query->where([$this->modelClass->getAlias() . '.is_published' => true]);
            } elseif ($statusFilter === '0' || $statusFilter === 'unpublished') {
                $query->where([$this->modelClass->getAlias() . '.is_published' => false]);
            }
            // Handle verification status (for products, etc.)
            elseif (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
                $query->where([$this->modelClass->getAlias() . '.verification_status' => $statusFilter]);
            }
        }

        return $query;
    }

    /**
     * Apply search filter to query
     *
     * @param \Cake\ORM\Query $query  
     * @param string|null $search
     * @return \Cake\ORM\Query
     */
    protected function applySearchFilter(Query $query, ?string $search): Query
    {
        if (!empty($search) && !empty($this->searchFields)) {
            $conditions = [];
            foreach ($this->searchFields as $field) {
                $conditions[$field . ' LIKE'] = '%' . $search . '%';
            }
            $query->where(['OR' => $conditions]);
        }

        return $query;
    }

    /**
     * Handle AJAX search responses
     *
     * @param mixed $results
     * @param string $templateName
     * @return \Cake\Http\Response|null
     */
    protected function handleAjaxResponse($results, string $templateName = 'search_results'): ?Response
    {
        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            $this->set(compact('results', 'search'));
            $this->viewBuilder()->setLayout('ajax');
            return $this->render($templateName);
        }
        return null;
    }

    /**
     * Standard index method implementation
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $search = $this->request->getQuery('search');

        $query = $this->buildIndexQuery();
        $query = $this->applyStatusFilter($query, $statusFilter);
        $query = $this->applySearchFilter($query, $search);

        // Allow subclasses to modify the query further
        $query = $this->modifyIndexQuery($query);

        $results = $this->paginate($query);

        // Handle AJAX requests
        $ajaxResponse = $this->handleAjaxResponse($results);
        if ($ajaxResponse !== null) {
            return $ajaxResponse;
        }

        $this->set(compact('results'));
        return null;
    }

    /**
     * Hook for subclasses to modify the index query
     *
     * @param \Cake\ORM\Query $query
     * @return \Cake\ORM\Query
     */
    protected function modifyIndexQuery(Query $query): Query
    {
        return $query;
    }

    /**
     * Standard view method implementation
     *
     * @param string|null $id
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function view(?string $id = null): ?Response
    {
        $entity = $this->modelClass->get($id, [
            'contain' => $this->defaultContain
        ]);

        $this->set([
            'entity' => $entity,
            strtolower($this->modelClass->getAlias()) => $entity
        ]);

        return null;
    }

    /**
     * Standard add method implementation
     *
     * @return \Cake\Http\Response|null
     */
    public function add(): ?Response
    {
        $entity = $this->modelClass->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Allow subclasses to modify data before save
            $data = $this->modifyDataBeforeSave($data, $entity);
            
            $entity = $this->modelClass->patchEntity($entity, $data);
            
            if ($this->modelClass->save($entity)) {
                $this->clearCache();
                $this->Flash->success($this->getSuccessMessage('saved'));
                
                return $this->redirect($this->getRedirectLocation('add', $entity));
            }
            
            $this->Flash->error($this->getErrorMessage('save'));
        }

        // Allow subclasses to provide additional data for the form
        $additionalData = $this->getAdditionalFormData($entity);
        
        $this->set(array_merge([
            'entity' => $entity,
            strtolower($this->modelClass->getAlias()) => $entity
        ], $additionalData));

        return null;
    }

    /**
     * Standard edit method implementation
     *
     * @param string|null $id
     * @return \Cake\Http\Response|null  
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function edit(?string $id = null): ?Response
    {
        $entity = $this->modelClass->get($id, [
            'contain' => $this->defaultContain
        ]);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Allow subclasses to modify data before save
            $data = $this->modifyDataBeforeSave($data, $entity);
            
            $entity = $this->modelClass->patchEntity($entity, $data);
            
            if ($this->modelClass->save($entity)) {
                $this->clearCache();
                $this->Flash->success($this->getSuccessMessage('updated'));
                
                return $this->redirect($this->getRedirectLocation('edit', $entity));
            }
            
            $this->Flash->error($this->getErrorMessage('update'));
        }

        // Allow subclasses to provide additional data for the form
        $additionalData = $this->getAdditionalFormData($entity);
        
        $this->set(array_merge([
            'entity' => $entity,
            strtolower($this->modelClass->getAlias()) => $entity
        ], $additionalData));

        return null;
    }

    /**
     * Standard delete method implementation
     *
     * @param string|null $id
     * @return \Cake\Http\Response
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $entity = $this->modelClass->get($id);
        
        if ($this->modelClass->delete($entity)) {
            $this->clearCache();
            $this->Flash->success($this->getSuccessMessage('deleted'));
        } else {
            $this->Flash->error($this->getErrorMessage('delete'));
        }

        return $this->redirect($this->referer(['action' => 'index']));
    }

    /**
     * Hook for subclasses to modify data before save operations
     *
     * @param array $data
     * @param mixed $entity
     * @return array
     */
    protected function modifyDataBeforeSave(array $data, $entity): array
    {
        return $data;
    }

    /**
     * Hook for subclasses to provide additional data for forms
     *
     * @param mixed $entity
     * @return array
     */
    protected function getAdditionalFormData($entity): array
    {
        return [];
    }

    /**
     * Get success message for operations
     *
     * @param string $operation
     * @return string
     */
    protected function getSuccessMessage(string $operation): string
    {
        $entityName = $this->getEntityDisplayName();
        
        return match($operation) {
            'saved' => __('The {0} has been saved.', $entityName),
            'updated' => __('The {0} has been updated.', $entityName),
            'deleted' => __('The {0} has been deleted.', $entityName),
            default => __('Operation completed successfully.')
        };
    }

    /**
     * Get error message for operations
     *
     * @param string $operation
     * @return string
     */
    protected function getErrorMessage(string $operation): string
    {
        $entityName = $this->getEntityDisplayName();
        
        return match($operation) {
            'save' => __('The {0} could not be saved. Please, try again.', $entityName),
            'update' => __('The {0} could not be updated. Please, try again.', $entityName),
            'delete' => __('The {0} could not be deleted. Please, try again.', $entityName),
            default => __('An error occurred. Please, try again.')
        };
    }

    /**
     * Get human-readable entity name for messages
     *
     * @return string
     */
    protected function getEntityDisplayName(): string
    {
        // Convert from "ImageGalleries" to "image gallery"
        $name = $this->modelClass->getAlias();
        $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name);
        return strtolower($name);
    }

    /**
     * Get redirect location after save operations
     *
     * @param string $action
     * @param mixed $entity
     * @return array
     */
    protected function getRedirectLocation(string $action, $entity): array
    {
        return ['action' => 'index'];
    }
}