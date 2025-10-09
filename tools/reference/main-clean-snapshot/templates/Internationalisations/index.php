<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Internationalisation> $internationalisations
 */
?>
<div class="internationalisations index content">
    <?= $this->Html->link(__('New Internationalisation'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Internationalisations') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('locale') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($internationalisations as $internationalisation): ?>
                <tr>
                    <td><?= h($internationalisation->id) ?></td>
                    <td><?= h($internationalisation->locale) ?></td>
                    <td><?= h($internationalisation->created) ?></td>
                    <td><?= h($internationalisation->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $internationalisation->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $internationalisation->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $internationalisation->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $internationalisation->id),
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