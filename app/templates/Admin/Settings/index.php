<?php
$this->assign('title', __('Settings'));
?>
<div class="settings index content">
    <h3><?= __('Settings') ?></h3>
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
                <?php if (isset($settings)): ?>
                    <?php foreach ($settings as $setting): ?>
                    <tr>
                        <td><?= h($setting->id ?? '') ?></td>
                        <td><?= h($setting->title ?? $setting->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $setting->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
