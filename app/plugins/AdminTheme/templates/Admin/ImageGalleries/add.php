<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGallery $imageGallery
 * @var \Cake\Collection\CollectionInterface|string[] $images
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
                    <h5 class="card-title"><?= __('Add Image Gallery') ?></h5>
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
                            <label class="form-label"><?= __('Upload Images') ?></label>
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i><?= __('Upload Options') ?></h6>
                                <p class="mb-2"><?= __('You can upload:') ?></p>
                                <ul class="mb-0">
                                    <li><strong><?= __('Individual Images') ?>:</strong> <?= __('JPG, PNG, GIF files') ?></li>
                                    <li><strong><?= __('Archive Files') ?>:</strong> <?= __('ZIP, TAR, TAR.GZ files containing multiple images') ?></li>
                                </ul>
                            </div>
                            <?php echo $this->Form->control('image_files[]', [
                                'type' => 'file',
                                'multiple' => true,
                                'accept' => 'image/*,.zip,.tar,.tar.gz,.tgz',
                                'class' => 'form-control' . ($this->Form->isFieldError('image_files') ? ' is-invalid' : ''),
                                'label' => false
                            ]); ?>
                            <?php if ($this->Form->isFieldError('image_files')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('image_files') ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">
                                <?= __('Select multiple image files or archive files. Images will be automatically processed and added to this gallery.') ?>
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