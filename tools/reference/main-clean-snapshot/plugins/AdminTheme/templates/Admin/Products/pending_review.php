<?php
$this->assign('title', __('Products - Pending Review'));
$this->Html->css('willow-admin', ['block' => true]);
?>

<?= $this->element('nav/products_tabs') ?>

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('Products - Pending Review') ?></h3>
            <div class="actions">
                <?= $this->Html->link(
                    '<i class="fas fa-plus"></i> ' . __('New Product'),
                    ['action' => 'add'],
                    ['class' => 'btn btn-success', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-chart-line"></i> ' . __('Dashboard'),
                    ['action' => 'dashboard'],
                    ['class' => 'btn btn-info', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-arrow-left"></i> ' . __('Back to All Products'),
                    ['action' => 'index'],
                    ['class' => 'btn btn-secondary', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
</div>

<!-- Filter and Search Bar -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <?= $this->Form->create(null, ['type' => 'get', 'class' => 'form-inline']) ?>
                
                <!-- Search -->
                <div class="form-group mr-3">
                    <?= $this->Form->control('search', [
                        'type' => 'text',
                        'placeholder' => __('Search products...'),
                        'value' => $this->request->getQuery('search'),
                        'class' => 'form-control',
                        'label' => false
                    ]) ?>
                </div>

                <!-- Manufacturer Filter -->
                <div class="form-group mr-3">
                    <?= $this->Form->control('manufacturer', [
                        'type' => 'text',
                        'placeholder' => __('Manufacturer'),
                        'value' => $this->request->getQuery('manufacturer'),
                        'class' => 'form-control',
                        'label' => false
                    ]) ?>
                </div>

                <!-- Featured Filter -->
                <div class="form-group mr-3">
                    <?= $this->Form->control('featured', [
                        'type' => 'checkbox',
                        'label' => __('Featured Only'),
                        'checked' => $this->request->getQuery('featured')
                    ]) ?>
                </div>

                <!-- User Selector -->
                <div class="form-group mr-3">
                    <?= $this->Form->control('user_id', [
                        'type' => 'select',
                        'options' => isset($users) ? $users : [],
                        'empty' => __('All Users'),
                        'value' => $this->request->getQuery('user_id'),
                        'class' => 'form-control',
                        'label' => false
                    ]) ?>
                </div>

                <!-- Date From -->
                <div class="form-group mr-3">
                    <?= $this->Form->control('date_from', [
                        'type' => 'date',
                        'value' => $this->request->getQuery('date_from'),
                        'class' => 'form-control',
                        'label' => false,
                        'placeholder' => __('From Date')
                    ]) ?>
                </div>

                <!-- Date To -->
                <div class="form-group mr-3">
                    <?= $this->Form->control('date_to', [
                        'type' => 'date',
                        'value' => $this->request->getQuery('date_to'),
                        'class' => 'form-control',
                        'label' => false,
                        'placeholder' => __('To Date')
                    ]) ?>
                </div>

                <!-- Submit -->
                <div class="form-group">
                    <?= $this->Form->button(__('Filter'), ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link(__('Clear'), ['action' => 'pending_review'], ['class' => 'btn btn-secondary ml-2']) ?>
                </div>

                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<!-- Products Table with Bulk Actions -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($products)): ?>
                    <!-- Bulk Actions Form -->
                    <?= $this->Form->create(null, [
                        'type' => 'post',
                        'id' => 'bulk-actions-form'
                    ]) ?>
                    
                    <!-- Bulk Actions Bar -->
                    <div class="bulk-actions-bar mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                    <label class="form-check-label" for="select-all">
                                        <?= __('Select All') ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group" role="group">
                                    <?= $this->Form->button(__('Verify Selected'), [
                                        'class' => 'btn btn-warning',
                                        'formaction' => $this->Url->build(['action' => 'bulk-verify']),
                                        'onclick' => 'return confirmBulkAction("verify");'
                                    ]) ?>
                                    <?= $this->Form->button(__('Approve Selected'), [
                                        'class' => 'btn btn-success',
                                        'formaction' => $this->Url->build(['action' => 'bulk-approve']),
                                        'onclick' => 'return confirmBulkAction("approve");'
                                    ]) ?>
                                    <?= $this->Form->button(__('Reject Selected'), [
                                        'class' => 'btn btn-danger',
                                        'formaction' => $this->Url->build(['action' => 'bulk-reject']),
                                        'onclick' => 'return confirmBulkAction("reject");'
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="select-all-header" onclick="toggleAllCheckboxes(this)">
                                    </th>
                                    <th><?= $this->Paginator->sort('title', __('Title')) ?></th>
                                    <th><?= $this->Paginator->sort('manufacturer', __('Manufacturer')) ?></th>
                                    <th><?= $this->Paginator->sort('user_id', __('User')) ?></th>
                                    <th><?= $this->Paginator->sort('reliability_score', __('Reliability Score')) ?></th>
                                    <th><?= $this->Paginator->sort('created', __('Created')) ?></th>
                                    <th class="actions"><?= __('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?= $this->Form->checkbox('ids[]', [
                                            'value' => $product->id,
                                            'class' => 'product-checkbox'
                                        ]) ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($product->image): ?>
                                                <img src="<?= h($product->image) ?>" alt="<?= h($product->alt_text) ?>" 
                                                     class="img-thumbnail mr-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= h($product->title) ?></strong>
                                                <?php if ($product->featured): ?>
                                                    <span class="badge badge-warning ml-1"><?= __('Featured') ?></span>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted"><?= h($product->model_number) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= h($product->manufacturer) ?></td>
                                    <td>
                                        <?php if ($product->user): ?>
                                            <span class="badge badge-secondary"><?= h($product->user->username) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product->reliability_score && $product->reliability_score > 0): ?>
                                            <span class="badge badge-info">
                                                <?= number_format($product->reliability_score, 1) ?>/5.0
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $product->created->format('M j, Y') ?><br>
                                        <small class="text-muted"><?= $product->created->format('g:i A') ?></small>
                                    </td>
                                    <td class="actions">
                                        <div class="btn-group" role="group">
                                            <?= $this->Html->link(
                                                '<i class="fas fa-eye"></i>',
                                                ['action' => 'view', $product->id],
                                                ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => __('View')]
                                            ) ?>
                                            <?= $this->Html->link(
                                                '<i class="fas fa-edit"></i>',
                                                ['action' => 'edit', $product->id],
                                                ['class' => 'btn btn-sm btn-outline-secondary', 'escape' => false, 'title' => __('Edit')]
                                            ) ?>
                                            
                                            <!-- Verify -->
                                            <?= $this->Form->postLink(
                                                '<i class="fas fa-check-circle"></i>',
                                                ['action' => 'verify', $product->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-warning',
                                                    'escape' => false,
                                                    'title' => __('Verify'),
                                                    'confirm' => __('Are you sure you want to verify this product?')
                                                ]
                                            ) ?>
                                            
                                            <!-- Approve -->
                                            <?= $this->Form->postLink(
                                                '<i class="fas fa-thumbs-up"></i>',
                                                ['action' => 'approve', $product->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-success',
                                                    'escape' => false,
                                                    'title' => __('Approve'),
                                                    'confirm' => __('Are you sure you want to approve this product?')
                                                ]
                                            ) ?>
                                            
                                            <!-- Reject -->
                                            <?= $this->Form->postLink(
                                                '<i class="fas fa-thumbs-down"></i>',
                                                ['action' => 'reject', $product->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-danger',
                                                    'escape' => false,
                                                    'title' => __('Reject'),
                                                    'confirm' => __('Are you sure you want to reject this product?')
                                                ]
                                            ) ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?= $this->Form->end() ?>

                    <!-- Pagination -->
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?= $this->Paginator->first('<< ' . __('first')) ?>
                            <?= $this->Paginator->prev('< ' . __('previous')) ?>
                            <?= $this->Paginator->numbers() ?>
                            <?= $this->Paginator->next(__('next') . ' >') ?>
                            <?= $this->Paginator->last(__('last') . ' >>') ?>
                        </ul>
                    </nav>

                    <p class="text-muted">
                        <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
                    </p>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p><?= __('No products pending review found.') ?></p>
                        <?= $this->Html->link(
                            __('View all products'),
                            ['action' => 'index'],
                            ['class' => 'btn btn-primary']
                        ) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript for bulk actions and checkbox management
function toggleAllCheckboxes(source) {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = source.checked;
    });
}

// Handle the main select-all checkbox
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const selectAllHeaderCheckbox = document.getElementById('select-all-header');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            toggleAllCheckboxes(this);
            if (selectAllHeaderCheckbox) {
                selectAllHeaderCheckbox.checked = this.checked;
            }
        });
    }
    
    if (selectAllHeaderCheckbox) {
        selectAllHeaderCheckbox.addEventListener('change', function() {
            toggleAllCheckboxes(this);
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = this.checked;
            }
        });
    }
    
    // Update select-all checkboxes when individual checkboxes are changed
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    productCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(productCheckboxes).every(cb => cb.checked);
            const anyChecked = Array.from(productCheckboxes).some(cb => cb.checked);
            
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && anyChecked;
            }
            if (selectAllHeaderCheckbox) {
                selectAllHeaderCheckbox.checked = allChecked;
                selectAllHeaderCheckbox.indeterminate = !allChecked && anyChecked;
            }
        });
    });
});

function confirmBulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('<?= __('Please select at least one product.') ?>');
        return false;
    }
    
    const actionText = {
        'verify': '<?= __('verify') ?>',
        'approve': '<?= __('approve') ?>',
        'reject': '<?= __('reject') ?>'
    };
    
    return confirm('<?= __('Are you sure you want to') ?> ' + actionText[action] + ' ' + checkedBoxes.length + ' <?= __('selected product(s)?') ?>');
}
</script>
