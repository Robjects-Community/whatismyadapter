<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\QuestionOption> $questionOptions
 */
?>
<div class="questionOptions index content">
    <?= $this->Html->link(__('New Question Option'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Question Options') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('question_id') ?></th>
                    <th><?= $this->Paginator->sort('option_text') ?></th>
                    <th><?= $this->Paginator->sort('is_correct') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questionOptions as $questionOption): ?>
                <tr>
                    <td><?= $this->Number->format($questionOption->id) ?></td>
                    <td><?= $questionOption->hasValue('question') ? $this->Html->link($questionOption->question->title, ['controller' => 'Questions', 'action' => 'view', $questionOption->question->id]) : '' ?></td>
                    <td><?= h($questionOption->option_text) ?></td>
                    <td><?= h($questionOption->is_correct) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $questionOption->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $questionOption->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $questionOption->id], ['confirm' => __('Are you sure you want to delete # {0}?', $questionOption->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>