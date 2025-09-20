<?php
/**
 * Reusable media table container element
 * 
 * @var \App\View\AppView $this
 * @var iterable $items Items to display in table
 * @var array $columns Table column configuration
 * @var array $emptyState Empty state configuration
 * @var string $tableClass CSS classes for table
 */

$items = $items ?? [];
$columns = $columns ?? [];
$emptyState = $emptyState ?? [];
$tableClass = $tableClass ?? 'table table-striped table-hover';
?>

<div id="ajax-target">
    <?php if (empty($items)): ?>
        <?= $this->element('empty_state', $emptyState) ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="<?= $tableClass ?>">
                <thead>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                        <th<?= isset($column['class']) ? ' class="' . $column['class'] . '"' : '' ?>>
                            <?php if (isset($column['sortable']) && $column['sortable']): ?>
                                <?= $this->Paginator->sort($column['field'], $column['title']) ?>
                            <?php else: ?>
                                <?= $column['title'] ?>
                            <?php endif; ?>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                        <td<?= isset($column['cellClass']) ? ' class="' . $column['cellClass'] . '"' : '' ?>>
                            <?php
                            if (isset($column['element'])) {
                                // Render element for this column
                                echo $this->element($column['element'], array_merge(
                                    ['item' => $item],
                                    $column['elementData'] ?? []
                                ));
                            } elseif (isset($column['callback']) && is_callable($column['callback'])) {
                                // Use callback function
                                echo $column['callback']($item, $this);
                            } elseif (isset($column['field'])) {
                                // Simple field display
                                $value = $item;
                                foreach (explode('.', $column['field']) as $field) {
                                    $value = $value->{$field} ?? '';
                                }
                                echo h($value);
                            }
                            ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?= $this->element('pagination') ?>
    <?php endif; ?>
</div>