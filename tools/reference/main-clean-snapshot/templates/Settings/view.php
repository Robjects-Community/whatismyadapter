<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Setting $setting
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Setting'), ['action' => 'edit', $setting->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Setting'), ['action' => 'delete', $setting->id], ['confirm' => __('Are you sure you want to delete # {0}?', $setting->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Settings'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Setting'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="settings view content">
            <h3><?= h($setting->category) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($setting->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Category') ?></th>
                    <td><?= h($setting->category) ?></td>
                </tr>
                <tr>
                    <th><?= __('Key Name') ?></th>
                    <td><?= h($setting->key_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Value Type') ?></th>
                    <td><?= h($setting->value_type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ordering') ?></th>
                    <td><?= $this->Number->format($setting->ordering) ?></td>
                </tr>
                <tr>
                    <th><?= __('Column Width') ?></th>
                    <td><?= $this->Number->format($setting->column_width) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($setting->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($setting->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Value Obscure') ?></th>
                    <td><?= $setting->value_obscure ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Value') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($setting->value)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($setting->description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Data') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($setting->data)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>