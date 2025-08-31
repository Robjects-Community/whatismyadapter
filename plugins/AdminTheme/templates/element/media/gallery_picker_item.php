<?php
/**
 * Gallery picker item element
 * 
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGallery $item Gallery entity
 * @var array $pickerOptions Picker configuration
 * @var string $viewType View type (grid|list)
 */

$item = $item ?? null;
$pickerOptions = $pickerOptions ?? [];
$viewType = $viewType ?? 'grid';

if (!$item) return;

$galleryName = h($item->name);
$imageCount = $item->getImageCount();
?>

<?php if ($viewType === 'list'): ?>
    <div class="list-group-item list-group-item-action select-gallery" 
         data-gallery-id="<?= $item->id ?>"
         data-gallery-name="<?= $galleryName ?>"
         data-gallery-slug="<?= h($item->slug) ?>"
         data-image-count="<?= $imageCount ?>"
         data-theme="default"
         style="cursor: pointer;">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <?php if ($item->hasPreviewImage()): ?>
                    <img src="<?= h($item->getPreviewImageUrl()) ?>"
                         alt="<?= $galleryName ?>"
                         class="gallery-preview-thumb"
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                <?php elseif (!empty($item->images)): ?>
                    <img src="<?= $item->images[0]->getImageUrl('thumbnail') ?>"
                         alt="<?= $galleryName ?>"
                         class="gallery-preview-thumb"
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                <?php else: ?>
                    <div class="text-center text-muted d-flex align-items-center justify-content-center"
                         style="width: 50px; height: 50px; border: 1px solid #ddd; border-radius: 4px;">
                        <i class="fas fa-images"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1"><?= $galleryName ?></h6>
                <?php if ($item->description): ?>
                    <small class="text-muted"><?= $this->Text->truncate(h($item->description), 50) ?></small>
                <?php endif; ?>
            </div>
            <div class="ms-2">
                <span class="badge bg-info me-1">
                    <?= $imageCount ?> <?= __('images') ?>
                </span>
                <?php if ($item->is_published): ?>
                    <span class="badge bg-success">
                        <i class="fas fa-eye"></i> <?= __('Published') ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary">
                        <i class="fas fa-eye-slash"></i> <?= __('Draft') ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card h-100 gallery-picker-card">
        <div class="card-body p-2">
            <div class="position-relative mb-2">
                <?php if ($item->hasPreviewImage()): ?>
                    <img src="<?= h($item->getPreviewImageUrl()) ?>" 
                         alt="<?= $galleryName ?>"
                         class="img-fluid select-gallery"
                         data-gallery-id="<?= $item->id ?>"
                         data-gallery-name="<?= $galleryName ?>"
                         data-gallery-slug="<?= h($item->slug) ?>"
                         data-image-count="<?= $imageCount ?>"
                         data-theme="default"
                         style="max-height: 120px; width: 100%; object-fit: cover; cursor: pointer; border-radius: 4px;">
                <?php elseif (!empty($item->images)): ?>
                    <img src="<?= $item->images[0]->getImageUrl('thumbnail') ?>" 
                         alt="<?= $galleryName ?>"
                         class="img-fluid select-gallery"
                         data-gallery-id="<?= $item->id ?>"
                         data-gallery-name="<?= $galleryName ?>"
                         data-gallery-slug="<?= h($item->slug) ?>"
                         data-image-count="<?= $imageCount ?>"
                         data-theme="default"
                         style="max-height: 120px; width: 100%; object-fit: cover; cursor: pointer; border-radius: 4px;">
                <?php else: ?>
                    <div class="text-center text-muted d-flex align-items-center justify-content-center select-gallery"
                         data-gallery-id="<?= $item->id ?>"
                         data-gallery-name="<?= $galleryName ?>"
                         data-gallery-slug="<?= h($item->slug) ?>"
                         data-image-count="<?= $imageCount ?>"
                         data-theme="default"
                         style="height: 120px; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-images fa-2x"></i>
                    </div>
                <?php endif; ?>
                
                <!-- Image count overlay -->
                <div class="position-absolute top-0 end-0 m-1">
                    <span class="badge bg-dark bg-opacity-75">
                        <i class="fas fa-images me-1"></i><?= $imageCount ?>
                    </span>
                </div>
            </div>
            
            <h6 class="card-title small mb-1"><?= $this->Text->truncate($galleryName, 25) ?></h6>
            
            <div class="d-flex justify-content-between align-items-center">
                <?php if ($item->is_published): ?>
                    <span class="badge bg-success">
                        <i class="fas fa-eye"></i> <?= __('Published') ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary">
                        <i class="fas fa-eye-slash"></i> <?= __('Draft') ?>
                    </span>
                <?php endif; ?>
                <small class="text-muted"><?= $item->created->format('M j') ?></small>
            </div>
        </div>
    </div>
<?php endif; ?>