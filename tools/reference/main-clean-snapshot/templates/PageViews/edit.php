<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\PageView $pageView
 * @var string[]|\Cake\Collection\CollectionInterface $articles
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $pageView->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $pageView->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Page Views'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="pageViews form content">
            <?= $this->Form->create($pageView) ?>
            <fieldset>
                <legend><?= __('Edit Page View') ?></legend>
                <?php
                    echo $this->Form->control('article_id', ['options' => $articles]);
                    echo $this->Form->control('product_id');
                    echo $this->Form->control('ip_address');
                    echo $this->Form->control('user_agent');
                    echo $this->Form->control('referer');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
