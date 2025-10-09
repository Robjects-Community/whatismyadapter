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
            <?= $this->Html->link(__('Edit Blocked Ip'), ['action' => 'edit', $blockedIp->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Blocked Ip'), ['action' => 'delete', $blockedIp->id], ['confirm' => __('Are you sure you want to delete # {0}?', $blockedIp->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Blocked Ips'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Blocked Ip'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="blockedIps view content">
            <h3><?= h($blockedIp->ip_address) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($blockedIp->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ip Address') ?></th>
                    <td><?= h($blockedIp->ip_address) ?></td>
                </tr>
                <tr>
                    <th><?= __('Blocked At') ?></th>
                    <td><?= h($blockedIp->blocked_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Expires At') ?></th>
                    <td><?= h($blockedIp->expires_at) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($blockedIp->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($blockedIp->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Reason') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($blockedIp->reason)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>