<?php
$this->assign('title', __('EmailTemplates'));
?>
<div class="emailTemplates index content">
    <h3><?= __('EmailTemplates') ?></h3>
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
                <?php if (isset($emailTemplates)): ?>
                    <?php foreach ($emailTemplates as $emailTemplate): ?>
                    <tr>
                        <td><?= h($emailTemplate->id ?? '') ?></td>
                        <td><?= h($emailTemplate->title ?? $emailTemplate->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $emailTemplate->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
