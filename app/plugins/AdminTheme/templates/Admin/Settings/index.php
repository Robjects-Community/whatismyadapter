<?php
/**
 * Admin Settings Index Template
 * 
 * Displays all application settings grouped by category
 */

$this->assign('title', __('Settings'));
?>

<div class="settings-index">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title"><?= __('Application Settings') ?></h1>
                    <div class="actions">
                        <?= $this->Html->link(
                            __('Save All Settings'),
                            'javascript:void(0)',
                            ['class' => 'btn btn-primary', 'onclick' => 'document.getElementById(\'settings-form\').submit()']
                        ) ?>
                    </div>
                </div>

                <?= $this->Form->create(null, [
                    'id' => 'settings-form',
                    'url' => ['action' => 'saveSettings']
                ]) ?>

                <?php if (!empty($groupedSettings)): ?>
                    <?php foreach ($groupedSettings as $category => $settings): ?>
                        <div class="settings-category card mb-4">
                            <div class="card-header">
                                <h3 class="mb-0"><?= h(ucfirst($category)) ?></h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($settings as $key => $setting): ?>
                                        <div class="col-md-<?= $setting['column_width'] ?? 6 ?> mb-3">
                                            <?php
                                            $inputOptions = [
                                                'label' => __(Cake\Utility\Inflector::humanize($key)),
                                                'class' => 'form-control',
                                                'value' => $setting['value']
                                            ];
                                            
                                            if (!empty($setting['description'])) {
                                                $inputOptions['help'] = $setting['description'];
                                            }

                                            // Handle different value types
                                            switch ($setting['value_type']) {
                                                case 'boolean':
                                                    $inputOptions['type'] = 'checkbox';
                                                    $inputOptions['checked'] = (bool)$setting['value'];
                                                    unset($inputOptions['value']);
                                                    break;
                                                case 'textarea':
                                                    $inputOptions['type'] = 'textarea';
                                                    $inputOptions['rows'] = 4;
                                                    break;
                                                case 'select':
                                                    if (!empty($setting['data']) && is_array($setting['data'])) {
                                                        $inputOptions['type'] = 'select';
                                                        $inputOptions['options'] = $setting['data'];
                                                    }
                                                    break;
                                                case 'password':
                                                    $inputOptions['type'] = 'password';
                                                    if ($setting['value_obscure'] ?? false) {
                                                        $inputOptions['value'] = '**********';
                                                    }
                                                    break;
                                                default:
                                                    $inputOptions['type'] = 'text';
                                            }
                                            
                                            echo $this->Form->control("$category.$key", $inputOptions);
                                            ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="form-actions">
                        <?= $this->Form->submit(__('Save All Settings'), ['class' => 'btn btn-primary btn-lg']) ?>
                        <?= $this->Html->link(__('Cancel'), ['controller' => 'Dashboard', 'action' => 'index'], ['class' => 'btn btn-secondary']) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <?= __('No settings configured yet.') ?>
                    </div>
                <?php endif; ?>

                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
