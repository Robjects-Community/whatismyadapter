<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Http\Response;
use Cake\ORM\Query;
use Cake\ORM\Table;

/**
 * MediaPickerTrait
 *
 * Provides shared functionality for media picker methods across controllers.
 * Handles common patterns for search, pagination, and AJAX responses in picker interfaces.
 */
trait MediaPickerTrait
{
    /**
     * Build base picker query with common select fields and ordering
     *
     * @param \Cake\ORM\Table $table The table to query
     * @param array $selectFields Fields to select
     * @param array $options Additional query options
     * @return \Cake\ORM\Query
     */
    protected function buildPickerQuery(Table $table, array $selectFields, array $options = []): Query
    {
        $query = $table->find()->select($selectFields);

        // Apply default ordering
        if (isset($options['order'])) {
            $query->orderBy($options['order']);
        } else {
            $query->orderBy([$table->getAlias() . '.created' => 'DESC']);
        }

        // Apply any containments
        if (isset($options['contain'])) {
            $query->contain($options['contain']);
        }

        return $query;
    }

    /**
     * Handle search filtering for picker queries
     *
     * @param \Cake\ORM\Query $query Query to filter
     * @param string|null $searchTerm Search term
     * @param array $searchFields Fields to search in
     * @return \Cake\ORM\Query
     */
    protected function handlePickerSearch(Query $query, ?string $searchTerm, array $searchFields): Query
    {
        if (!empty($searchTerm)) {
            $conditions = [];
            foreach ($searchFields as $field) {
                $conditions[] = [$field . ' LIKE' => '%' . $searchTerm . '%'];
            }
            $query->where(['OR' => $conditions]);
        }

        return $query;
    }

    /**
     * Setup pagination configuration for picker
     *
     * @param array $options Pagination options
     * @return array
     */
    protected function setupPickerPagination(array $options = []): array
    {
        $defaults = [
            'limit' => 12,
            'maxLimit' => 24,
        ];

        return array_merge($defaults, $options);
    }

    /**
     * Handle picker AJAX response
     *
     * @param mixed $results Results to return
     * @param string|null $search Search term
     * @param string $template Template to render for AJAX
     * @return \Cake\Http\Response|null
     */
    protected function handlePickerAjaxResponse(mixed $results, ?string $search, string $template): ?Response
    {
        if ($this->request->is('ajax')) {
            $this->set(compact('results', 'search'));
            $this->set('_serialize', ['results', 'search']);
            $this->viewBuilder()->setLayout('ajax');

            return $this->render($template);
        }

        return null;
    }

    /**
     * Apply exclusion filter for picker (e.g., exclude images already in gallery)
     *
     * @param \Cake\ORM\Query $query Query to filter
     * @param \Cake\ORM\Table $pivotTable Pivot table for relationships
     * @param string $foreignKey Foreign key field name
     * @param string $recordId Record ID to exclude related items from
     * @param string $excludeField Field name to exclude
     * @return \Cake\ORM\Query
     */
    protected function applyPickerExclusion(
        Query $query,
        Table $pivotTable,
        string $foreignKey,
        string $recordId,
        string $excludeField,
    ): Query {
        $excludeIds = $pivotTable
            ->find()
            ->select([$excludeField])
            ->where([$foreignKey => $recordId])
            ->all()
            ->extract($excludeField)
            ->toArray();

        if (!empty($excludeIds)) {
            $tableAlias = $query->getRepository()->getAlias();
            $query->where([$tableAlias . '.id NOT IN' => $excludeIds]);
        }

        return $query;
    }

    /**
     * Handle request limit parameter with validation
     *
     * @param int $default Default limit
     * @param int $max Maximum allowed limit
     * @return int
     */
    protected function getRequestLimit(int $default = 12, int $max = 24): int
    {
        return min((int)$this->request->getQuery('limit', $default), $max);
    }

    /**
     * Get current page from request
     *
     * @param int $default Default page number
     * @return int
     */
    protected function getRequestPage(int $default = 1): int
    {
        return (int)$this->request->getQuery('page', $default);
    }
}
