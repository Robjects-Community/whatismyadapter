<?php
/**
 * Admin Aiprompts Add Template
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Aiprompt $Aiprompt
 */
$this->assign('title', __('Add Aiprompt'));
?>
<div class="Aiprompt form content">
    <?= $this->Form->create($Aiprompt ?? null) ?>
    <fieldset>
        <legend><?= __('Add Aiprompt') ?></legend>
        <?php
            echo $this->Form->control('title');
            echo $this->Form->control('description', ['type' => 'textarea']);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
