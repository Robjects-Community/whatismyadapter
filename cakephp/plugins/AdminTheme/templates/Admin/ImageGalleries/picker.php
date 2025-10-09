<?php
/**
 * Gallery Picker - For selecting galleries to insert into content
 * Modern layout with separated search form and results for smooth AJAX updates
 * 
 * @var \App\View\AppView $this
 * @var iterable $results Gallery results
 * @var string|null $search Current search term
 * @var string $viewType Current view type (grid/list)
 */

$results = $results ?? [];
$search = $search ?? '';
$viewType = $viewType ?? 'grid';
?>

<div class="willow-gallery-picker">
    <!-- Static Search Form (Never gets replaced via AJAX) -->
    <div class="willow-search-form p-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           id="gallerySearch" 
                           placeholder="<?= __('Search galleries by name, description...') ?>"
                           value="<?= h($search) ?>"
                           autocomplete="off">
                    <?php if ($search): ?>
                        <button class="btn btn-outline-secondary" type="button" id="clearGallerySearch">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group" aria-label="View toggle">
                    <input type="radio" class="btn-check" name="galleryViewType" id="gridView" value="grid" <?= $viewType === 'grid' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-primary" for="gridView" title="<?= __('Grid View') ?>">
                        <i class="fas fa-th"></i>
                    </label>
                    <input type="radio" class="btn-check" name="galleryViewType" id="listView" value="list" <?= $viewType === 'list' ? 'checked' : '' ?>>
                    <label class="btn btn-outline-primary" for="listView" title="<?= __('List View') ?>">
                        <i class="fas fa-list"></i>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Search Stats -->
        <div class="row mt-2">
            <div class="col">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    <?= __('Select a gallery to insert into your content') ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Dynamic Results Container (Gets replaced via AJAX) -->
    <div id="gallery-results" class="willow-results-container">
        <?php if (!empty($results)): ?>
            <div class="row g-3 p-3">
                <?php foreach ($results as $gallery): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card willow-picker-card h-100 shadow-sm">
                            <!-- Gallery Preview -->
                            <div class="position-relative overflow-hidden" style="height: 180px;">
                                <?php if (!empty($gallery->images)): ?>
                                    <?php if ($gallery->hasPreviewImage()): ?>
                                        <img src="<?= h($gallery->getPreviewImageUrl()) ?>" 
                                             alt="<?= h($gallery->name) ?>"
                                             class="img-fluid w-100 h-100"
                                             style="object-fit: cover;">
                                    <?php else: ?>
                                        <img src="<?= h($gallery->images[0]->getImageUrl('medium')) ?>" 
                                             alt="<?= h($gallery->name) ?>"
                                             class="img-fluid w-100 h-100"
                                             style="object-fit: cover;">
                                    <?php endif; ?>
                                    
                                    <!-- Image count overlay -->
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-dark bg-opacity-75">
                                            <i class="fas fa-images me-1"></i><?= count($gallery->images) ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Status overlay -->
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge <?= $gallery->is_published ? 'bg-success' : 'bg-warning text-dark' ?>">
                                            <?= $gallery->is_published ? __('Published') : __('Draft') ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                        <div class="text-center text-muted">
                                            <i class="fas fa-images fa-3x mb-2"></i>
                                            <div class="small"><?= __('Empty Gallery') ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Gallery Info -->
                            <div class="card-body">
                                <h6 class="card-title mb-2"><?= h($gallery->name) ?></h6>
                                
                                <?php if ($gallery->description): ?>
                                    <p class="card-text text-muted small mb-2">
                                        <?= h($this->Text->truncate($gallery->description, 80)) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center text-muted small">
                                    <span>
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= $gallery->created->format('M j, Y') ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-tag me-1"></i>
                                        <?= h($gallery->slug) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Selection Button -->
                            <div class="card-footer bg-transparent p-3 pt-0">
                                <button type="button" 
                                        class="btn btn-primary w-100 select-gallery"
                                        data-gallery-id="<?= h($gallery->id) ?>"
                                        data-gallery-name="<?= h($gallery->name) ?>"
                                        data-gallery-slug="<?= h($gallery->slug) ?>"
                                        data-theme="default"
                                        data-image-count="<?= count($gallery->images) ?>">
                                    <i class="fas fa-plus me-2"></i>
                                    <?= __('Insert Gallery') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Beautiful Empty State -->
            <div class="willow-empty-state p-5">
                <div class="text-center">
                    <?php if ($search): ?>
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted mb-2"><?= __('No galleries found') ?></h5>
                        <p class="text-muted mb-3">
                            <?= __('No galleries match "{0}"', h($search)) ?>
                        </p>
                        <button type="button" class="btn btn-outline-primary" id="clearSearchBtn">
                            <i class="fas fa-times me-2"></i>
                            <?= __('Clear Search') ?>
                        </button>
                    <?php else: ?>
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted mb-2"><?= __('No galleries available') ?></h5>
                        <p class="text-muted">
                            <?= __('Create image galleries first to insert them into your content.') ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($this->Paginator->total() > $this->Paginator->param('perPage')): ?>
            <div class="d-flex justify-content-center p-3 border-top">
                <?= $this->element('pagination') ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Enhanced gallery picker interactions
document.addEventListener('DOMContentLoaded', function() {
    // Clear search functionality
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            const searchInput = document.getElementById('gallerySearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                searchInput.focus();
            }
        });
    }
    
    // Clear search button in search form
    const clearGallerySearch = document.getElementById('clearGallerySearch');
    if (clearGallerySearch) {
        clearGallerySearch.addEventListener('click', function() {
            const searchInput = document.getElementById('gallerySearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                searchInput.focus();
            }
        });
    }
});