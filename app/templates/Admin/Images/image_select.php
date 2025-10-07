<?php
$this->assign('title', __('Select Image'));
?>
<div class="images imageselect content">
    <h3><?= __('Select Image') ?></h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('Preview') ?></th>
                    <th><?= __('Title') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($images)): ?>
                    <?php foreach ($images as $image): ?>
                    <tr>
                        <td>
                            <?php if (isset($image->path)): ?>
                                <img src="<?= h($image->path) ?>" alt="<?= h($image->title ?? '') ?>" style="max-width:50px;">
                            <?php endif; ?>
                        </td>
                        <td><?= h($image->title ?? '') ?></td>
                        <td><?= $this->Html->link(__('Select'), '#', ['class' => 'select-image', 'data-id' => $image->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
