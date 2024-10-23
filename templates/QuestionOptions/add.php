<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\QuestionOption $questionOption
 * @var \Cake\Collection\CollectionInterface|string[] $questions
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Question Options'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="questionOptions form content">
            <?= $this->Form->create($questionOption) ?>
            <fieldset>
                <legend><?= __('Add Question Option') ?></legend>
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
