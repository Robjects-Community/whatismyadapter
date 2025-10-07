<?php
/**
 * Admin Articles Index Template
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 */
$this->assign('title', __('Articles'));
?>
<div class="articles index content">
    <h3><?= __('Articles') ?></h3>
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
                <?php if (isset($articles)): ?>
                    <?php foreach ($articles as $Article): ?>
                    <tr>
                        <td><?= h($Article->id ?? '') ?></td>
                        <td><?= h($Article->title ?? $Article->name ?? '') ?></td>
                        <td><?= h($Article->created ?? '') ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $Article->id ?? '']) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $Article->id ?? '']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
