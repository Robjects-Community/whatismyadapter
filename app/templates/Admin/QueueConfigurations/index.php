<?php
$this->assign('title', __('QueueConfigurations'));
?>
<div class="queueConfigurations index content">
    <h3><?= __('QueueConfigurations') ?></h3>
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
                <?php if (isset($queueConfigurations)): ?>
                    <?php foreach ($queueConfigurations as $queueConfiguration): ?>
                    <tr>
                        <td><?= h($queueConfiguration->id ?? '') ?></td>
                        <td><?= h($queueConfiguration->title ?? $queueConfiguration->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $queueConfiguration->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
