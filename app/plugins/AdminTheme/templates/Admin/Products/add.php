<?php
/**
 * Admin Products Add Template
 */
$this->assign('title', __('Add Product'));
?>

<div class="products-form">
    <div class="container-fluid">
        <div class="page-header mb-4">
            <h1 class="page-title"><?= __('Add Product') ?></h1>
        </div>

        <div class="card">
            <div class="card-body">
                <?= $this->Form->create($product) ?>
                <fieldset>
                    <legend><?= __('Product Information') ?></legend>
                    <?= $this->Form->control('title', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('slug', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('description', ['type' => 'textarea', 'class' => 'form-control']) ?>
                    <?= $this->Form->control('manufacturer', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('model_number', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('price', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('currency', ['class' => 'form-control', 'default' => 'USD']) ?>
                    <?= $this->Form->control('image', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('alt_text', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('is_published', ['type' => 'checkbox']) ?>
                    <?= $this->Form->control('featured', ['type' => 'checkbox']) ?>
                    <?= $this->Form->control('tags._ids', ['options' => $tags, 'class' => 'form-control', 'multiple' => true]) ?>
                </fieldset>
                <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
