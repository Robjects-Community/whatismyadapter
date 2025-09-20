<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Cake\Http\Response;
use Cake\ORM\Query;

/**
 * AdminSearchTrait
 * 
 * Provides standardized AJAX search functionality for admin controllers.
 * Eliminates duplicated JavaScript and search patterns across 12+ controllers.
 */
trait AdminSearchTrait
{
    /**
     * Search configuration
     * Override in controllers to customize search behavior
     * 
     * @var array
     */
    protected array $searchConfig = [
        'fields' => [],
        'template' => 'search_results',
        'conditions' => [],
        'contain' => [],
        'limit' => null,
        'order' => []
    ];

    /**
     * Build search query with standardized filters
     *
     * @param \Cake\ORM\Query $query Base query
     * @param array $params Search parameters
     * @return \Cake\ORM\Query
     */
    protected function buildSearchQuery(Query $query, array $params = []): Query
    {
        // Extract search parameters
        $search = $params['search'] ?? $this->request->getQuery('search');
        $status = $params['status'] ?? $this->request->getQuery('status');
        $filter = $params['filter'] ?? $this->request->getQuery('filter');
        
        // Apply search conditions
        if (!empty($search) && !empty($this->searchConfig['fields'])) {
            $searchConditions = [];
            foreach ($this->searchConfig['fields'] as $field) {
                $searchConditions[$field . ' LIKE'] = '%' . $search . '%';
            }
            $query->where(['OR' => $searchConditions]);
        }

        // Apply status filter (common pattern)
        if ($status !== null && $status !== '') {
            $statusField = $this->getStatusField();
            if ($statusField) {
                if (in_array($status, ['1', 'published', 'active'])) {
                    $query->where([$statusField => true]);
                } elseif (in_array($status, ['0', 'unpublished', 'inactive'])) {
                    $query->where([$statusField => false]);
                } else {
                    $query->where([$statusField => $status]);
                }
            }
        }

        // Apply additional conditions
        if (!empty($this->searchConfig['conditions'])) {
            $query->where($this->searchConfig['conditions']);
        }

        // Apply contain associations
        if (!empty($this->searchConfig['contain'])) {
            $query->contain($this->searchConfig['contain']);
        }

        // Apply ordering
        if (!empty($this->searchConfig['order'])) {
            $query->orderBy($this->searchConfig['order']);
        }

        // Apply limit
        if ($this->searchConfig['limit']) {
            $query->limit($this->searchConfig['limit']);
        }

        // Allow controller-specific query modifications
        return $this->modifySearchQuery($query, $params);
    }

    /**
     * Hook for controller-specific query modifications
     *
     * @param \Cake\ORM\Query $query
     * @param array $params
     * @return \Cake\ORM\Query
     */
    protected function modifySearchQuery(Query $query, array $params = []): Query
    {
        return $query;
    }

    /**
     * Get the status field name for this model
     * Common patterns: is_published, is_active, status, verification_status
     *
     * @return string|null
     */
    protected function getStatusField(): ?string
    {
        $table = $this->modelClass ?? $this->{$this->defaultTable};
        $schema = $table->getSchema();
        
        // Check common status field names
        $commonFields = [
            'is_published',
            'is_active', 
            'published',
            'active',
            'status',
            'verification_status'
        ];
        
        foreach ($commonFields as $field) {
            if ($schema->hasColumn($field)) {
                return $table->getAlias() . '.' . $field;
            }
        }
        
        return null;
    }

    /**
     * Execute search and return AJAX response
     *
     * @param \Cake\ORM\Query $query
     * @param array $additionalVars Additional variables for the view
     * @return \Cake\Http\Response|null
     */
    protected function executeSearch(Query $query, array $additionalVars = []): ?Response
    {
        $results = $this->paginate($query);
        
        if ($this->request->is('ajax')) {
            $viewVars = array_merge([
                'results' => $results,
                'search' => $this->request->getQuery('search'),
                'status' => $this->request->getQuery('status')
            ], $additionalVars);
            
            $this->set($viewVars);
            $this->viewBuilder()->setLayout('ajax');
            
            return $this->render($this->searchConfig['template']);
        }
        
        return null;
    }

    /**
     * Standard search action implementation
     * Controllers can call this directly or use it as a base
     *
     * @return \Cake\Http\Response|null
     */
    public function search(): ?Response
    {
        $this->request->allowMethod(['get', 'post']);
        
        // Build base query - controllers should override getSearchQuery()
        $query = $this->getSearchQuery();
        
        // Apply search filters
        $query = $this->buildSearchQuery($query);
        
        // Execute search and return response
        return $this->executeSearch($query);
    }

    /**
     * Get the base query for search - must be implemented by controllers
     *
     * @return \Cake\ORM\Query
     */
    abstract protected function getSearchQuery(): Query;

    /**
     * Generate search form HTML (for consistency)
     *
     * @param array $options
     * @return string
     */
    protected function renderSearchForm(array $options = []): string
    {
        $defaults = [
            'placeholder' => __('Search...'),
            'showStatus' => true,
            'statusOptions' => [
                '' => __('All'),
                '1' => __('Published'),
                '0' => __('Unpublished')
            ],
            'formId' => 'search-form',
            'inputId' => 'search-input',
            'statusId' => 'status-filter'
        ];
        
        $options = array_merge($defaults, $options);
        
        ob_start();
        ?>
        <form id="<?= h($options['formId']) ?>" class="d-flex align-items-center gap-3 mb-3">
            <div class="flex-grow-1">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input 
                        type="text" 
                        id="<?= h($options['inputId']) ?>" 
                        name="search" 
                        class="form-control" 
                        placeholder="<?= h($options['placeholder']) ?>"
                        value="<?= h($this->request->getQuery('search')) ?>"
                        autocomplete="off"
                    >
                    <?php if ($this->request->getQuery('search')): ?>
                    <button type="button" id="clear-search" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($options['showStatus']): ?>
            <div class="flex-shrink-0">
                <select id="<?= h($options['statusId']) ?>" name="status" class="form-select">
                    <?php foreach ($options['statusOptions'] as $value => $label): ?>
                    <option value="<?= h($value) ?>" <?= $this->request->getQuery('status') == $value ? 'selected' : '' ?>>
                        <?= h($label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </form>
        
        <div id="search-results" class="mt-3">
            <!-- AJAX results will be loaded here -->
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('<?= h($options['formId']) ?>');
            const searchInput = document.getElementById('<?= h($options['inputId']) ?>');
            const statusSelect = document.getElementById('<?= h($options['statusId']) ?>');
            const clearBtn = document.getElementById('clear-search');
            const resultsContainer = document.getElementById('search-results');
            
            let searchTimeout;
            
            function performSearch() {
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                
                // Add AJAX header
                fetch(window.location.pathname + '?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                })
                .catch(error => {
                    console.error('Search error:', error);
                    resultsContainer.innerHTML = '<div class="alert alert-danger">Search failed. Please try again.</div>';
                });
            }
            
            // Real-time search
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300);
            });
            
            // Status filter change
            if (statusSelect) {
                statusSelect.addEventListener('change', performSearch);
            }
            
            // Clear search
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    performSearch();
                });
            }
            
            // Initial search if there are existing parameters
            if (searchInput.value || (statusSelect && statusSelect.value)) {
                performSearch();
            }
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
}