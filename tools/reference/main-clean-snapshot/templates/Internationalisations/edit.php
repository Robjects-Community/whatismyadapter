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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $internationalisation->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $internationalisation->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Internationalisations'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="internationalisations form content">
            <?= $this->Form->create($internationalisation) ?>
            <fieldset>
                <legend><?= __('Edit Internationalisation') ?></legend>
                <?php
                    echo $this->Form->control('locale');
                    echo $this->Form->control('message_id');
                    echo $this->Form->control('message_str');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
