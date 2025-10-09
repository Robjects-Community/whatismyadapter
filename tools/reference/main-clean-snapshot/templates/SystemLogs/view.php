<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\SystemLog $systemLog
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit System Log'), ['action' => 'edit', $systemLog->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete System Log'), ['action' => 'delete', $systemLog->id], ['confirm' => __('Are you sure you want to delete # {0}?', $systemLog->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List System Logs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New System Log'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="systemLogs view content">
            <h3><?= h($systemLog->level) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($systemLog->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Level') ?></th>
                    <td><?= h($systemLog->level) ?></td>
                </tr>
                <tr>
                    <th><?= __('Group Name') ?></th>
                    <td><?= h($systemLog->group_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($systemLog->created) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Message') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($systemLog->message)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Context') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($systemLog->context)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>