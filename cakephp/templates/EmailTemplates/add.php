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
            <?= $this->Html->link(__('List Email Templates'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="emailTemplates form content">
            <?= $this->Form->create($emailTemplate) ?>
            <fieldset>
                <legend><?= __('Add Email Template') ?></legend>
                <?php
                    echo $this->Form->control('template_identifier');
                    echo $this->Form->control('name');
                    echo $this->Form->control('subject');
                    echo $this->Form->control('body_html');
                    echo $this->Form->control('body_plain');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
