<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Tag> $tags
 */
?>
<div class="tags index content">
    <?= $this->Html->link(__('New Tag'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Tags') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('title') ?></th>
                    <th><?= $this->Paginator->sort('slug') ?></th>
                    <th><?= $this->Paginator->sort('image') ?></th>
                    <th><?= $this->Paginator->sort('dir') ?></th>
                    <th><?= $this->Paginator->sort('alt_text') ?></th>
                    <th><?= $this->Paginator->sort('keywords') ?></th>
                    <th><?= $this->Paginator->sort('size') ?></th>
                    <th><?= $this->Paginator->sort('mime') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('meta_title') ?></th>
                    <th><?= $this->Paginator->sort('parent_id') ?></th>
                    <th><?= $this->Paginator->sort('main_menu') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tags as $tag): ?>
                <tr>
                    <td><?= h($tag->id) ?></td>
                    <td><?= h($tag->title) ?></td>
                    <td><?= h($tag->slug) ?></td>
                    <td><?= h($tag->image) ?></td>
                    <td><?= h($tag->dir) ?></td>
                    <td><?= h($tag->alt_text) ?></td>
                    <td><?= h($tag->keywords) ?></td>
                    <td><?= $tag->size === null ? '' : $this->Number->format($tag->size) ?></td>
                    <td><?= h($tag->mime) ?></td>
                    <td><?= h($tag->name) ?></td>
                    <td><?= h($tag->meta_title) ?></td>
                    <td><?= $tag->hasValue('parent_tag') ? $this->Html->link($tag->parent_tag->title, ['controller' => 'Tags', 'action' => 'view', $tag->parent_tag->id]) : '' ?></td>
                    <td><?= h($tag->main_menu) ?></td>
                    <td><?= h($tag->modified) ?></td>
                    <td><?= h($tag->created) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $tag->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $tag->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $tag->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $tag->id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>