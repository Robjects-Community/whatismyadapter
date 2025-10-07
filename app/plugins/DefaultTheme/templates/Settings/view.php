<?php
/**
 * Settings View Template
 * 
 * Displays a single setting record
 */

$this->assign('title', __('View Setting'));
$this->Html->meta('description', __('View setting details'), ['block' => 'meta']);
?>

<div class="settings-view">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><?= h($setting->key_name) ?></h2>
                        <?php if ($this->Identity->isLoggedIn()): ?>
                            <div class="actions">
                                <?= $this->Html->link(
                                    __('Edit'),
                                    ['action' => 'edit', $setting->id],
                                    ['class' => 'btn btn-warning btn-sm']
                                ) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['action' => 'delete', $setting->id],
                                    [
                                        'class' => 'btn btn-danger btn-sm',
                                        'confirm' => __('Are you sure you want to delete this setting?')
                                    ]
                                ) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4"><?= __('Category') ?></dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-primary"><?= h($setting->category) ?></span>
                            </dd>

                            <dt class="col-sm-4"><?= __('Key Name') ?></dt>
                            <dd class="col-sm-8"><?= h($setting->key_name) ?></dd>

                            <dt class="col-sm-4"><?= __('Value Type') ?></dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-secondary"><?= h($setting->value_type) ?></span>
                            </dd>

                            <dt class="col-sm-4"><?= __('Value') ?></dt>
                            <dd class="col-sm-8">
                                <?php if ($setting->value_obscure): ?>
                                    <span class="text-muted"><em><?= __('[Hidden]') ?></em></span>
                                <?php else: ?>
                                    <div class="bg-light p-2 rounded">
                                        <code><?= h($setting->value) ?></code>
                                    </div>
                                <?php endif; ?>
                            </dd>

                            <?php if (!empty($setting->description)): ?>
                                <dt class="col-sm-4"><?= __('Description') ?></dt>
                                <dd class="col-sm-8"><?= h($setting->description) ?></dd>
                            <?php endif; ?>

                            <dt class="col-sm-4"><?= __('Display Order') ?></dt>
                            <dd class="col-sm-8"><?= $this->Number->format($setting->ordering ?? 0) ?></dd>

                            <dt class="col-sm-4"><?= __('Column Width') ?></dt>
                            <dd class="col-sm-8"><?= $this->Number->format($setting->column_width ?? 6) ?></dd>

                            <?php if (!empty($setting->data)): ?>
                                <dt class="col-sm-4"><?= __('Additional Data') ?></dt>
                                <dd class="col-sm-8">
                                    <div class="bg-light p-2 rounded">
                                        <pre class="mb-0"><code><?= h(json_encode($setting->data, JSON_PRETTY_PRINT)) ?></code></pre>
                                    </div>
                                </dd>
                            <?php endif; ?>

                            <?php if (!empty($setting->created)): ?>
                                <dt class="col-sm-4"><?= __('Created') ?></dt>
                                <dd class="col-sm-8"><?= h($setting->created->format('Y-m-d H:i:s')) ?></dd>
                            <?php endif; ?>

                            <?php if (!empty($setting->modified)): ?>
                                <dt class="col-sm-4"><?= __('Modified') ?></dt>
                                <dd class="col-sm-8"><?= h($setting->modified->format('Y-m-d H:i:s')) ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                    <div class="card-footer">
                        <?= $this->Html->link(
                            '<i class="fas fa-arrow-left"></i> ' . __('Back to List'),
                            ['action' => 'index'],
                            ['class' => 'btn btn-secondary', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
