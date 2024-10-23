<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\QuestionOption $questionOption
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Question Option'), ['action' => 'edit', $questionOption->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Question Option'), ['action' => 'delete', $questionOption->id], ['confirm' => __('Are you sure you want to delete # {0}?', $questionOption->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Question Options'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Question Option'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="questionOptions view content">
            <h3><?= h($questionOption->option_text) ?></h3>
            <table>
                <tr>
                    <th><?= __('Question') ?></th>
                    <td><?= $questionOption->hasValue('question') ? $this->Html->link($questionOption->question->title, ['controller' => 'Questions', 'action' => 'view', $questionOption->question->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Option Text') ?></th>
                    <td><?= h($questionOption->option_text) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($questionOption->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Correct') ?></th>
                    <td><?= $questionOption->is_correct ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>