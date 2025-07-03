<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGallery $imageGallery
 * @var string[]|\Cake\Collection\CollectionInterface $images
 */
?>
<?php
// Only show actions if we have an entity (edit mode)
if (!$imageGallery->isNew()) {
    echo $this->element('actions_card', [
        'modelName' => 'Image Gallery',
        'controllerName' => 'Image Galleries',
        'entity' => $imageGallery,
        'entityDisplayName' => $imageGallery->name
    ]);
}
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Edit Image Gallery') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($imageGallery,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
                    <fieldset>
                        <div class="mb-3">
                            <?php echo $this->Form->control('name', ['class' => 'form-control' . ($this->Form->isFieldError('name') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('name')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('name') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <?php echo $this->Form->control('slug', ['class' => 'form-control' . ($this->Form->isFieldError('slug') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('slug')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('slug') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <?php echo $this->Form->control('description', ['type' => 'textarea', 'rows' => 3, 'class' => 'form-control' . ($this->Form->isFieldError('description') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('description')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('description') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('is_published', [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('is_published') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="is-published">
                                    <?= __('Is Published') ?>
                                </label>
                            </div>
                            <?php if ($this->Form->isFieldError('is_published')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('is_published') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= __('Add More Images') ?></label>
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-plus-circle me-2"></i><?= __('Add Images to Gallery') ?></h6>
                                <p class="mb-2"><?= __('Upload additional images to add to this gallery:') ?></p>
                                <ul class="mb-0">
                                    <li><strong><?= __('Individual Images') ?>:</strong> <?= __('JPG, PNG, GIF files') ?></li>
                                    <li><strong><?= __('Archive Files') ?>:</strong> <?= __('ZIP, TAR, TAR.GZ files containing multiple images') ?></li>
                                </ul>
                                <hr class="my-2">
                                <p class="mb-0 small text-muted">
                                    <i class="fas fa-info-circle me-1"></i><?= __('New images will be added to the existing gallery. Existing images will not be affected.') ?>
                                </p>
                            </div>
                            <?php echo $this->Form->control('image_files[]', [
                                'type' => 'file',
                                'multiple' => true,
                                'accept' => 'image/*,.zip,.tar,.tar.gz,.tgz',
                                'class' => 'form-control' . ($this->Form->isFieldError('image_files') ? ' is-invalid' : ''),
                                'label' => false,
                                'required' => false
                            ]); ?>
                            <?php if ($this->Form->isFieldError('image_files')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('image_files') ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">
                                <?= __('Optional: Select image files or archive files to add to this gallery. Leave empty to update gallery details only.') ?>
                            </div>
                        </div>
                    </fieldset>
                    
                    <?= $this->element('form/seo', ['hideWordCount' => true]) ?>
                    
                    <div class="form-group">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>