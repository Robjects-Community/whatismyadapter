#!/bin/bash
#
# Create Admin Templates Script
# Generates missing admin controller templates for WillowCMS
#

set -e

TEMPLATE_BASE="/Volumes/1TB_DAVINCI/docker/willow/app/plugins/AdminTheme/templates/Admin"

echo "Creating missing admin templates..."

# Create Products templates
mkdir -p "$TEMPLATE_BASE/Products"

# Products index.php
cat > "$TEMPLATE_BASE/Products/index.php" << 'EOF'
<?php
/**
 * Admin Products Index Template
 */
$this->assign('title', __('Products'));
?>

<div class="products-index">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><?= __('Products') ?></h1>
            <div class="actions">
                <?= $this->Html->link(__('Add Product'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link(__('Dashboard'), ['action' => 'dashboard'], ['class' => 'btn btn-info']) ?>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <?= $this->Form->create(null, ['type' => 'get', 'valueSources' => 'query']) ?>
                <div class="row">
                    <div class="col-md-4">
                        <?= $this->Form->control('search', [
                            'label' => __('Search'),
                            'placeholder' => __('Title, manufacturer, model...'),
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $this->Form->control('status', [
                            'label' => __('Status'),
                            'options' => [
                                '' => __('All'),
                                'published' => __('Published'),
                                'unpublished' => __('Unpublished'),
                                'pending' => __('Pending'),
                                'approved' => __('Approved')
                            ],
                            'empty' => __('All Status'),
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $this->Form->control('featured', [
                            'label' => __('Featured'),
                            'type' => 'checkbox',
                            'class' => 'form-check-input'
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $this->Form->button(__('Search'), ['class' => 'btn btn-primary']) ?>
                        <?= $this->Html->link(__('Clear'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-body">
                <?php if (!empty($products)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Title') ?></th>
                                <th><?= __('Manufacturer') ?></th>
                                <th><?= __('Model') ?></th>
                                <th><?= __('Status') ?></th>
                                <th><?= __('Featured') ?></th>
                                <th><?= __('Created') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= h($product->id) ?></td>
                                <td><?= $this->Html->link(h($product->title), ['action' => 'view', $product->id]) ?></td>
                                <td><?= h($product->manufacturer) ?></td>
                                <td><?= h($product->model_number) ?></td>
                                <td>
                                    <span class="badge badge-<?= $product->is_published ? 'success' : 'warning' ?>">
                                        <?= $product->is_published ? __('Published') : __('Unpublished') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product->featured): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($product->created->format('M j, Y')) ?></td>
                                <td class="actions">
                                    <?= $this->Html->link(__('View'), ['action' => 'view', $product->id], ['class' => 'btn btn-sm btn-info']) ?>
                                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $product->id], ['class' => 'btn btn-sm btn-primary']) ?>
                                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $product->id], [
                                        'confirm' => __('Are you sure you want to delete # {0}?', $product->id),
                                        'class' => 'btn btn-sm btn-danger'
                                    ]) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="paginator">
                    <ul class="pagination">
                        <?= $this->Paginator->first('<< ' . __('first')) ?>
                        <?= $this->Paginator->prev('< ' . __('previous')) ?>
                        <?= $this->Paginator->numbers() ?>
                        <?= $this->Paginator->next(__('next') . ' >') ?>
                        <?= $this->Paginator->last(__('last') . ' >>') ?>
                    </ul>
                    <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <?= __('No products found.') ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
EOF

# Products add.php
cat > "$TEMPLATE_BASE/Products/add.php" << 'EOF'
<?php
/**
 * Admin Products Add Template
 */
$this->assign('title', __('Add Product'));
?>

<div class="products-form">
    <div class="container-fluid">
        <div class="page-header mb-4">
            <h1 class="page-title"><?= __('Add Product') ?></h1>
        </div>

        <div class="card">
            <div class="card-body">
                <?= $this->Form->create($product) ?>
                <fieldset>
                    <legend><?= __('Product Information') ?></legend>
                    <?= $this->Form->control('title', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('slug', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('description', ['type' => 'textarea', 'class' => 'form-control']) ?>
                    <?= $this->Form->control('manufacturer', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('model_number', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('price', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('currency', ['class' => 'form-control', 'default' => 'USD']) ?>
                    <?= $this->Form->control('image', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('alt_text', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('is_published', ['type' => 'checkbox']) ?>
                    <?= $this->Form->control('featured', ['type' => 'checkbox']) ?>
                    <?= $this->Form->control('tags._ids', ['options' => $tags, 'class' => 'form-control', 'multiple' => true]) ?>
                </fieldset>
                <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
EOF

# Products edit.php  
cat > "$TEMPLATE_BASE/Products/edit.php" << 'EOF'
<?php
/**
 * Admin Products Edit Template
 */
$this->assign('title', __('Edit Product'));
?>

<div class="products-form">
    <div class="container-fluid">
        <div class="page-header mb-4">
            <h1 class="page-title"><?= __('Edit Product: {0}', h($product->title)) ?></h1>
        </div>

        <div class="card">
            <div class="card-body">
                <?= $this->Form->create($product) ?>
                <fieldset>
                    <legend><?= __('Product Information') ?></legend>
                    <?= $this->Form->control('title', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('slug', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('description', ['type' => 'textarea', 'class' => 'form-control']) ?>
                    <?= $this->Form->control('manufacturer', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('model_number', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('price', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('currency', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('image', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('alt_text', ['class' => 'form-control']) ?>
                    <?= $this->Form->control('is_published', ['type' => 'checkbox']) ?>
                    <?= $this->Form->control('featured', ['type' => 'checkbox']) ?>
                    <?= $this->Form->control('tags._ids', ['options' => $tags, 'class' => 'form-control', 'multiple' => true]) ?>
                </fieldset>
                <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
                <?= $this->Form->postLink(
                    __('Delete'),
                    ['action' => 'delete', $product->id],
                    ['confirm' => __('Are you sure?'), 'class' => 'btn btn-danger']
                ) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
EOF

# Products view.php
cat > "$TEMPLATE_BASE/Products/view.php" << 'EOF'
<?php
/**
 * Admin Products View Template
 */
$this->assign('title', __('Product Details'));
?>

<div class="products-view">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><?= h($product->title) ?></h1>
            <div class="actions">
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $product->id], ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link(__('Back to List'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?= __('Product Information') ?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <th><?= __('Title') ?></th>
                                <td><?= h($product->title) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Manufacturer') ?></th>
                                <td><?= h($product->manufacturer) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Model Number') ?></th>
                                <td><?= h($product->model_number) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Price') ?></th>
                                <td><?= $this->Number->currency($product->price, $product->currency) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Status') ?></th>
                                <td>
                                    <span class="badge badge-<?= $product->is_published ? 'success' : 'warning' ?>">
                                        <?= $product->is_published ? __('Published') : __('Unpublished') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><?= __('Featured') ?></th>
                                <td><?= $product->featured ? __('Yes') : __('No') ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Created') ?></th>
                                <td><?= h($product->created->format('M j, Y g:i A')) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Modified') ?></th>
                                <td><?= h($product->modified->format('M j, Y g:i A')) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5><?= __('Description') ?></h5>
                    </div>
                    <div class="card-body">
                        <?= $this->Text->autoParagraph(h($product->description)) ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <?php if (!empty($product->image)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?= __('Product Image') ?></h5>
                    </div>
                    <div class="card-body">
                        <?= $this->Html->image($product->image, ['class' => 'img-fluid', 'alt' => h($product->alt_text)]) ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($product->tags)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5><?= __('Tags') ?></h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($product->tags as $tag): ?>
                            <span class="badge badge-secondary"><?= h($tag->title) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
EOF

echo "✓ Products templates created"

# Create additional controller template directories
for controller in Articles Tags Users Pages ImageGalleries Comments Videos; do
    mkdir -p "$TEMPLATE_BASE/$controller"
    echo "✓ Created directory: $TEMPLATE_BASE/$controller"
done

echo ""
echo "All template directories created successfully!"
echo "Note: Individual templates for Articles, Tags, Users, etc. should be created based on their specific controller needs."
EOF

chmod +x "$TEMPLATE_BASE/../../tools/create_admin_templates.sh"
