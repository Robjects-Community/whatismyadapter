<?php
/**
 * Gallery Card Element for Grid View
 * 
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGallery $gallery Gallery entity
 * @var array $options Card options
 */

$defaults = [
    'showActions' => true,
    'showPreview' => true,
    'cardClass' => 'card h-100 gallery-card',
];
$config = array_merge($defaults, $options ?? []);
?>

<div class="<?= h($config['cardClass']) ?>">
    <!-- Card Header -->
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><?= h($gallery->name) ?></h6>
        <?php if ($gallery->is_published): ?>
            <span class="badge bg-success">
                <i class="fas fa-eye"></i> <?= __('Published') ?>
            </span>
        <?php else: ?>
            <span class="badge bg-secondary">
                <i class="fas fa-eye-slash"></i> <?= __('Draft') ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Card Body -->
    <?php if ($config['showPreview']): ?>
    <div class="card-body p-0">
        <?php if (!empty($gallery->images)): ?>
            <!-- Preview Image -->
            <?php if ($gallery->hasPreviewImage()): ?>
                <div class="gallery-preview-overlay" data-gallery-id="gallery-<?= $gallery->id ?>">
                    <img src="<?= h($gallery->getPreviewImageUrl()) ?>" 
                         alt="<?= h($gallery->name) ?>"
                         class="gallery-preview-image">
                    <div class="gallery-image-count">
                        <i class="fas fa-images me-1"></i><?= $gallery->getImageCount() ?>
                    </div>
                    <div class="position-absolute top-50 start-50 translate-middle gallery-play-button">
                        <i class="fas fa-play-circle fa-3x text-white"></i>
                    </div>
                </div>
            <?php else: ?>
                <!-- Use first gallery image as preview -->
                <?= $this->element('image/icon', [
                    'model' => $gallery->images[0],
                    'icon' => $gallery->images[0]->tinyImageUrl,
                    'preview' => $gallery->images[0]->mediumImageUrl,
                    'class' => 'gallery-preview-thumb'
                ]) ?>
            <?php endif; ?>

            <!-- Hidden gallery for slideshow using GalleryCell -->
            <div class="d-none">
                <?= $this->cell('Gallery::display', [
                    $gallery->id,
                    'admin',
                    $gallery->name
                ]) ?>
            </div>
        <?php else: ?>
            <!-- No images state -->
            <div class="text-center text-muted py-5">
                <i class="fas fa-images fa-2x mb-2"></i>
                <p><?= __('No images') ?></p>
            </div>
        <?php endif; ?>

        <!-- Gallery Description -->
        <?php if ($gallery->description): ?>
            <div class="p-3">
                <p class="card-text small">
                    <?= $this->Text->truncate(h($gallery->description), 100) ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Card Footer with Actions -->
    <?php if ($config['showActions']): ?>
    <div class="card-footer">
        <div class="d-flex gap-2">
            <!-- View Button -->
            <?= $this->Html->link(
                '<i class="fas fa-eye"></i> ' . __('View'),
                ['action' => 'view', $gallery->id],
                ['class' => 'btn btn-outline-primary btn-sm flex-fill', 'escape' => false]
            ) ?>

            <!-- Edit Button -->
            <?= $this->Html->link(
                '<i class="fas fa-edit"></i> ' . __('Edit'),
                ['action' => 'edit', $gallery->id],
                ['class' => 'btn btn-outline-secondary btn-sm flex-fill', 'escape' => false]
            ) ?>

            <!-- Dropdown Actions -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                        type="button" 
                        data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <?= $this->Html->link(
                            __('Manage Images'),
                            ['action' => 'manageImages', $gallery->id],
                            ['class' => 'dropdown-item']
                        ) ?>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $gallery->id],
                            [
                                'class' => 'dropdown-item text-danger',
                                'confirm' => __('Are you sure you want to delete this gallery?')
                            ]
                        ) ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>