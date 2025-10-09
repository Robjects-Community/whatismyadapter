<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ImageGallery> $imageGalleries
 * @var string $viewType
 */

// Load gallery search and grid interaction JavaScript
$this->Html->script('gallery-search', ['block' => true]);
$this->Html->script('gallery-grid-interactions', ['block' => true]);

// Load consolidated images-grid CSS (includes gallery styles)
$this->Html->css('images-grid', ['block' => true]);
?>

<!-- Header with view switcher and search -->
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <!-- View Switcher -->
            <?= $this->element('view_switcher', [
                'currentView' => $viewType,
                'queryParams' => $this->request->getQueryParams()
            ]) ?>
            
            <!-- Search Form -->
            <?= $this->element('search_form', [
                'searchValue' => $this->request->getQuery('search'),
                'options' => [
                    'id' => 'gallery-search-form',
                    'inputId' => 'gallery-search',
                    'placeholder' => __('Search galleries...')
                ]
            ]) ?>
            
            <!-- Status Filter -->
            <?= $this->element('status_filter') ?>
        </div>
        
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="fas fa-plus"></i> ' . __('New Gallery'),
                ['action' => 'add'],
                ['class' => 'btn btn-success', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>

<!-- Content Target for AJAX updates -->
<div id="ajax-target">
    <?php if (empty($imageGalleries)): ?>
        <?= $this->element('empty_state', [
            'icon' => 'fas fa-images',
            'title' => __('No Image Galleries Found'),
            'message' => __('Create your first gallery to get started.'),
            'actionText' => __('Create Gallery'),
            'actionUrl' => ['action' => 'add']
        ]) ?>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php foreach ($imageGalleries as $gallery): ?>
            <div class="col">
                <?= $this->element('ImageGalleries/gallery_card', ['gallery' => $gallery]) ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?= $this->element('pagination') ?>
    <?php endif; ?>
</div>

<script>
// Initialize gallery search and grid interactions
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search with grid interaction refresh callback
    if (window.GallerySearch) {
        window.GallerySearch.init({
            onSearchComplete: function() {
                // Refresh grid interactions after AJAX search
                if (window.GalleryGridInteractions) {
                    window.GalleryGridInteractions.refresh();
                }
            }
        });
    }
    
    // Initialize grid interactions
    if (window.GalleryGridInteractions) {
        window.GalleryGridInteractions.init();
    }
});
</script>

