<?php
/**
 * Gallery Picker Search Results - AJAX response for gallery picker
 * 
 * @var \App\View\AppView $this
 * @var iterable $results Gallery results
 * @var string|null $search Current search term
 * @var string $viewType Current view type
 */

$results = $results ?? [];
$search = $search ?? '';
$viewType = $viewType ?? 'grid';
?>

<!-- Gallery Grid (same as picker.php but without the search form) -->
<div class="row" id="gallery-grid">
    <?php if (!empty($results)): ?>
        <?php foreach ($results as $gallery): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card gallery-picker-card h-100">
                    <!-- Gallery Preview -->
                    <div class="gallery-preview">
                        <?php if (!empty($gallery->images)): ?>
                            <div class="preview-grid">
                                <?php foreach (array_slice($gallery->images, 0, 4) as $index => $image): ?>
                                    <div class="preview-item preview-<?= $index + 1 ?>">
                                        <img src="<?= h($image->thumbnailImageUrl ?: $image->getImageUrlBySize('thumbnail')) ?>" 
                                             alt="<?= h($image->alt_text ?: $image->name) ?>"
                                             class="img-fluid">
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($gallery->images) > 4): ?>
                                    <div class="preview-overlay">
                                        <span class="badge bg-dark">
                                            +<?= count($gallery->images) - 4 ?> <?= __('more') ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-preview">
                                <i class="fas fa-images fa-3x text-muted"></i>
                                <p class="text-muted mt-2"><?= __('Empty Gallery') ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Gallery Info -->
                    <div class="card-body">
                        <h6 class="card-title"><?= h($gallery->name) ?></h6>
                        <?php if ($gallery->description): ?>
                            <p class="card-text text-muted small">
                                <?= h(mb_substr($gallery->description, 0, 100)) ?>
                                <?= mb_strlen($gallery->description) > 100 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-images me-1"></i>
                                <?= count($gallery->images) ?> <?= __('images') ?>
                            </small>
                            <small class="text-muted">
                                <?= $gallery->created->format('M j, Y') ?>
                            </small>
                        </div>
                    </div>

                    <!-- Selection Button -->
                    <div class="card-footer bg-transparent border-top-0 pt-0">
                        <button type="button" 
                                class="btn btn-primary btn-sm w-100 select-gallery"
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
    <?php else: ?>
        <!-- Empty State -->
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-images fa-4x text-muted mb-3"></i>
                <h5 class="text-muted"><?= __('No galleries found') ?></h5>
                <?php if ($search): ?>
                    <p class="text-muted">
                        <?= __('No galleries match your search for "{0}"', h($search)) ?>
                    </p>
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('gallerySearch').value = ''; document.getElementById('gallerySearch').dispatchEvent(new Event('input'));">
                        <?= __('Clear Search') ?>
                    </button>
                <?php else: ?>
                    <p class="text-muted">
                        <?= __('Create some galleries first to insert them into your content.') ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($this->Paginator->total() > $this->Paginator->param('perPage')): ?>
    <div class="d-flex justify-content-center mt-4">
        <?= $this->element('pagination') ?>
    </div>
<?php endif; ?>