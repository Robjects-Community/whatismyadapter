<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Aiprompt> $aiprompts
 */
?>
<div class="aiprompts index content">
    <?= $this->Html->link(__('New Aiprompt'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Aiprompts') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('task_type') ?></th>
                    <th><?= $this->Paginator->sort('model') ?></th>
                    <th><?= $this->Paginator->sort('max_tokens') ?></th>
                    <th><?= $this->Paginator->sort('temperature') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aiprompts as $aiprompt): ?>
                <tr>
                    <td><?= h($aiprompt->id) ?></td>
                    <td><?= h($aiprompt->task_type) ?></td>
                    <td><?= h($aiprompt->model) ?></td>
                    <td><?= $this->Number->format($aiprompt->max_tokens) ?></td>
                    <td><?= $this->Number->format($aiprompt->temperature) ?></td>
                    <td><?= h($aiprompt->created) ?></td>
                    <td><?= h($aiprompt->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $aiprompt->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $aiprompt->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $aiprompt->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $aiprompt->id),
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