<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<?php foreach ($images as $image): ?>
    <div class="col-6 col-md-4 col-lg-3 image-item" data-image-id="<?= h($image->id) ?>">
        <div class="card h-100">
            <div class="position-relative">
                <?= $this->Html->image($image->getImageUrlBySize('thumbnail'), [
                    'class' => 'card-img-top',
                    'style' => 'height: 120px; object-fit: cover;',
                    'alt' => h($image->alt_text ?: $image->name)
                ]) ?>
                <div class="position-absolute top-0 end-0 p-1">
                    <span class="badge bg-primary">
                        <i class="fas fa-check" style="display: none;"></i>
                    </span>
                </div>
            </div>
            <div class="card-body p-2">
                <h6 class="card-title small mb-0" title="<?= h($image->name) ?>">
                    <?= $this->Text->truncate(h($image->name), 20) ?>
                </h6>
            </div>
        </div>
    </div>
<?php endforeach; ?>