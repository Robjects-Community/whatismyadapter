<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Slug $slug
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Slug'), ['action' => 'edit', $slug->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Slug'), ['action' => 'delete', $slug->id], ['confirm' => __('Are you sure you want to delete # {0}?', $slug->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Slugs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Slug'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="slugs view content">
            <h3><?= h($slug->slug) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($slug->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Model') ?></th>
                    <td><?= h($slug->model) ?></td>
                </tr>
                <tr>
                    <th><?= __('Article') ?></th>
                    <td><?= $slug->hasValue('article') ? $this->Html->link($slug->article->title, ['controller' => 'Articles', 'action' => 'view', $slug->article->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Slug') ?></th>
                    <td><?= h($slug->slug) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($slug->created) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>