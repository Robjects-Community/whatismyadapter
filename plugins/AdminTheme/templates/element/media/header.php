<?php
/**
 * Reusable media header element
 * 
 * @var \App\View\AppView $this
 * @var string $title Title for the header
 * @var array $actions Array of action buttons
 * @var array $viewSwitcher View switcher configuration
 * @var array $searchForm Search form configuration  
 * @var array $filters Additional filter elements
 */

$title = $title ?? __('Media');
$actions = $actions ?? [];
$viewSwitcher = $viewSwitcher ?? null;
$searchForm = $searchForm ?? null;
$filters = $filters ?? [];
?>

<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <?php if ($viewSwitcher): ?>
                <!-- View Switcher -->
                <?php if (isset($viewSwitcher['helper']) && $viewSwitcher['helper'] === 'Gallery'): ?>
                    <?= $this->element('view_switcher', [
                        'currentView' => $viewSwitcher['currentView'],
                        'queryParams' => $viewSwitcher['queryParams'] ?? []
                    ]) ?>
                <?php else: ?>
                    <div class="btn-group me-3" role="group">
                        <?= $this->Html->link(
                            '<i class="fas fa-list"></i>',
                            ['action' => 'index', '?' => ['view' => 'list'] + ($viewSwitcher['queryParams'] ?? [])],
                            [
                                'class' => 'btn ' . (($viewSwitcher['currentView'] ?? 'list') === 'list' ? 'btn-primary' : 'btn-outline-secondary'),
                                'escape' => false,
                                'title' => __('List View'),
                            ]
                        ) ?>
                        <?= $this->Html->link(
                            '<i class="fas fa-th"></i>',
                            ['action' => 'index', '?' => ['view' => 'grid'] + ($viewSwitcher['queryParams'] ?? [])],
                            [
                                'class' => 'btn ' . (($viewSwitcher['currentView'] ?? 'list') === 'grid' ? 'btn-primary' : 'btn-outline-secondary'),
                                'escape' => false,
                                'title' => __('Grid View'),
                            ]
                        ) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($searchForm): ?>
                <!-- Search Form -->
                <?php if (isset($searchForm['helper']) && $searchForm['helper'] === 'Gallery'): ?>
                    <?= $this->element('search_form', [
                        'searchValue' => $searchForm['value'] ?? null,
                        'options' => [
                            'id' => $searchForm['id'] ?? 'gallery-search-form',
                            'inputId' => $searchForm['inputId'] ?? 'gallery-search',
                            'placeholder' => $searchForm['placeholder'] ?? __('Search galleries...')
                        ]
                    ]) ?>
                <?php else: ?>
                    <form class="d-flex me-3" role="search" id="<?= $searchForm['id'] ?? 'media-search-form' ?>">
                        <div class="input-group">
                            <?= $this->Form->control('search', [
                                'type' => 'search',
                                'id' => $searchForm['inputId'] ?? 'media-search',
                                'class' => 'form-control',
                                'placeholder' => $searchForm['placeholder'] ?? __('Search...'),
                                'value' => h($searchForm['value'] ?? ''),
                                'label' => false,
                            ]) ?>
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <?php foreach ($filters as $filter): ?>
                <?= $this->element($filter['element'], $filter['data'] ?? []) ?>
            <?php endforeach; ?>
        </div>
        
        <div class="flex-shrink-0">
            <?php foreach ($actions as $action): ?>
                <?= $this->Html->link(
                    ($action['icon'] ?? '') . ' ' . $action['text'],
                    $action['url'],
                    array_merge([
                        'class' => 'btn ' . ($action['class'] ?? 'btn-primary'),
                        'escape' => false
                    ], $action['options'] ?? [])
                ) ?>
            <?php endforeach; ?>
        </div>
    </div>
</header>