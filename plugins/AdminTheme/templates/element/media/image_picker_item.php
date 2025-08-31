<?php

use App\Utility\FileUtility;

/**
 * Image picker item element
 * 
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Image $item Image entity
 * @var array $pickerOptions Picker configuration
 * @var string $viewType View type (grid|list)
 */

$item = $item ?? null;
$pickerOptions = $pickerOptions ?? [];
$viewType = $viewType ?? 'grid';

if (!$item) return;

$imageUrl = $item->getImageUrl('thumbnail');
$imageName = h($item->name);
$imageAlt = h($item->alt_text ?: $item->name);
?>

<?php if ($viewType === 'list'): ?>
    <div class="list-group-item list-group-item-action insert-image" 
         data-id="<?= $item->id ?>"
         data-src="<?= h($item->image) ?>"
         data-name="<?= $imageName ?>"
         data-alt="<?= $imageAlt ?>"
         style="cursor: pointer;">
        <div class="d-flex align-items-center">
            <img src="<?= $imageUrl ?>" 
                 alt="<?= $imageAlt ?>"
                 class="me-3"
                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
            <div class="flex-grow-1">
                <h6 class="mb-1"><?= $imageName ?></h6>
                <?php if ($item->alt_text): ?>
                    <small class="text-muted"><?= $imageAlt ?></small>
                <?php endif; ?>
            </div>
            <div class="ms-2">
                <small class="text-muted"><?= FileUtility::formatFileSize($item->size ?: 0) ?></small>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card h-100 image-picker-card">
        <div class="card-body p-2 text-center">
            <img src="<?= $imageUrl ?>" 
                 alt="<?= $imageAlt ?>"
                 class="img-fluid mb-2 insert-image"
                 data-id="<?= $item->id ?>"
                 data-src="<?= h($item->image) ?>"
                 data-name="<?= $imageName ?>"
                 data-alt="<?= $imageAlt ?>"
                 style="max-height: 120px; cursor: pointer; border-radius: 4px;">
            
            <h6 class="card-title small mb-1"><?= $this->Text->truncate($imageName, 25) ?></h6>
            
            <!-- Size selector -->
            <select class="form-select form-select-sm" id="<?= $item->id ?>_size">
                <option value="thumbnail"><?= __('Thumbnail') ?></option>
                <option value="medium"><?= __('Medium') ?></option>
                <option value="large" selected><?= __('Large') ?></option>
                <option value="original"><?= __('Original') ?></option>
            </select>
        </div>
    </div>
<?php endif; ?>