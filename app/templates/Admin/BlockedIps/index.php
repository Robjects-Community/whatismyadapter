<?php
/**
 * Admin BlockedIps Index Template
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\BlockedIp> $blockedIps
 */
$this->assign('title', __('BlockedIps'));
?>
<div class="blockedIps index content">
    <h3><?= __('BlockedIps') ?></h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('Id') ?></th>
                    <th><?= __('Title') ?></th>
                    <th><?= __('Created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($blockedIps)): ?>
                    <?php foreach ($blockedIps as $BlockedIp): ?>
                    <tr>
                        <td><?= h($BlockedIp->id ?? '') ?></td>
                        <td><?= h($BlockedIp->title ?? $BlockedIp->name ?? '') ?></td>
                        <td><?= h($BlockedIp->created ?? '') ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $BlockedIp->id ?? '']) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $BlockedIp->id ?? '']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
