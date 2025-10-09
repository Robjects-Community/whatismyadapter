<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ProductFormField $productFormField
 */

$types = [
    'text' => 'Text',
    'textarea' => 'Textarea',
    'email' => 'Email',
    'number' => 'Number',
    'select' => 'Select',
    'radio' => 'Radio',
    'checkbox' => 'Checkbox',
    'file' => 'File',
    'date' => 'Date',
    'url' => 'URL',
    'json' => 'JSON',
];
?>
<div class="card shadow-sm">
    <div class="card-body">
        <?= $this->Form->create($productFormField, ['type' => 'post']) ?>
        <div class="row g-3">
            <div class="col-md-6">
                <?= $this->Form->control('label', ['label' => __('Label'), 'class' => 'form-control', 'required' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $this->Form->control('name', ['label' => __('Machine Name'), 'class' => 'form-control', 'required' => true, 'help' => __('Unique key, e.g., model_number, support_url')]) ?>
            </div>
            <div class="col-md-4">
                <?= $this->Form->control('type', ['label' => __('Field Type'), 'options' => $types, 'class' => 'form-select']) ?>
            </div>
            <div class="col-md-4">
                <?= $this->Form->control('field_group', ['label' => __('Group'), 'class' => 'form-control', 'placeholder' => __('e.g., Basics, Details, Links')]) ?>
            </div>
            <div class="col-md-4">
                <?= $this->Form->control('order_index', ['label' => __('Order Index'), 'class' => 'form-control', 'type' => 'number', 'min' => 0]) ?>
            </div>

            <div class="col-md-6">
                <?= $this->Form->control('placeholder', ['label' => __('Placeholder'), 'class' => 'form-control']) ?>
            </div>
            <div class="col-md-6">
                <?= $this->Form->control('default_value', ['label' => __('Default Value'), 'class' => 'form-control']) ?>
            </div>
            <div class="col-12">
                <?= $this->Form->control('help_text', ['label' => __('Help Text'), 'class' => 'form-control', 'type' => 'textarea', 'rows' => 2]) ?>
            </div>

            <div class="col-md-6">
                <?= $this->Form->control('options_json', ['label' => __('Options (JSON for select/radio)'), 'class' => 'form-control font-monospace', 'type' => 'textarea', 'rows' => 4, 'placeholder' => '{"key":"Label"}']) ?>
            </div>
            <div class="col-md-6">
                <?= $this->Form->control('validation_rules_json', ['label' => __('Validation Rules (JSON)'), 'class' => 'form-control font-monospace', 'type' => 'textarea', 'rows' => 4, 'placeholder' => '{"regex":"^\\\n$","message":"..."}']) ?>
                <div class="form-text"><?= __('Example: {"regex":"^[A-Z0-9-]+$","message":"Only uppercase letters, numbers, and dashes."}') ?></div>
            </div>

            <div class="col-md-3 form-check form-switch">
                <?= $this->Form->control('required', ['label' => __('Required'), 'type' => 'checkbox', 'class' => 'form-check-input']) ?>
            </div>
            <div class="col-md-3 form-check form-switch">
                <?= $this->Form->control('visible_public', ['label' => __('Visible on Public Form'), 'type' => 'checkbox', 'class' => 'form-check-input']) ?>
            </div>
            <div class="col-md-3 form-check form-switch">
                <?= $this->Form->control('ai_enabled', ['label' => __('AI Suggestions Enabled'), 'type' => 'checkbox', 'class' => 'form-check-input']) ?>
            </div>
            <div class="col-md-3 form-check form-switch">
                <?= $this->Form->control('is_active', ['label' => __('Active'), 'type' => 'checkbox', 'class' => 'form-check-input']) ?>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= $this->Form->button(__('Save Field'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
