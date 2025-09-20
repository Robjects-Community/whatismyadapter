<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PageView $pageView
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Page View'), ['action' => 'edit', $pageView->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Page View'), ['action' => 'delete', $pageView->id], ['confirm' => __('Are you sure you want to delete # {0}?', $pageView->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Page Views'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Page View'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="pageViews view content">
            <h3><?= h($pageView->ip_address) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($pageView->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Article') ?></th>
                    <td><?= $pageView->hasValue('article') ? $this->Html->link($pageView->article->title, ['controller' => 'Articles', 'action' => 'view', $pageView->article->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Product Id') ?></th>
                    <td><?= h($pageView->product_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ip Address') ?></th>
                    <td><?= h($pageView->ip_address) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($pageView->created) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('User Agent') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($pageView->user_agent)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Referer') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($pageView->referer)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>