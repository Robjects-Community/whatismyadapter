<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\ResultSetInterface|\Cake\Collection\CollectionInterface $productFormFields
 */
use Cake\I18n\FrozenTime;

$this->assign('title', __('Product Form Fields'));
?>
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><?= h($this->fetch('title')) ?></h1>
        <div class="btn-group">
            <?= $this->Html->link(__('Reset Order'), ['action' => 'resetOrder'], ['class' => 'btn btn-outline-secondary', 'confirm' => __('Are you sure you want to reset the field order?')]) ?>
            <?= $this->Html->link(__('Add Field'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?= __('ID') ?></th>
                            <th><?= __('Label') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Type') ?></th>
                            <th><?= __('Group') ?></th>
                            <th><?= __('Required') ?></th>
                            <th><?= __('AI') ?></th>
                            <th><?= __('Order') ?></th>
                            <th><?= __('Active') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="text-end"><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productFormFields)) : ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4"><?= __('No fields found. Click "Add Field" to create one.') ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($productFormFields as $field) : ?>
                                <tr>
                                    <td class="text-muted">#<?= (int)$field->id ?></td>
                                    <td><strong><?= h($field->field_label ?? $field->field_name) ?></strong></td>
                                    <td><code><?= h($field->field_name) ?></code></td>
                                    <td><?= h($field->field_type) ?></td>
                                    <td><?= h($field->field_group ?? '-') ?></td>
                                    <td>
                                        <?php if ($field->is_required) : ?>
                                            <span class="badge bg-danger-subtle text-danger"><?= __('Required') ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary-subtle text-secondary"><?= __('Optional') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($field->ai_enabled) : ?>
                                            <span class="badge bg-success-subtle text-success"><?= __('Enabled') ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary-subtle text-secondary"><?= __('Disabled') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted"><?= (int)($field->display_order ?? 0) ?></span>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?= $this->Html->link('↑', ['action' => 'reorder', $field->id, 'up'], ['class' => 'btn btn-outline-secondary', 'escapeTitle' => false, 'title' => __('Move up')]) ?>
                                                <?= $this->Html->link('↓', ['action' => 'reorder', $field->id, 'down'], ['class' => 'btn btn-outline-secondary', 'escapeTitle' => false, 'title' => __('Move down')]) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($field->is_active ?? true) : ?>
                                            <span class="badge bg-success-subtle text-success"><?= __('Active') ?></span>
                                        <?php else : ?>
                                            <span class="badge bg-warning-subtle text-warning"><?= __('Hidden') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap text-muted">
                                        <?php if (!empty($field->modified)) : ?>
                                            <?= h(($field->modified instanceof FrozenTime) ? $field->modified->nice() : (string)$field->modified) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?= $this->Html->link(__('View'), ['action' => 'view', $field->id], ['class' => 'btn btn-outline-secondary']) ?>
                                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $field->id], ['class' => 'btn btn-outline-primary']) ?>
                                            <?= $this->Html->link($field->ai_enabled ? __('Disable AI') : __('Enable AI'), ['action' => 'toggleAi', $field->id], ['class' => 'btn btn-outline-info']) ?>
                                            <?= $this->Html->link(__('Test AI'), ['action' => 'testAi', $field->id], ['class' => 'btn btn-outline-success']) ?>
                                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $field->id], ['class' => 'btn btn-outline-danger', 'confirm' => __('Are you sure you want to delete field "{0}"?', $field->field_label ?? $field->field_name)]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
