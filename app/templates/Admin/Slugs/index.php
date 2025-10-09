<?php
$this->assign('title', __('Slugs'));
?>
<div class="slugs index content">
    <h3><?= __('Slugs') ?></h3>
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
                <?php if (isset($slugs)): ?>
                    <?php foreach ($slugs as $slug): ?>
                    <tr>
                        <td><?= h($slug->id ?? '') ?></td>
                        <td><?= h($slug->title ?? $slug->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $slug->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
