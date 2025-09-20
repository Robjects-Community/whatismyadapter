<?php
/**
 * View Switcher Element
 * 
 * @var \App\View\AppView $this
 * @var string $currentView Current view type (list|grid)
 * @var array $queryParams Current query parameters to preserve
 * @var array $options Additional options
 */

$defaults = [
    'action' => 'index',
    'class' => 'btn-group me-3',
    'showLabels' => false,
];
$config = array_merge($defaults, $options ?? []);
$queryParams = $queryParams ?? [];
?>

<div class="<?= h($config['class']) ?>" role="group">
    <?= $this->Html->link(
        '<i class="fas fa-list"></i>' . ($config['showLabels'] ? ' ' . __('List') : ''),
        ['action' => $config['action'], '?' => ['view' => 'list'] + $queryParams],
        [
            'class' => 'btn ' . ($currentView === 'list' ? 'btn-primary' : 'btn-outline-secondary'),
            'escape' => false,
            'title' => __('List View')
        ]
    ) ?>
    <?= $this->Html->link(
        '<i class="fas fa-th"></i>' . ($config['showLabels'] ? ' ' . __('Grid') : ''),
        ['action' => $config['action'], '?' => ['view' => 'grid'] + $queryParams],
        [
            'class' => 'btn ' . ($currentView === 'grid' ? 'btn-primary' : 'btn-outline-secondary'),
            'escape' => false,
            'title' => __('Grid View')
        ]
    ) ?>
</div>