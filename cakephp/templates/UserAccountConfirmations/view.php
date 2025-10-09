<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UserAccountConfirmation $userAccountConfirmation
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit User Account Confirmation'), ['action' => 'edit', $userAccountConfirmation->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete User Account Confirmation'), ['action' => 'delete', $userAccountConfirmation->id], ['confirm' => __('Are you sure you want to delete # {0}?', $userAccountConfirmation->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List User Account Confirmations'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New User Account Confirmation'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="userAccountConfirmations view content">
            <h3><?= h($userAccountConfirmation->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($userAccountConfirmation->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $userAccountConfirmation->hasValue('user') ? $this->Html->link($userAccountConfirmation->user->username, ['controller' => 'Users', 'action' => 'view', $userAccountConfirmation->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Confirmation Code') ?></th>
                    <td><?= h($userAccountConfirmation->confirmation_code) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($userAccountConfirmation->created) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>