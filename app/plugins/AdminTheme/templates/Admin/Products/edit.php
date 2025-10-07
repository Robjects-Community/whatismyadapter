<?php
/**
 * Admin Products Edit Template
 */
$this->assign('title', __('Edit Product'));
?>

<div class="products-form">
    <div class="container-fluid">
        <div class="page-header mb-4">
            <h1 class="page-title"><?= __('Edit Product: {0}', h($product->title)) ?></h1>
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
                    <?= $this->Form->control('currency', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('image', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('alt_text', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('is_published', ['type' => 'checkbox']) ?>
                    <?= $this->Form->control('featured', ['type' => 'checkbox']) ?>
                    <?= $this->Form->control('tags._ids', ['options' => $tags, 'class' => 'form-control', 'multiple' => true]) ?>
                </fieldset>
                <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
                <?= $this->Form->postLink(
                    __('Delete'),
                    ['action' => 'delete', $product->id],
                    ['confirm' => __('Are you sure?'), 'class' => 'btn btn-danger']
                ) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
