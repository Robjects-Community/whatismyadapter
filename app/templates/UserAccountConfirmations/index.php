<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\UserAccountConfirmation> $userAccountConfirmations
 */
?>
<div class="userAccountConfirmations index content">
    <?= $this->Html->link(__('New User Account Confirmation'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('User Account Confirmations') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('confirmation_code') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userAccountConfirmations as $userAccountConfirmation): ?>
                <tr>
                    <td><?= h($userAccountConfirmation->id) ?></td>
                    <td><?= $userAccountConfirmation->hasValue('user') ? $this->Html->link($userAccountConfirmation->user->username, ['controller' => 'Users', 'action' => 'view', $userAccountConfirmation->user->id]) : '' ?></td>
                    <td><?= h($userAccountConfirmation->confirmation_code) ?></td>
                    <td><?= h($userAccountConfirmation->created) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $userAccountConfirmation->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $userAccountConfirmation->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $userAccountConfirmation->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $userAccountConfirmation->id),
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