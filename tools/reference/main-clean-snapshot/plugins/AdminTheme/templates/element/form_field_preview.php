<?php
/**
 * Form Field Preview Element
 * @var object $field Form field entity
 */
?>

<?php switch ($field->field_type): ?>
    <?php case 'text': ?>
    <?php case 'email': ?>
    <?php case 'url': ?>
        <input type="<?= h($field->field_type) ?>" 
               class="form-control <?= h($field->css_classes) ?>" 
               placeholder="<?= h($field->field_placeholder) ?>" 
               <?= $field->is_required ? 'required' : '' ?>
               disabled>
        <?php break; ?>

    <?php case 'number': ?>
        <input type="number" 
               class="form-control <?= h($field->css_classes) ?>" 
               placeholder="<?= h($field->field_placeholder) ?>" 
               <?= $field->is_required ? 'required' : '' ?>
               disabled>
        <?php break; ?>

    <?php case 'textarea': ?>
        <textarea class="form-control <?= h($field->css_classes) ?>" 
                  rows="2" 
                  placeholder="<?= h($field->field_placeholder) ?>"
                  <?= $field->is_required ? 'required' : '' ?>
                  disabled></textarea>
        <?php break; ?>

    <?php case 'select': ?>
        <select class="form-select <?= h($field->css_classes) ?>" 
                <?= $field->is_required ? 'required' : '' ?>
                disabled>
            <option value="">Choose...</option>
            <?php if ($field->field_options): ?>
                <?php $options = json_decode($field->field_options, true); ?>
                <?php foreach ($options as $value => $label): ?>
                    <option value="<?= h($value) ?>"><?= h($label) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <?php break; ?>

    <?php case 'checkbox': ?>
        <div class="form-check">
            <input type="checkbox" 
                   class="form-check-input <?= h($field->css_classes) ?>" 
                   disabled>
            <label class="form-check-label">
                <?= h($field->field_label) ?>
            </label>
        </div>
        <?php break; ?>

    <?php case 'radio': ?>
        <?php if ($field->field_options): ?>
            <?php $options = json_decode($field->field_options, true); ?>
            <?php foreach (array_slice($options, 0, 2) as $value => $label): ?>
                <div class="form-check">
                    <input type="radio" 
                           class="form-check-input <?= h($field->css_classes) ?>" 
                           name="preview_<?= h($field->field_name) ?>"
                           disabled>
                    <label class="form-check-label">
                        <?= h($label) ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <?php if (count($options) > 2): ?>
                <small class="text-muted">...and <?= count($options) - 2 ?> more</small>
            <?php endif; ?>
        <?php endif; ?>
        <?php break; ?>

    <?php case 'file': ?>
        <input type="file" 
               class="form-control <?= h($field->css_classes) ?>" 
               disabled>
        <?php break; ?>

    <?php case 'date': ?>
        <input type="date" 
               class="form-control <?= h($field->css_classes) ?>" 
               disabled>
        <?php break; ?>

    <?php default: ?>
        <input type="text" 
               class="form-control <?= h($field->css_classes) ?>" 
               placeholder="<?= h($field->field_placeholder) ?>" 
               disabled>
        <?php break; ?>
<?php endswitch; ?>

<?php if ($field->field_help_text): ?>
    <small class="form-text text-muted mt-1 d-block">
        <?= h(substr($field->field_help_text, 0, 60)) ?>
        <?php if (strlen($field->field_help_text) > 60): ?>...<?php endif; ?>
    </small>
<?php endif; ?>
