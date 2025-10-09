<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UserAccountConfirmation $userAccountConfirmation
 * @var \Cake\Collection\CollectionInterface|string[] $users
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List User Account Confirmations'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="userAccountConfirmations form content">
            <?= $this->Form->create($userAccountConfirmation) ?>
            <fieldset>
                <legend><?= __('Add User Account Confirmation') ?></legend>
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
