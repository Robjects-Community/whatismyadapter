<?php
/**
 * Reusable media grid container element
 * 
 * @var \App\View\AppView $this
 * @var iterable $items Items to display in grid
 * @var array $emptyState Empty state configuration
 * @var array $gridOptions Grid layout options
 * @var string $itemElement Element to use for each item
 * @var array $itemData Additional data to pass to item element
 */

$items = $items ?? [];
$emptyState = $emptyState ?? [];
$gridOptions = $gridOptions ?? [];
$itemElement = $itemElement ?? 'media/grid_item';
$itemData = $itemData ?? [];

// Default grid classes
$gridClass = $gridOptions['gridClass'] ?? 'row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4';
$colClass = $gridOptions['colClass'] ?? 'col';
?>

<div id="ajax-target">
    <?php if (empty($items)): ?>
        <?= $this->element('empty_state', $emptyState) ?>
    <?php else: ?>
        <div class="<?= $gridClass ?>">
            <?php foreach ($items as $item): ?>
            <div class="<?= $colClass ?>">
                <?= $this->element($itemElement, array_merge(['item' => $item], $itemData)) ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?= $this->element('pagination') ?>
    <?php endif; ?>
</div>