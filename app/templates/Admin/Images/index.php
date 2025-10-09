<?php
$this->assign('title', __('Images'));
?>
<div class="images index content">
    <h3><?= __('Images') ?></h3>
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
                <?php if (isset($images)): ?>
                    <?php foreach ($images as $image): ?>
                    <tr>
                        <td><?= h($image->id ?? '') ?></td>
                        <td><?= h($image->title ?? $image->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $image->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
