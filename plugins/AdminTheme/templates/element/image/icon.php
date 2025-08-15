
<?php
// Handle null or empty icon values by falling back to original image URL
$imageUrl = $icon ?: ($model->image ? $model->imageUrl : null);

if ($imageUrl) : ?>
    <?= $this->Html->image($imageUrl, [
        'pathPrefix' => '',
        'alt' => $model->alt_text ?: h($model->name),
        'class' => 'img-thumbnail',
    ]) ?>
<?php else : ?>
    <div class="text-muted text-center d-flex align-items-center justify-content-center img-thumbnail" style="width: 100px; height: 75px;">
        <i class="fas fa-image fa-2x"></i>
    </div>
<?php endif; ?>