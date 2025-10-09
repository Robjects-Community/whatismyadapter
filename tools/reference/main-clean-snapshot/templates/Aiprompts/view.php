<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Aiprompt $aiprompt
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Aiprompt'), ['action' => 'edit', $aiprompt->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Aiprompt'), ['action' => 'delete', $aiprompt->id], ['confirm' => __('Are you sure you want to delete # {0}?', $aiprompt->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Aiprompts'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Aiprompt'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="aiprompts view content">
            <h3><?= h($aiprompt->task_type) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($aiprompt->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Task Type') ?></th>
                    <td><?= h($aiprompt->task_type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Model') ?></th>
                    <td><?= h($aiprompt->model) ?></td>
                </tr>
                <tr>
                    <th><?= __('Max Tokens') ?></th>
                    <td><?= $this->Number->format($aiprompt->max_tokens) ?></td>
                </tr>
                <tr>
                    <th><?= __('Temperature') ?></th>
                    <td><?= $this->Number->format($aiprompt->temperature) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($aiprompt->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($aiprompt->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('System Prompt') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($aiprompt->system_prompt)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>