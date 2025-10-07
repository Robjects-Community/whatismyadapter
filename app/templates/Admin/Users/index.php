<?php
$this->assign('title', __('Users'));
?>
<div class="users index content">
    <h3><?= __('Users') ?></h3>
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
                <?php if (isset($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= h($user->id ?? '') ?></td>
                        <td><?= h($user->title ?? $user->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $user->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
