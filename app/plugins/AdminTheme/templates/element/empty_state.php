<?php
/**
 * Empty State Element
 * 
 * @var \App\View\AppView $this
 * @var array $options Configuration options
 */

$defaults = [
    'icon' => 'fas fa-inbox',
    'iconSize' => 'fa-3x',
    'title' => __('No Items Found'),
    'message' => __('There are no items to display.'),
    'actionText' => null,
    'actionUrl' => null,
    'actionClass' => 'btn btn-primary',
    'class' => 'text-center py-5',
    'iconClass' => 'text-muted mb-3',
    'titleClass' => 'text-muted',
    'messageClass' => 'text-muted',
    'type' => 'default', // default, search, error
];
$config = array_merge($defaults, $options ?? []);

// Adjust defaults based on type
switch ($config['type']) {
    case 'search':
        $config['icon'] = $config['icon'] ?: 'fas fa-search';
        $config['title'] = $config['title'] ?: __('No Results Found');
        $config['message'] = $config['message'] ?: __('Try adjusting your search terms or filters.');
        break;
    case 'error':
        $config['icon'] = $config['icon'] ?: 'fas fa-exclamation-triangle';
        $config['title'] = $config['title'] ?: __('Something Went Wrong');
        $config['message'] = $config['message'] ?: __('Please try again later.');
        $config['iconClass'] = 'text-danger mb-3';
        break;
}
?>

<div class="<?= h($config['class']) ?>">
    <i class="<?= h($config['icon']) ?> <?= h($config['iconSize']) ?> <?= h($config['iconClass']) ?>"></i>
    
    <?php if ($config['title']): ?>
        <h4 class="<?= h($config['titleClass']) ?>"><?= h($config['title']) ?></h4>
    <?php endif; ?>
    
    <?php if ($config['message']): ?>
        <p class="<?= h($config['messageClass']) ?>"><?= h($config['message']) ?></p>
    <?php endif; ?>
    
    <?php if ($config['actionText'] && $config['actionUrl']): ?>
        <?= $this->Html->link(
            '<i class="fas fa-plus"></i> ' . h($config['actionText']),
            $config['actionUrl'],
            ['class' => $config['actionClass'], 'escape' => false]
        ) ?>
    <?php endif; ?>
</div>