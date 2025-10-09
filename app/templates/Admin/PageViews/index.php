<?php
$this->assign('title', __('PageViews'));
?>
<div class="pageViews index content">
    <h3><?= __('PageViews') ?></h3>
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
                <?php if (isset($pageViews)): ?>
                    <?php foreach ($pageViews as $pageView): ?>
                    <tr>
                        <td><?= h($pageView->id ?? '') ?></td>
                        <td><?= h($pageView->title ?? $pageView->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $pageView->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
