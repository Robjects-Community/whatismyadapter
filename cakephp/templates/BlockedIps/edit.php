<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\BlockedIp $blockedIp
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $blockedIp->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $blockedIp->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Blocked Ips'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="blockedIps form content">
            <?= $this->Form->create($blockedIp) ?>
            <fieldset>
                <legend><?= __('Edit Blocked Ip') ?></legend>
                <?php
                    echo $this->Form->control('ip_address');
                    echo $this->Form->control('reason');
                    echo $this->Form->control('blocked_at');
                    echo $this->Form->control('expires_at', ['empty' => true]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
