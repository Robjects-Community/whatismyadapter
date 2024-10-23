<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\QuestionOption $questionOption
 * @var string[]|\Cake\Collection\CollectionInterface $questions
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $questionOption->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $questionOption->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Question Options'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="questionOptions form content">
            <?= $this->Form->create($questionOption) ?>
            <fieldset>
                <legend><?= __('Edit Question Option') ?></legend>
                <?php
                    echo $this->Form->control('question_id', ['options' => $questions]);
                    echo $this->Form->control('option_text');
                    echo $this->Form->control('is_correct');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
