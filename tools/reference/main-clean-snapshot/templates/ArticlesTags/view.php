<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ArticlesTag $articlesTag
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Articles Tag'), ['action' => 'edit', $articlesTag->article_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Articles Tag'), ['action' => 'delete', $articlesTag->article_id], ['confirm' => __('Are you sure you want to delete # {0}?', $articlesTag->article_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Articles Tags'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Articles Tag'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="articlesTags view content">
            <h3><?= h($articlesTag->Array) ?></h3>
            <table>
                <tr>
                    <th><?= __('Article') ?></th>
                    <td><?= $articlesTag->hasValue('article') ? $this->Html->link($articlesTag->article->title, ['controller' => 'Articles', 'action' => 'view', $articlesTag->article->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Tag') ?></th>
                    <td><?= $articlesTag->hasValue('tag') ? $this->Html->link($articlesTag->tag->title, ['controller' => 'Tags', 'action' => 'view', $articlesTag->tag->id]) : '' ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>