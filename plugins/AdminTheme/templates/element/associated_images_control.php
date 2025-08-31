<?php if (!empty($images)): ?>
    <?= $this->element('image_carousel', [
        'images' => $images,
        'carouselId' => $carouselId ?? 'imageCarouselID'
    ]) ?>
<?php endif; ?>
<?= $this->Form->label('image_uploads[]', 'Images') ?>
<?= $this->Form->file('image_uploads[]', ['multiple' => true, 'class' => 'form-control-file']) ?>