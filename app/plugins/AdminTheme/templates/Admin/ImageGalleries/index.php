<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ImageGallery> $imageGalleries
 * @var string $viewType
 */

// Load gallery search JavaScript
$this->Html->script('AdminTheme.gallery-search', ['block' => 'scriptBottom']);
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
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?= __('Preview') ?></th>
                        <th><?= $this->Paginator->sort('name') ?></th>
                        <th><?= $this->Paginator->sort('slug') ?></th>
                        <th><?= __('Status') ?></th>
                        <th><?= __('Images') ?></th>
                        <th><?= $this->Paginator->sort('created') ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($imageGalleries as $gallery): ?>
                    <tr>
                        <td>
                            <?php if ($gallery->hasPreviewImage()): ?>
                                <img src="<?= h($gallery->getPreviewImageUrl()) ?>"
                                     alt="<?= h($gallery->name) ?>"
                                     class="img-thumbnail gallery-preview-thumb"
                                     data-bs-toggle="popover"
                                     data-bs-trigger="hover"
                                     data-bs-content="<img src='<?= h($gallery->getPreviewImageUrl()) ?>' style='max-width: 300px; max-height: 200px;' alt='<?= h($gallery->name) ?>'>"
                                     data-bs-html="true"
                                     data-bs-placement="right">
                            <?php elseif (!empty($gallery->images)): ?>
                                <?= $this->element('image/icon', [
                                    'model' => $gallery->images[0],
                                    'icon' => $gallery->images[0]->tinyImageUrl,
                                    'preview' => $gallery->images[0]->mediumImageUrl,
                                    'class' => 'img-thumbnail gallery-preview-thumb'
                                ]) ?>
                            <?php else: ?>
                                <div class="text-center text-muted d-flex align-items-center justify-content-center img-thumbnail gallery-preview-thumb"
                                     style="border: 1px solid #ddd; border-radius: 4px;">
                                    <i class="fas fa-images"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= h($gallery->name) ?></strong>
                            <?php if ($gallery->description): ?>
                                <br><small class="text-muted"><?= $this->Text->truncate(h($gallery->description), 50) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code><?= h($gallery->slug) ?></code></td>
                        <td>
                            <?php if ($gallery->is_published): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-eye"></i> <?= __('Published') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-eye-slash"></i> <?= __('Draft') ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?= $gallery->getImageCount() ?> <?= __('images') ?>
                            </span>
                        </td>
                        <td><?= $gallery->created->format('M j, Y') ?></td>
                        <td>
                            <?= $this->element('evd_dropdown', [
                                'model' => $gallery,
                                'display' => 'name',
                                'controller' => 'ImageGalleries'
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?= $this->element('pagination') ?>
    <?php endif; ?>
</div>

<script>
// Initialize gallery search with popover reinitialization callback
document.addEventListener('DOMContentLoaded', function() {
    if (window.GallerySearch) {
        window.GallerySearch.init({
            onSearchComplete: function() {
                // Re-initialize popovers after AJAX search
                const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                popoverTriggerList.map(function (popoverTriggerEl) {
                    // Dispose existing popover to avoid duplicates
                    const existingPopover = bootstrap.Popover.getInstance(popoverTriggerEl);
                    if (existingPopover) {
                        existingPopover.dispose();
                    }
                    return new bootstrap.Popover(popoverTriggerEl);
                });
            }
        });
    }
    
    // Initialize popovers on page load
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
</script>