<?php
/**
 * Settings Add Template
 * 
 * Form to create a new setting
 */

$this->assign('title', __('Add Setting'));
$this->Html->meta('description', __('Add a new application setting'), ['block' => 'meta']);
?>

<div class="settings-add">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0"><?= __('Add Setting') ?></h2>
                    </div>
                    <div class="card-body">
                        <?= $this->Form->create($setting, ['class' => 'setting-form']) ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?= $this->Form->control('category', [
                                    'label' => __('Category'),
                                    'class' => 'form-control',
                                    'required' => true,
                                    'placeholder' => __('e.g., general, email, api')
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('key_name', [
                                    'label' => __('Key Name'),
                                    'class' => 'form-control',
                                    'required' => true,
                                    'placeholder' => __('e.g., site_name, smtp_host')
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
                                    ],
                                    'default' => 'string'
                                ]) ?>
                            </div>
                        </div>

                        <?= $this->Form->control('description', [
                            'label' => __('Description'),
                            'class' => 'form-control',
                            'type' => 'textarea',
                            'rows' => 2,
                            'placeholder' => __('Optional description of this setting')
                        ]) ?>

                        <div class="row">
                            <div class="col-md-4">
                                <?= $this->Form->control('ordering', [
                                    'label' => __('Display Order'),
                                    'class' => 'form-control',
                                    'type' => 'number',
                                    'default' => 0
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
                                    ],
                                    'default' => '6'
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $this->Form->control('value_obscure', [
                                    'label' => __('Obscure Value'),
                                    'class' => 'form-check-input',
                                    'type' => 'checkbox',
                                    'help' => __('Hide value in listings')
                                ]) ?>
                            </div>
                        </div>

                        <div class="form-actions mt-4">
                            <?= $this->Form->submit(__('Save Setting'), ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                __('Cancel'),
                                ['action' => 'index'],
                                ['class' => 'btn btn-secondary']
                            ) ?>
                        </div>

                        <?= $this->Form->end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
