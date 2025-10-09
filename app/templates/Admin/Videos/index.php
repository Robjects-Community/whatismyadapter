<?php
$this->assign('title', __('Videos'));
?>
<div class="videos index content">
    <h3><?= __('Videos') ?></h3>
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
                <?php if (isset($videos)): ?>
                    <?php foreach ($videos as $video): ?>
                    <tr>
                        <td><?= h($video->id ?? '') ?></td>
                        <td><?= h($video->title ?? $video->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $video->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
