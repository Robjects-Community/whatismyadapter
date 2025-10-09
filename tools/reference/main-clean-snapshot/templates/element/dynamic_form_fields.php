<?php
/**
 * Dynamic Form Fields Element
 * @var array $fields Array of form field entities
 * @var \App\Model\Entity\Product $product Product entity
 * @var array $tags Available tags (for tag fields)
 */

// Helper function to get field options
function getFieldOptions($field, $tags = []) {
    if ($field->field_options) {
        return json_decode($field->field_options, true);
    }
    
    // Special handling for certain field types
    if ($field->field_name === 'tags' && !empty($tags)) {
        $options = [];
        foreach ($tags as $key => $value) {
            $options[$key] = is_array($value) ? $value['title'] : $value;
        }
        return $options;
    }
    
    return [];
}

// Helper function to get HTML attributes
function getHtmlAttributes($field) {
    $baseAttributes = [
        'class' => 'form-control ' . ($field->css_classes ?: ''),
        'id' => $field->field_name,
        'name' => $field->field_name,
    ];
    
    if ($field->field_placeholder) {
        $baseAttributes['placeholder'] = $field->field_placeholder;
    }
    
    if ($field->is_required) {
        $baseAttributes['required'] = true;
    }
    
    if ($field->ai_enabled) {
        $baseAttributes['data-ai-enabled'] = 'true';
    }
    
    // Add custom HTML attributes from field configuration
    if ($field->html_attributes) {
        $customAttrs = json_decode($field->html_attributes, true);
        if (is_array($customAttrs)) {
            $baseAttributes = array_merge($baseAttributes, $customAttrs);
        }
    }
    
    return $baseAttributes;
}

// Sort fields by display order
usort($fields, function($a, $b) {
    return $a->display_order <=> $b->display_order;
});

// Group fields into rows based on column width
$currentRow = [];
$currentWidth = 0;
$rows = [];

foreach ($fields as $field) {
    if (!$field->is_active) {
        continue; // Skip inactive fields
    }
    
    $fieldWidth = $field->column_width ?: 12;
    
    // If adding this field would exceed 12 columns, start a new row
    if ($currentWidth + $fieldWidth > 12) {
        if (!empty($currentRow)) {
            $rows[] = $currentRow;
        }
        $currentRow = [];
        $currentWidth = 0;
    }
    
    $currentRow[] = $field;
    $currentWidth += $fieldWidth;
    
    // If we've reached exactly 12 columns, complete the row
    if ($currentWidth >= 12) {
        $rows[] = $currentRow;
        $currentRow = [];
        $currentWidth = 0;
    }
}

// Add any remaining fields to the last row
if (!empty($currentRow)) {
    $rows[] = $currentRow;
}
?>

<?php foreach ($rows as $row): ?>
    <div class="row">
        <?php foreach ($row as $field): ?>
            <?php 
            $colClass = 'col-md-' . ($field->column_width ?: 12);
            $validation = $field->field_validation ? json_decode($field->field_validation, true) : [];
            $options = getFieldOptions($field, $tags);
            $attributes = getHtmlAttributes($field);
            ?>
            
            <div class="<?= $colClass ?> mb-3">
                <div class="field-wrapper <?= $field->ai_enabled ? 'field-with-ai' : '' ?>">
                    <?php switch ($field->field_type): ?>
                        
                        <?php case 'text': ?>
                        <?php case 'email': ?>
                        <?php case 'url': ?>
                            <?php $attributes['type'] = $field->field_type; ?>
                            <?= $this->Form->control($field->field_name, [
                                'label' => $field->field_label . ($field->is_required ? ' *' : ''),
                                'type' => $field->field_type,
                                'class' => $attributes['class'] . ($this->Form->isFieldError($field->field_name) ? ' is-invalid' : ''),
                                'placeholder' => $field->field_placeholder,
                                'required' => $field->is_required,
                                'data-ai-enabled' => $field->ai_enabled ? 'true' : 'false'
                            ] + (isset($validation['max_length']) ? ['maxlength' => $validation['max_length']] : [])) ?>
                            <?php break; ?>
                            
                        <?php case 'number': ?>
                            <?= $this->Form->control($field->field_name, [
                                'label' => $field->field_label . ($field->is_required ? ' *' : ''),
                                'type' => 'number',
                                'class' => $attributes['class'] . ($this->Form->isFieldError($field->field_name) ? ' is-invalid' : ''),
                                'placeholder' => $field->field_placeholder,
                                'required' => $field->is_required,
                                'data-ai-enabled' => $field->ai_enabled ? 'true' : 'false',
                                'step' => $validation['step'] ?? '0.01',
                                'min' => $validation['min'] ?? null,
                                'max' => $validation['max'] ?? null
                            ]) ?>
                            <?php break; ?>
                            
                        <?php case 'textarea': ?>
                            <?= $this->Form->control($field->field_name, [
                                'label' => $field->field_label . ($field->is_required ? ' *' : ''),
                                'type' => 'textarea',
                                'class' => $attributes['class'] . ($this->Form->isFieldError($field->field_name) ? ' is-invalid' : ''),
                                'placeholder' => $field->field_placeholder,
                                'required' => $field->is_required,
                                'data-ai-enabled' => $field->ai_enabled ? 'true' : 'false',
                                'rows' => $attributes['rows'] ?? 4
                            ]) ?>
                            <?php break; ?>
                            
                        <?php case 'select': ?>
                            <?php if ($field->field_name === 'tags._ids'): ?>
                                <?= $this->Form->control('tags._ids', [
                                    'label' => $field->field_label . ($field->is_required ? ' *' : ''),
                                    'options' => $tags,
                                    'multiple' => true,
                                    'class' => 'form-select' . ($this->Form->isFieldError('tags._ids') ? ' is-invalid' : ''),
                                    'size' => 6,
                                    'data-placeholder' => $field->field_placeholder ?: 'Select categories...'
                                ]) ?>
                            <?php else: ?>
                                <?= $this->Form->control($field->field_name, [
                                    'label' => $field->field_label . ($field->is_required ? ' *' : ''),
                                    'type' => 'select',
                                    'options' => $options,
                                    'empty' => $field->field_placeholder ?: 'Choose...',
                                    'class' => 'form-select' . ($this->Form->isFieldError($field->field_name) ? ' is-invalid' : ''),
                                    'required' => $field->is_required,
                                    'default' => $field->default_value
                                ]) ?>
                            <?php endif; ?>
                            <?php break; ?>
                            
                        <?php case 'checkbox': ?>
                            <div class="form-check">
                                <?= $this->Form->checkbox($field->field_name, [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError($field->field_name) ? ' is-invalid' : ''),
                                    'required' => $field->is_required
                                ]) ?>
                                <label class="form-check-label" for="<?= h($field->field_name) ?>">
                                    <?= h($field->field_label) ?>
                                    <?php if ($field->is_required): ?>
                                        <span class="text-danger">*</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                            <?php break; ?>
                            
                        <?php case 'radio': ?>
                            <fieldset>
                                <legend class="form-label">
                                    <?= h($field->field_label) ?>
                                    <?php if ($field->is_required): ?>
                                        <span class="text-danger">*</span>
                                    <?php endif; ?>
                                </legend>
                                <?php foreach ($options as $value => $label): ?>
                                    <div class="form-check">
                                        <?= $this->Form->radio($field->field_name, [
                                            $value => $label
                                        ], [
                                            'class' => 'form-check-input',
                                            'required' => $field->is_required,
                                            'legend' => false,
                                            'fieldset' => false
                                        ]) ?>
                                    </div>
                                <?php endforeach; ?>
                            </fieldset>
                            <?php break; ?>
                            
                        <?php case 'file': ?>
                            <?= $this->Form->control($field->field_name, [
                                'label' => $field->field_label . ($field->is_required ? ' *' : ''),
                                'type' => 'file',
                                'class' => 'form-control' . ($this->Form->isFieldError($field->field_name) ? ' is-invalid' : ''),
                                'required' => $field->is_required,
                                'accept' => $attributes['accept'] ?? null
                            ]) ?>
                            <?php break; ?>
                            
                        <?php case 'date': ?>
                            <?= $this->Form->control($field->field_name, [
                                'label' => $field->field_label . ($field->is_required ? ' *' : ''),
                                'type' => 'date',
                                'class' => 'form-control' . ($this->Form->isFieldError($field->field_name) ? ' is-invalid' : ''),
                                'required' => $field->is_required
                            ]) ?>
                            <?php break; ?>
                            
                        <?php default: ?>
                            <?= $this->Form->control($field->field_name, [
                                'label' => $field->field_label . ($field->is_required ? ' *' : ''),
                                'type' => 'text',
                                'class' => $attributes['class'] . ($this->Form->isFieldError($field->field_name) ? ' is-invalid' : ''),
                                'placeholder' => $field->field_placeholder,
                                'required' => $field->is_required,
                                'data-ai-enabled' => $field->ai_enabled ? 'true' : 'false'
                            ]) ?>
                            <?php break; ?>
                            
                    <?php endswitch; ?>
                    
                    <!-- AI Indicator -->
                    <?php if ($field->ai_enabled): ?>
                        <div class="ai-field-indicator">
                            <span class="badge bg-success position-absolute" 
                                  style="top: 8px; right: 8px; font-size: 0.6rem; z-index: 5;">
                                <i class="fas fa-magic"></i> AI
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Help Text -->
                    <?php if ($field->field_help_text): ?>
                        <div class="form-text">
                            <i class="fas fa-info-circle text-info me-1"></i>
                            <?= h($field->field_help_text) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Validation Error -->
                    <?php if ($this->Form->isFieldError($field->field_name)): ?>
                        <div class="invalid-feedback">
                            <?= $this->Form->error($field->field_name) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
