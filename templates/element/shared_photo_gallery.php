<?php
/**
 * Shared Photo Gallery Element
 * 
 * Works across both AdminTheme and DefaultTheme with automatic theme detection
 * 
 * @var \App\View\AppView $this
 * @var array $images Array of image entities
 * @var string $title Gallery title
 * @var string $description Gallery description
 * @var string $theme Gallery theme ('admin' or 'default') - auto-detected if not provided
 * @var bool $showActions Whether to show management actions (admin only)
 * @var string $galleryId Gallery ID for management links
 */

// Auto-detect theme if not provided
if (!isset($theme)) {
    $request = $this->getRequest();
    $theme = (str_contains($request->getPath(), '/admin/') || $this->getPlugin() === 'AdminTheme') ? 'admin' : 'default';
}

$showActions = $showActions ?? ($theme === 'admin');
$title = $title ?? __('Gallery Images');
$description = $description ?? '';
$images = $images ?? [];
$galleryId = $galleryId ?? null;
?>

<?php if (!empty($images)) : ?>
    <?php if ($theme === 'admin'): ?>
        <div class="gallery-header">
            <div class="d-flex justify-content-end">
                <span class="gallery-count"><?= count($images) ?> <?= __('images') ?></span>
            </div>
        </div>
    <?php elseif (!empty($title) && $theme === 'default'): ?>
        <div class="gallery-header">
            <h4 class="gallery-title"><?= h($title) ?></h4>
            <?php if (!empty($description)): ?>
                <p class="gallery-description text-muted"><?= h($description) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="<?= $theme === 'admin' ? 'admin-gallery' : 'default-gallery' ?>">
        <div class="photo-gallery">
            <?php foreach ($images as $image) : ?>
                <div class="gallery-item">
                    <a href="<?= h($image->massiveImageUrl ?: $image->extraLargeImageUrl ?: $image->getImageUrlBySize('massive') ?: $image->getImageUrlBySize('extraLarge')) ?>" 
                       data-title="<?= h($image->name) ?>"
                       data-caption="<?= h($image->alt_text) ?>"
                       data-pswp-width=""
                       data-pswp-height="">
                        <img src="<?= h($image->largeImageUrl ?: $image->getImageUrlBySize('large')) ?>" 
                             alt="<?= h($image->alt_text ?: $image->name) ?>"
                             loading="lazy"
                             class="gallery-image" />
                        
                        <div class="gallery-item-overlay">
                            <div class="gallery-item-title"><?= h($image->name) ?></div>
                            <?php if ($image->alt_text): ?>
                                <div class="gallery-item-caption"><?= h($image->alt_text) ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php if ($showActions && !empty($galleryId)): ?>
        <!-- Image Management Actions (Admin Theme) -->
        <div class="mt-4 pt-4 border-top">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="btn-group gap-2" role="group">
                        <?= $this->Html->link(
                            '<i class="fas fa-edit me-2"></i>' . __('Manage Images'),
                            ['controller' => 'ImageGalleries', 'action' => 'manageImages', $galleryId],
                            ['class' => 'btn btn-primary btn-lg', 'escape' => false]
                        ) ?>
                        
                        <?= $this->Html->link(
                            '<i class="fas fa-plus me-2"></i>' . __('Add More Images'),
                            ['controller' => 'ImageGalleries', 'action' => 'edit', $galleryId],
                            ['class' => 'btn btn-outline-secondary btn-lg ms-2', 'escape' => false]
                        ) ?>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-muted small text-end">
                        <i class="fas fa-info-circle me-1"></i>
                        <?= __('Click any image to view slideshow') ?><br>
                        <small class="text-muted"><?= __('Press spacebar or use controls to play automatically') ?></small>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($theme === 'default' && !empty($galleryId)): ?>
        <!-- Image Management Actions (Default Theme) -->
        <div class="mt-4 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?= $this->Html->link(
                        '<i class="fas fa-edit me-2"></i>' . __('Manage Images'),
                        ['controller' => 'ImageGalleries', 'action' => 'manageImages', $galleryId],
                        ['class' => 'btn btn-primary', 'escape' => false]
                    ) ?>
                    
                    <?= $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>' . __('Add More Images'),
                        ['controller' => 'ImageGalleries', 'action' => 'edit', $galleryId],
                        ['class' => 'btn btn-outline-secondary', 'escape' => false]
                    ) ?>
                </div>
                
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    <?= __('Click any image to view slideshow') ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="gallery-empty">
        <div class="gallery-empty-icon">
            <i class="fas fa-images"></i>
        </div>
        <div class="gallery-empty-text"><?= __('No images in this gallery') ?></div>
        <div class="gallery-empty-subtext"><?= __('Add some images to get started') ?></div>
        
        <?php if ($showActions && !empty($galleryId)): ?>
            <div class="mt-3">
                <?= $this->Html->link(
                    '<i class="fas fa-plus me-2"></i>' . __('Add Images'),
                    ['controller' => 'ImageGalleries', 'action' => 'edit', $galleryId],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
// Note: PhotoSwipe assets are now loaded by the calling cell/controller
// This keeps the element focused on just rendering the gallery HTML
?>