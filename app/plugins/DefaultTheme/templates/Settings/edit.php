<?php
/**
 * Settings Edit Template
 * 
 * Form to edit an existing setting
 */

$this->assign('title', __('Edit Setting'));
$this->Html->meta('description', __('Edit application setting'), ['block' => 'meta']);
?>

<div class="settings-edit">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0"><?= __('Edit Setting') ?></h2>
                    </div>
                    <div class="card-body">
                        <?= $this->Form->create($setting, ['class' => 'setting-form']) ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?= $this->Form->control('category', [
                                    'label' => __('Category'),
                                    'class' => 'form-control',
                                    'required' => true
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('key_name', [
                                    'label' => __('Key Name'),
                                    'class' => 'form-control',
                                    'required' => true
                                ]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <?= $this->Form->control('value', [
                                    'label' => __('Value'),
                                    'class' => 'form-control',
                                    'type' => 'textarea',
                                    'rows' => 3
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $this->Form->control('value_type', [
                                    'label' => __('Value Type'),
                                    'class' => 'form-select',
                                    'options' => [
                                        'string' => __('String'),
                                        'integer' => __('Integer'),
                                        'boolean' => __('Boolean'),
                                        'textarea' => __('Textarea'),
                                        'select' => __('Select'),
                                        'password' => __('Password'),
                                    ]
                                ]) ?>
                            </div>
                        </div>

                        <?= $this->Form->control('description', [
                            'label' => __('Description'),
                            'class' => 'form-control',
                            'type' => 'textarea',
                            'rows' => 2
                        ]) ?>

                        <div class="row">
                            <div class="col-md-4">
                                <?= $this->Form->control('ordering', [
                                    'label' => __('Display Order'),
                                    'class' => 'form-control',
                                    'type' => 'number'
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $this->Form->control('column_width', [
                                    'label' => __('Column Width'),
                                    'class' => 'form-select',
                                    'options' => [
                                        '4' => __('Small (1/3)'),
                                        '6' => __('Medium (1/2)'),
                                        '8' => __('Large (2/3)'),
                                        '12' => __('Full Width')
                                    ]
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <?= $this->Form->control('value_obscure', [
                                        'label' => __('Obscure Value'),
                                        'class' => 'form-check-input',
                                        'type' => 'checkbox'
                                    ]) ?>
                                    <small class="form-text text-muted">
                                        <?= __('Hide value in listings for security') ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions mt-4">
                            <?= $this->Form->submit(__('Save Changes'), ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                __('Cancel'),
                                ['action' => 'view', $setting->id],
                                ['class' => 'btn btn-secondary']
                            ) ?>
                            <?= $this->Form->postLink(
                                __('Delete'),
                                ['action' => 'delete', $setting->id],
                                [
                                    'class' => 'btn btn-danger float-end',
                                    'confirm' => __('Are you sure you want to delete this setting?')
                                ]
                            ) ?>
                        </div>

                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
