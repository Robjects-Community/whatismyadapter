<?php
/**
 * Status Filter Element
 * 
 * @var \App\View\AppView $this
 * @var array $options Configuration options
 */

$defaults = [
    'action' => 'index',
    'buttonText' => __('Status'),
    'class' => 'dropdown',
    'buttonClass' => 'btn btn-outline-secondary dropdown-toggle',
    'menuClass' => 'dropdown-menu',
    'itemClass' => 'dropdown-item',
    'filters' => [
        'all' => ['label' => __('All'), 'params' => []],
        'published' => ['label' => __('Published'), 'params' => ['status' => '1']],
        'unpublished' => ['label' => __('Un-Published'), 'params' => ['status' => '0']],
    ],
    'preserveParams' => true,
];
$config = array_merge($defaults, $options ?? []);

// Get current query params to preserve
$currentParams = $config['preserveParams'] ? $this->request->getQueryParams() : [];
// Remove status from current params so it doesn't get duplicated
unset($currentParams['status']);
?>

<div class="<?= h($config['class']) ?>">
    <button class="<?= h($config['buttonClass']) ?>" type="button" data-bs-toggle="dropdown">
        <?= h($config['buttonText']) ?>
    </button>
    <ul class="<?= h($config['menuClass']) ?>">
        <?php foreach ($config['filters'] as $key => $filter): ?>
            <li>
                <?= $this->Html->link(
                    h($filter['label']),
                    ['action' => $config['action'], '?' => $filter['params'] + $currentParams],
                    ['class' => $config['itemClass']]
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>