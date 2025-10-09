<?php
$this->assign('title', __('Images'));
?>
<div class="images index content">
    <h3><?= __('Images Grid') ?></h3>
    <div class="row">
        <?php if (isset($images)): ?>
            <?php foreach ($images as $image): ?>
            <div class="col-md-3">
                <div class="card">
                    <?php if (isset($image->path)): ?>
                        <img src="<?= h($image->path) ?>" class="card-img-top" alt="<?= h($image->title ?? '') ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= h($image->title ?? 'Untitled') ?></h5>
                        <?= $this->Html->link(__('View'), ['action' => 'view', $image->id ?? ''], ['class' => 'btn btn-sm btn-primary']) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
