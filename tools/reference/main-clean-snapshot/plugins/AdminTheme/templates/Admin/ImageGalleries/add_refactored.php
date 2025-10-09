<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGallery $imageGallery
 * @var \Cake\Collection\CollectionInterface|string[] $images
 */

// Start form content capture
ob_start();
?>

<?= $this->element('form/input', [
    'field' => 'name',
    'required' => true
]) ?>

<?= $this->element('form/input', [
    'field' => 'description',
    'type' => 'textarea',
    'options' => ['rows' => 3]
]) ?>

<?= $this->element('form/input', [
    'field' => 'is_published',
    'type' => 'checkbox',
    'label' => __('Is Published')
]) ?>

<!-- Custom upload section -->
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
    <?= $this->element('form/input', [
        'field' => 'image_files',
        'type' => 'file',
        'options' => [
            'multiple' => true,
            'accept' => 'image/*,.zip,.tar,.tar.gz,.tgz'
        ],
        'help' => __('Select multiple image files or archive files. Images will be automatically processed and added to this gallery.'),
        'containerClass' => ''
    ]) ?>
</div>

<?= $this->element('form/seo', ['hideWordCount' => true]) ?>

<?php
$content = ob_get_clean();

// Render the form wrapper
echo $this->element('form/wrapper', [
    'title' => __('Add Image Gallery'),
    'entity' => $imageGallery,
    'modelName' => 'Image Gallery',
    'controllerName' => 'Image Galleries',
    'content' => $content
]);
?>