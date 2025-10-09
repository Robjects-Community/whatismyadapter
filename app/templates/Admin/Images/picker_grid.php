<?php
$this->assign('title', __('Image Picker'));
?>
<div class="images picker content">
    <h3><?= __('Image Picker') ?></h3>
    <div class="row">
        <?php if (isset($images)): ?>
            <?php foreach ($images as $image): ?>
            <div class="col-md-2">
                <div class="card picker-card" data-id="<?= h($image->id ?? '') ?>">
                    <?php if (isset($image->path)): ?>
                        <img src="<?= h($image->path) ?>" class="card-img-top" alt="<?= h($image->title ?? '') ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <small><?= h($image->title ?? '') ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
