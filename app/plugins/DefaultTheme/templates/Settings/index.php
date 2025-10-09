<?php
/**
 * Settings Index Template
 * 
 * Displays a listing of all settings with pagination
 */

$this->assign('title', __('Settings'));
$this->Html->meta('description', __('View and manage application settings'), ['block' => 'meta']);
?>

<div class="settings-index">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title"><?= __('Settings') ?></h1>
                    <?php if ($this->Identity->isLoggedIn()): ?>
                        <div class="actions">
                            <?= $this->Html->link(
                                __('Add Setting'),
                                ['action' => 'add'],
                                ['class' => 'btn btn-primary']
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($settings)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?= $this->Paginator->sort('category', __('Category')) ?></th>
                                    <th><?= $this->Paginator->sort('key_name', __('Key')) ?></th>
                                    <th><?= __('Value') ?></th>
                                    <th><?= $this->Paginator->sort('value_type', __('Type')) ?></th>
                                    <th><?= __('Description') ?></th>
                                    <th class="actions"><?= __('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($settings as $setting): ?>
                                    <tr>
                                        <td><?= h($setting->category) ?></td>
                                        <td><?= h($setting->key_name) ?></td>
                                        <td>
                                            <?php if ($setting->value_obscure): ?>
                                                <span class="text-muted"><em><?= __('Hidden') ?></em></span>
                                            <?php else: ?>
                                                <?= h($this->Text->truncate($setting->value, 50)) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= h($setting->value_type) ?></span></td>
                                        <td><?= h($this->Text->truncate($setting->description ?? '', 60)) ?></td>
                                        <td class="actions">
                                            <?= $this->Html->link(
                                                __('View'),
                                                ['action' => 'view', $setting->id],
                                                ['class' => 'btn btn-sm btn-info']
                                            ) ?>
                                            <?php if ($this->Identity->isLoggedIn()): ?>
                                                <?= $this->Html->link(
                                                    __('Edit'),
                                                    ['action' => 'edit', $setting->id],
                                                    ['class' => 'btn btn-sm btn-warning']
                                                ) ?>
                                                <?= $this->Form->postLink(
                                                    __('Delete'),
                                                    ['action' => 'delete', $setting->id],
                                                    [
                                                        'class' => 'btn btn-sm btn-danger',
                                                        'confirm' => __('Are you sure you want to delete # {0}?', $setting->id)
                                                    ]
                                                ) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-wrapper mt-4">
                        <?= $this->element('pagination') ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <?= __('No settings found.') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
