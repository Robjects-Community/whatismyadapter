<?php
/**
 * Standardized Form Input Element
 * 
 * Provides consistent form inputs with Bootstrap validation styling
 * 
 * @var \App\View\AppView $this
 * @var string $field Field name
 * @var string $type Input type (text, textarea, email, etc.)
 * @var string $label Label text (auto-generated if not provided)
 * @var array $options Additional input options
 * @var string $help Help text to display below input
 * @var bool $required Whether field is required
 * @var string $containerClass Additional classes for the container div
 */

// Set defaults
$type = $type ?? 'text';
$label = $label ?? null;
$options = $options ?? [];
$help = $help ?? null;
$required = $required ?? false;
$containerClass = $containerClass ?? 'mb-3';

// Auto-generate label if not provided
if ($label === null) {
    $label = __(Cake\Utility\Inflector::humanize($field));
}

// Add Bootstrap classes and validation
$hasError = $this->Form->isFieldError($field);
$baseClass = '';

// Determine base class based on input type
switch ($type) {
    case 'checkbox':
        $baseClass = 'form-check-input';
        break;
    case 'select':
        $baseClass = 'form-select';
        break;
    case 'file':
        $baseClass = 'form-control';
        break;
    default:
        $baseClass = 'form-control';
}

// Add validation classes
$validationClass = $hasError ? ' is-invalid' : '';
$options['class'] = ($options['class'] ?? '') . ' ' . $baseClass . $validationClass;

// Handle required attribute
if ($required && !isset($options['required'])) {
    $options['required'] = true;
}

// Handle label for checkbox
$showLabel = $type !== 'checkbox';
$checkboxLabel = $type === 'checkbox' ? $label : null;

if (!$showLabel) {
    $options['label'] = false;
}
?>

<div class="<?= h($containerClass) ?>">
    <?php if ($showLabel): ?>
        <label class="form-label<?= $required ? ' required' : '' ?>" for="<?= h(str_replace(['[', ']', '.'], ['-', '', '-'], $field)) ?>">
            <?= h($label) ?>
            <?php if ($required): ?>
                <span class="text-danger">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <?php if ($type === 'checkbox'): ?>
        <div class="form-check">
            <?= $this->Form->control($field, array_merge($options, ['type' => $type])) ?>
            <label class="form-check-label" for="<?= h(str_replace(['[', ']', '.'], ['-', '', '-'], $field)) ?>">
                <?= h($checkboxLabel) ?>
                <?php if ($required): ?>
                    <span class="text-danger">*</span>
                <?php endif; ?>
            </label>
        </div>
    <?php else: ?>
        <?= $this->Form->control($field, array_merge($options, [
            'type' => $type,
            'label' => false
        ])) ?>
    <?php endif; ?>

    <?php if ($hasError): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error($field) ?>
        </div>
    <?php endif; ?>

    <?php if ($help): ?>
        <div class="form-text">
            <?= $help ?>
        </div>
    <?php endif; ?>
</div>