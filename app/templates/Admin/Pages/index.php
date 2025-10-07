<?php
$this->assign('title', __('Pages'));
?>
<div class="pages index content">
    <h3><?= __('Pages') ?></h3>
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
                <?php if (isset($pages)): ?>
                    <?php foreach ($pages as $page): ?>
                    <tr>
                        <td><?= h($page->id ?? '') ?></td>
                        <td><?= h($page->title ?? $page->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $page->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
