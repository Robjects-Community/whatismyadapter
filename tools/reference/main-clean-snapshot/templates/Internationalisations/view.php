<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Internationalisation $internationalisation
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Internationalisation'), ['action' => 'edit', $internationalisation->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Internationalisation'), ['action' => 'delete', $internationalisation->id], ['confirm' => __('Are you sure you want to delete # {0}?', $internationalisation->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Internationalisations'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Internationalisation'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="internationalisations view content">
            <h3><?= h($internationalisation->message_id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($internationalisation->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Locale') ?></th>
                    <td><?= h($internationalisation->locale) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($internationalisation->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($internationalisation->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Message Id') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($internationalisation->message_id)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Message Str') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($internationalisation->message_str)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>