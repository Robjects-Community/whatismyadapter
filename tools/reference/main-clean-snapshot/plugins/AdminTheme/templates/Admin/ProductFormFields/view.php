<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ProductFormField $productFormField
 */
$this->assign('title', __('View Product Form Field'));
?>
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><?php echo h($this->fetch('title')) ?></h1>
        <div class="btn-group">
            <?= $this->Html->link(__('Back to list'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $productFormField->id], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-2 text-muted"><?= __('Label') ?></div>
                    <div class="fs-5 fw-semibold"><?= h($productFormField->field_label) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2 text-muted"><?= __('Machine Name') ?></div>
                    <div class="fs-5"><code><?= h($productFormField->field_name) ?></code></div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2 text-muted"><?= __('Type') ?></div>
                    <div><?= h($productFormField->field_type) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2 text-muted"><?= __('Group') ?></div>
                    <div><?= h($productFormField->field_group ?? '-') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2 text-muted"><?= __('Order') ?></div>
                    <div><?= (int)($productFormField->display_order ?? 0) ?></div>
                </div>

                <div class="col-12">
                    <div class="mb-2 text-muted"><?= __('Help Text') ?></div>
                    <div><?= nl2br(h($productFormField->field_help_text ?? '')) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2 text-muted"><?= __('Placeholder') ?></div>
                    <div><?= h($productFormField->field_placeholder ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <div class="mb-2 text-muted"><?= __('Default Value') ?></div>
                    <div><?= h($productFormField->default_value ?? '') ?></div>
                </div>

                <div class="col-md-6">
                    <div class="mb-2 text-muted"><?= __('Options JSON') ?></div>
                    <pre class="bg-light p-3 rounded small"><?= h($productFormField->field_options ?? '{}') ?></pre>
                </div>
                <div class="col-md-6">
                    <div class="mb-2 text-muted"><?= __('Validation Rules JSON') ?></div>
                    <pre class="bg-light p-3 rounded small"><?= h($productFormField->field_validation ?? '{}') ?></pre>
                </div>

                <div class="col-md-3">
                    <div class="mb-2 text-muted"><?= __('Required') ?></div>
                    <div><?= $productFormField->is_required ? __('Yes') : __('No') ?></div>
                </div>
                <div class="col-md-3">
                    <div class="mb-2 text-muted"><?= __('Active') ?></div>
                    <div><?= ($productFormField->is_active ?? false) ? __('Yes') : __('No') ?></div>
                </div>
                <div class="col-md-3">
                    <div class="mb-2 text-muted"><?= __('AI Enabled') ?></div>
                    <div><?= ($productFormField->ai_enabled ?? false) ? __('Yes') : __('No') ?></div>
                </div>
                <div class="col-md-3">
                    <div class="mb-2 text-muted"><?= __('Column Width') ?></div>
                    <div><?= ($productFormField->column_width ?? 12) ?></div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <?= $this->Html->link(__('Test AI'), ['action' => 'testAi', $productFormField->id], ['class' => 'btn btn-outline-success']) ?>
            <?= $this->Html->link(__('Toggle AI'), ['action' => 'toggleAi', $productFormField->id], ['class' => 'btn btn-outline-info']) ?>
        </div>
    </div>
</div>
