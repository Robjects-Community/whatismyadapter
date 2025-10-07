<?php
$this->assign('title', __('Comments'));
?>
<div class="comments index content">
    <h3><?= __('Comments') ?></h3>
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
                <?php if (isset($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><?= h($comment->id ?? '') ?></td>
                        <td><?= h($comment->title ?? $comment->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $comment->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
