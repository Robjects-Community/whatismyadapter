<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGallery $imageGallery
 */

// Load CSS and JavaScript assets
$this->Html->css('images-grid', ['block' => true]); // Now includes gallery styles
$this->Html->script('https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', ['block' => true]);
$this->Html->script('gallery-manage-images', ['block' => true]);
?>
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
            <div class="mb-2 mb-md-0">
                <h3><?= h($imageGallery->name) ?> - <?= __('Manage Images') ?></h3>
            </div>
            <div class="btn-group btn-group-sm" role="group">
                <?= $this->Html->link(
                    '<i class="fas fa-edit me-1"></i>' . __('Edit Gallery'), 
                    ['action' => 'edit', $imageGallery->id], 
                    ['class' => 'btn btn-outline-primary', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-list me-1"></i>' . __('List Galleries'), 
                    ['action' => 'index'], 
                    ['class' => 'btn btn-outline-secondary', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-plus me-1"></i>' . __('New Gallery'), 
                    ['action' => 'add'], 
                    ['class' => 'btn btn-outline-success', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-trash me-1"></i>' . __('Delete'), 
                    ['action' => 'delete', $imageGallery->id], 
                    [
                        'confirm' => __('Are you sure you want to delete "{0}"?', $imageGallery->name),
                        'class' => 'btn btn-outline-danger',
                        'escape' => false
                    ]
                ) ?>
            </div>
        </div>
        <div class="imageGalleries manage images">
            <div class="table-responsive">
                <p><?= __('Drag and drop images to reorder them in the gallery.') ?></p>
                
                <?php if (!empty($imageGallery->image_galleries_images)): ?>
                    <div id="sortable-images" class="gallery-manage-grid">
                        <?php foreach ($imageGallery->image_galleries_images as $galleryImage): ?>
                            <div class="gallery-image-item" data-image-id="<?= h($galleryImage->image_id) ?>">
                                <?php if ($galleryImage->image && $galleryImage->image->image): ?>
                                    <?= $this->element('image/icon', [
                                        'model' => $galleryImage->image, 
                                        'icon' => $galleryImage->image->mediumImageUrl ?? null, 
                                        'preview' => $galleryImage->image->largeImageUrl ?? null
                                    ]) ?>
                                    <div class="gallery-image-actions">
                                        <button type="button" class="btn btn-danger btn-sm remove-image" 
                                                data-image-id="<?= h($galleryImage->image_id) ?>">
                                            <?= __('Remove') ?>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted text-center p-3">
                                        <i class="fas fa-image"></i><br>
                                        <?= __('Image not available') ?>
                                        <div class="gallery-image-actions">
                                            <button type="button" class="btn btn-danger btn-sm remove-image" 
                                                    data-image-id="<?= h($galleryImage->image_id) ?>">
                                                <?= __('Remove') ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p><?= __('No images in this gallery yet.') ?></p>
                <?php endif; ?>
                
                <div class="actions">
                    <?= $this->Html->link(__('Add Images'), ['controller' => 'Images', 'action' => 'picker', '?' => ['gallery_id' => $imageGallery->id]], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Configure gallery management via data attributes
$this->Html->scriptBlock(
    'window.GalleryManageConfig = ' . json_encode([
        'galleryId' => $imageGallery->id,
        'csrfToken' => $this->request->getAttribute('csrfToken'),
        'updateOrderUrl' => $this->Url->build(['action' => 'updateImageOrder']),
        'removeImageUrl' => $this->Url->build(['action' => 'removeImage', $imageGallery->id]) . '/:imageId',
        'confirmMessage' => __('Are you sure you want to remove this image from the gallery?')
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';',
    ['block' => true]
);
?>