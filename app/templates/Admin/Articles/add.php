<?php
/**
 * Admin Articles Add Template
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $Article
 */
$this->assign('title', __('Add Article'));
?>
<div class="Article form content">
    <?= $this->Form->create($Article ?? null) ?>
    <fieldset>
        <legend><?= __('Add Article') ?></legend>
        <?php
            echo $this->Form->control('title');
            echo $this->Form->control('description', ['type' => 'textarea']);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
