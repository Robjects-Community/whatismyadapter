<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\SystemLog> $systemLogs
 */
?>
<div class="systemLogs index content">
    <?= $this->Html->link(__('New System Log'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('System Logs') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('level') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('group_name') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($systemLogs as $systemLog): ?>
                <tr>
                    <td><?= h($systemLog->id) ?></td>
                    <td><?= h($systemLog->level) ?></td>
                    <td><?= h($systemLog->created) ?></td>
                    <td><?= h($systemLog->group_name) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $systemLog->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $systemLog->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $systemLog->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $systemLog->id),
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