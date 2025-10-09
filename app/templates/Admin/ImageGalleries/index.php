<?php
$this->assign('title', __('ImageGalleries'));
?>
<div class="imageGalleries index content">
    <h3><?= __('ImageGalleries') ?></h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('Id') ?></th>
                    <th><?= __('Title/Name') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($imageGalleries)): ?>
                    <?php foreach ($imageGalleries as $imageGallery): ?>
                    <tr>
                        <td><?= h($imageGallery->id ?? '') ?></td>
                        <td><?= h($imageGallery->title ?? $imageGallery->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $imageGallery->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
