<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate $emailTemplate
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Email Template'), ['action' => 'edit', $emailTemplate->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Email Template'), ['action' => 'delete', $emailTemplate->id], ['confirm' => __('Are you sure you want to delete # {0}?', $emailTemplate->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Email Templates'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Email Template'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="emailTemplates view content">
            <h3><?= h($emailTemplate->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($emailTemplate->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Template Identifier') ?></th>
                    <td><?= h($emailTemplate->template_identifier) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($emailTemplate->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Subject') ?></th>
                    <td><?= h($emailTemplate->subject) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($emailTemplate->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($emailTemplate->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Body Html') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($emailTemplate->body_html)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Body Plain') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($emailTemplate->body_plain)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>