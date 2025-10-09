<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UserAccountConfirmation $userAccountConfirmation
 * @var string[]|\Cake\Collection\CollectionInterface $users
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $userAccountConfirmation->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $userAccountConfirmation->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List User Account Confirmations'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="userAccountConfirmations form content">
            <?= $this->Form->create($userAccountConfirmation) ?>
            <fieldset>
                <legend><?= __('Edit User Account Confirmation') ?></legend>
                <?php
                    echo $this->Form->control('user_id', ['options' => $users]);
                    echo $this->Form->control('confirmation_code');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
