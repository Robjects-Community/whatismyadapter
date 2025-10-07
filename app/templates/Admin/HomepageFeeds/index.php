<?php
$this->assign('title', __('HomepageFeeds'));
?>
<div class="homepageFeeds index content">
    <h3><?= __('HomepageFeeds') ?></h3>
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
                <?php if (isset($homepageFeeds)): ?>
                    <?php foreach ($homepageFeeds as $homepageFeed): ?>
                    <tr>
                        <td><?= h($homepageFeed->id ?? '') ?></td>
                        <td><?= h($homepageFeed->title ?? $homepageFeed->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $homepageFeed->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
