<?php
/**
 * @var \App\View\AppView $this
 * @var array $groupedSettings
 */
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Settings') ?></h5>
                </div>

                <div class="card-body">
                    <?= $this->Form->create(null, ['url' => ['action' => 'saveSettings'], 'class' => 'needs-validation', 'novalidate' => true]) ?>
                    <?php foreach ($groupedSettings as $category => $settings): ?>
                        <h4 class="mb-3 mt-4 text-secondary"><?= h($this->makeHumanReadable($category)) ?></h4>
                        <div class="row">
                        <?php foreach ($settings as $key => $setting): ?>
                            <div class="col-md-<?= isset($setting['column_width']) ? $setting['column_width'] : 2; ?> mb-1">
                                <?php
                                $value = $setting['value'];
                                $value_type = $setting['value_type'];
                                $obscure = isset($setting['value_obscure']) && $setting['value_obscure'] == 1;
                                $description = $setting['description'];
                                $tooltipAttrs = !empty($description) ? [
                                    'data-bs-toggle' => 'tooltip',
                                    'data-bs-placement' => 'top',
                                    'title' => html_entity_decode($description)
                                ] : [];
                                ?>
                                <?php if ($value_type === 'bool'): ?>
                                    <div class="form-check form-switch" <?= $this->Html->templater()->formatAttributes($tooltipAttrs) ?>>
                                        <?= $this->Form->checkbox("{$category}.{$key}", [
                                            'label' => false,
                                            'value' => 1,
                                            'class' => 'form-check-input',
                                            'checked' => (bool)$value,
                                            'type' => 'checkbox'
                                        ]) ?>
                                        <label class="form-check-label" for="<?= "{$category}-{$key}" ?>">
                                            <?= $this->makeHumanReadable($key) ?>
                                        </label>
                                    </div>
                                <?php elseif ($value_type === 'select-page'): ?>
                                    <label class="form-check-label" for="<?= "{$category}-{$key}" ?>" 
                                        <?= $this->Html->templater()->formatAttributes($tooltipAttrs) ?>>
                                        <?= $this->makeHumanReadable($key) ?>
                                    </label>
                                    <?= $this->Form->select("{$category}.{$key}", $sitePages, [
                                        'label' => $this->makeHumanReadable($key),
                                        'value' => $value,
                                        'class' => 'form-control',
                                    ]) ?>
                                <?php elseif ($value_type === 'textarea'): ?>
                                    <?= $this->Form->control("{$category}.{$key}", array_merge([
                                        'label' => $this->makeHumanReadable($key),
                                        'value' => $value,
                                        'class' => 'form-control',
                                        'type' => 'textarea',
                                        'rows' => 8,
                                        'placeholder' => __('Enter text')
                                    ], $tooltipAttrs)) ?>
                                <?php elseif ($value_type === 'select'): ?>
                                    <?php $options = json_decode($setting['data'], true); ?>
                                    <label class="form-check-label" for="<?= "{$category}-{$key}" ?>" 
                                           <?= $this->Html->templater()->formatAttributes($tooltipAttrs) ?>>
                                        <?= $this->makeHumanReadable($key) ?>
                                    </label>
                                    <?= $this->Form->select("{$category}.{$key}", $options, [
                                        'label' => $this->makeHumanReadable($key),
                                        'value' => $value,
                                        'class' => 'form-control'
                                    ]) ?>
                                <?php elseif ($obscure): ?>
                                    <label for="<?= "{$category}-{$key}" ?>" 
                                           <?= $this->Html->templater()->formatAttributes($tooltipAttrs) ?>>
                                        <?= $this->makeHumanReadable($key) ?>
                                    </label>
                                    <div class="input-group">
                                        <?= $this->Form->text("{$category}.{$key}", [
                                            'value' => str_repeat('•', strlen($value)),
                                            'class' => 'form-control obscured-field obscured',
                                            'id' => "{$category}-{$key}",
                                            'autocomplete' => 'off',
                                            'data-real-value' => $value
                                        ]) ?>
                                        <button class="btn btn-outline-secondary toggle-obscured" type="button" 
                                                data-target="<?= "{$category}-{$key}" ?>">
                                            <?= __('Show') ?>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <?= $this->Form->control("{$category}.{$key}", array_merge([
                                        'label' => $this->makeHumanReadable($key),
                                        'value' => $value,
                                        'class' => 'form-control' . ($value_type === 'numeric' ? ' is-numeric' : ''),
                                        'type' => $value_type === 'numeric' ? 'number' : 'text',
                                        'min' => $value_type === 'numeric' ? 0 : null,
                                        'step' => $value_type === 'numeric' ? 1 : null,
                                        'placeholder' => $value_type === 'numeric' ? __('Enter a number') : __('Enter text')
                                    ], $tooltipAttrs)) ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <?= $this->Form->button(__('Save All Settings'), ['class' => 'btn btn-primary mt-3']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>


            </div>
        </div>
    </div>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
(function() {
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function() {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize all obscured fields
        document.querySelectorAll('.obscured-field').forEach(function(input) {
            input.type = 'password';
            
            input.addEventListener('input', function(e) {
                input.setAttribute('data-real-value', e.target.value);
            });

            input.addEventListener('paste', function(e) {
                setTimeout(() => {
                    input.setAttribute('data-real-value', input.value);
                }, 0);
            });
        });

        // Handle obscured field toggling
        document.body.addEventListener('click', function(event) {
            if (event.target.classList.contains('toggle-obscured')) {
                const targetId = event.target.getAttribute('data-target');
                const input = document.getElementById(targetId);

                if (input.classList.contains('obscured')) {
                    input.type = 'text';
                    input.value = input.getAttribute('data-real-value');
                    input.classList.remove('obscured');
                    event.target.textContent = '<?= __('Hide') ?>';
                } else {
                    input.type = 'password';
                    input.value = input.getAttribute('data-real-value');
                    input.classList.add('obscured');
                    event.target.textContent = '<?= __('Show') ?>';
                }
            }
        });

        // Handle form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                document.querySelectorAll('.obscured-field').forEach(function(input) {
                    if (!input.classList.contains('obscured')) {
                        input.setAttribute('data-real-value', input.value);
                    }
                    input.value = input.getAttribute('data-real-value');
                });
            });
        }
    });
})();
<?php $this->Html->scriptEnd(); ?>