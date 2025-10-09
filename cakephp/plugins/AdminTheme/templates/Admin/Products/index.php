<?php
$this->assign('title', __('Products'));
$this->Html->css('willow-admin', ['block' => true]);
?>

<?= $this->element('nav/products_tabs') ?>

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('Products') ?></h3>
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
                    '<i class="fas fa-clock"></i> ' . __('Pending Review'),
                    ['action' => 'pendingReview'],
                    ['class' => 'btn btn-warning', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
</div>

<style>
.product-row.is-selected {
    background-color: #e3f2fd !important;
    border-left: 3px solid #2196f3;
}

.bulk-actions-bar {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
}

.dependent-field:disabled {
    opacity: 0.5;
}

.field-toggle:checked + label {
    color: #0d6efd;
    font-weight: 600;
}
</style>

<script>
// JavaScript for bulk actions and checkbox management
let selectedProducts = new Set();

function updateSelectedCount() {
    document.getElementById('selected-count').textContent = selectedProducts.size;
    document.getElementById('modal-selected-count').textContent = selectedProducts.size;
    
    // Enable/disable bulk action button
    const applyButton = document.getElementById('apply-bulk-action');
    const bulkSelect = document.getElementById('bulk-action-select');
    applyButton.disabled = selectedProducts.size === 0 || !bulkSelect.value;
}

function toggleAllCheckboxes(source) {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = source.checked;
        const row = checkbox.closest('.product-row');
        const productId = row.dataset.productId;
        
        if (checkbox.checked) {
            selectedProducts.add(productId);
            row.classList.add('is-selected');
        } else {
            selectedProducts.delete(productId);
            row.classList.remove('is-selected');
        }
    });
    updateSelectedCount();
}

function handleProductCheckboxChange(checkbox) {
    const row = checkbox.closest('.product-row');
    const productId = row.dataset.productId;
    
    if (checkbox.checked) {
        selectedProducts.add(productId);
        row.classList.add('is-selected');
    } else {
        selectedProducts.delete(productId);
        row.classList.remove('is-selected');
    }
    
    // Update main select-all checkboxes
    const allCheckboxes = document.querySelectorAll('.product-checkbox');
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const selectAllMain = document.getElementById('select-all');
    const selectAllHeader = document.getElementById('select-all-header');
    
    if (checkedBoxes.length === allCheckboxes.length) {
        selectAllMain.checked = true;
        selectAllHeader.checked = true;
        selectAllMain.indeterminate = false;
        selectAllHeader.indeterminate = false;
    } else if (checkedBoxes.length === 0) {
        selectAllMain.checked = false;
        selectAllHeader.checked = false;
        selectAllMain.indeterminate = false;
        selectAllHeader.indeterminate = false;
    } else {
        selectAllMain.indeterminate = true;
        selectAllHeader.indeterminate = true;
    }
    
    updateSelectedCount();
}

function confirmBulkAction(action, count) {
    const actionMessages = {
        'verify': 'queue verification for',
        'approve': 'approve',
        'reject': 'reject', 
        'publish': 'publish',
        'unpublish': 'unpublish',
        'feature': 'feature',
        'unfeature': 'unfeature',
        'delete': 'permanently delete'
    };
    
    const message = actionMessages[action] || action;
    return confirm(`Are you sure you want to ${message} ${count} selected product(s)?`);
}

function updateMassEditSelectedIds() {
    const container = document.getElementById('mass-edit-selected-ids');
    container.innerHTML = '';
    
    selectedProducts.forEach(function(productId) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected[]';
        input.value = productId;
        container.appendChild(input);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Handle select-all checkboxes
    const selectAllMain = document.getElementById('select-all');
    const selectAllHeader = document.getElementById('select-all-header');
    
    if (selectAllMain) {
        selectAllMain.addEventListener('change', function() {
            toggleAllCheckboxes(this);
            if (selectAllHeader) selectAllHeader.checked = this.checked;
        });
    }
    
    if (selectAllHeader) {
        selectAllHeader.addEventListener('change', function() {
            toggleAllCheckboxes(this);
            if (selectAllMain) selectAllMain.checked = this.checked;
        });
    }
    
    // Handle individual product checkboxes
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    productCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            handleProductCheckboxChange(this);
        });
    });
    
    // Handle bulk action button
    const applyButton = document.getElementById('apply-bulk-action');
    const bulkSelect = document.getElementById('bulk-action-select');
    
    if (applyButton) {
        applyButton.addEventListener('click', function() {
            const action = bulkSelect.value;
            const count = selectedProducts.size;
            
            if (count === 0) {
                alert('Please select at least one product.');
                return;
            }
            
            if (action === 'mass-edit') {
                updateMassEditSelectedIds();
                const modal = new bootstrap.Modal(document.getElementById('massEditModal'));
                modal.show();
                return;
            }
            
            if (['delete', 'unpublish'].includes(action) && !confirmBulkAction(action, count)) {
                return;
            }
            
            // Create form and submit
            const form = document.getElementById('bulk-actions-form');
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'bulk_action';
            actionInput.value = action;
            form.appendChild(actionInput);
            
            // Ensure all selected IDs are included
            form.querySelectorAll('input[name="selected[]"]').forEach(input => {
                input.checked = selectedProducts.has(input.value);
            });
            
            form.submit();
        });
    }
    
    // Handle bulk select change to enable/disable apply button
    if (bulkSelect) {
        bulkSelect.addEventListener('change', function() {
            updateSelectedCount();
        });
    }
    
    // Handle field toggles in mass edit modal
    const fieldToggles = document.querySelectorAll('.field-toggle');
    fieldToggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const fieldName = this.dataset.field;
            const dependentField = document.querySelector(`[data-depends="${this.id}"]`);
            
            if (dependentField) {
                dependentField.disabled = !this.checked;
                if (this.checked) {
                    dependentField.focus();
                }
            }
        });
    });
});
</script>

<!-- Filter and Search Bar -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <?= $this->Form->create(null, ['type' => 'get', 'class' => 'form-inline']) ?>
                
                <!-- Status Filter -->
                <div class="form-group mr-3">
                    <?= $this->Form->control('status', [
                        'type' => 'select',
                        'options' => [
                            '' => __('All Status'),
                            'published' => __('Published'),
                            'unpublished' => __('Unpublished'),
                            'pending' => __('Pending Verification'),
                            'approved' => __('Approved'),
                            'rejected' => __('Rejected')
                        ],
                        'value' => $this->request->getQuery('status'),
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

                <!-- Submit -->
                <div class="form-group">
                    <?= $this->Form->button(__('Filter'), ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link(__('Clear'), ['action' => 'index'], ['class' => 'btn btn-secondary ml-2']) ?>
                </div>

                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($products)): ?>
                    <!-- Bulk Actions Form -->
                    <?= $this->Form->create(null, [
                        'type' => 'post',
                        'id' => 'bulk-actions-form',
                        'url' => ['action' => 'bulkEdit']
                    ]) ?>
                    <?= $this->Form->hidden('returnUrl', ['value' => $this->request->getRequestTarget()]) ?>
                    
                    <!-- Bulk Actions Bar -->
                    <div class="bulk-actions-bar mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                    <label class="form-check-label" for="select-all">
                                        <?= __('Select All') ?> (<span id="selected-count">0</span> <?= __('selected') ?>)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <select name="bulk_action" class="form-control" id="bulk-action-select">
                                        <option value=""><?= __('Bulk Actions...') ?></option>
                                        <optgroup label="<?= __('Status') ?>">
                                            <option value="verify"><?= __('Queue Verification') ?></option>
                                            <option value="approve"><?= __('Approve Selected') ?></option>
                                            <option value="reject"><?= __('Reject Selected') ?></option>
                                        </optgroup>
                                        <optgroup label="<?= __('Publishing') ?>">
                                            <option value="publish"><?= __('Publish Selected') ?></option>
                                            <option value="unpublish"><?= __('Unpublish Selected') ?></option>
                                        </optgroup>
                                        <optgroup label="<?= __('Featuring') ?>">
                                            <option value="feature"><?= __('Feature Selected') ?></option>
                                            <option value="unfeature"><?= __('Unfeature Selected') ?></option>
                                        </optgroup>
                                        <optgroup label="<?= __('Advanced') ?>">
                                            <option value="mass-edit"><?= __('Mass Edit (Advanced)') ?></option>
                                            <option value="delete"><?= __('Delete Selected') ?></option>
                                        </optgroup>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" id="apply-bulk-action" class="btn btn-primary" disabled>
                                            <?= __('Apply') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="select-all-header" class="form-check-input" onclick="toggleAllCheckboxes(this)">
                                    </th>
                                    <th><?= $this->Paginator->sort('title', __('Title')) ?></th>
                                    <th><?= $this->Paginator->sort('manufacturer', __('Manufacturer')) ?></th>
                                    <th><?= $this->Paginator->sort('capability_name', __('Capability')) ?></th>
                                    <th><?= $this->Paginator->sort('port_family', __('Port Type')) ?></th>
                                    <th><?= $this->Paginator->sort('form_factor', __('Form Factor')) ?></th>
                                    <th><?= $this->Paginator->sort('connector_gender', __('Gender')) ?></th>
                                    <th><?= $this->Paginator->sort('device_category', __('Device Cat')) ?></th>
                                    <th><?= $this->Paginator->sort('compatibility_level', __('Compatibility')) ?></th>
                                    <th><?= $this->Paginator->sort('price', __('Price')) ?></th>
                                    <th><?= $this->Paginator->sort('numeric_rating', __('Rating')) ?></th>
                                    <th><?= $this->Paginator->sort('is_certified', __('Certified')) ?></th>
                                    <th><?= __('Status') ?></th>
                                    <th><?= $this->Paginator->sort('reliability_score', __('Rel. Score')) ?></th>
                                    <th><?= $this->Paginator->sort('view_count', __('Views')) ?></th>
                                    <th><?= $this->Paginator->sort('created', __('Created')) ?></th>
                                    <th class="actions"><?= __('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr class="product-row" data-product-id="<?= h($product->id) ?>">
                                    <!-- Selection checkbox -->
                                    <td>
                                        <?= $this->Form->checkbox('selected[]', [
                                            'value' => $product->id,
                                            'class' => 'product-checkbox form-check-input',
                                            'aria-label' => __('Select product: {0}', $product->title)
                                        ]) ?>
                                    </td>
                                    <!-- Title with image and model -->
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
                                    
                                    <!-- Manufacturer -->
                                    <td><?= h($product->manufacturer) ?></td>
                                    
                                    <!-- Capability Name -->
                                    <td>
                                        <span class="badge badge-secondary"><?= h($product->capability_name) ?: '-' ?></span>
                                        <?php if ($product->capability_category): ?>
                                            <br><small class="text-muted"><?= h($product->capability_category) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Port Type (port_family) -->
                                    <td>
                                        <span class="badge badge-primary"><?= h($product->port_family) ?: '-' ?></span>
                                        <?php if ($product->port_type_name): ?>
                                            <br><small class="text-muted"><?= h($product->port_type_name) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Form Factor -->
                                    <td><?= h($product->form_factor) ?: '<span class="text-muted">-</span>' ?></td>
                                    
                                    <!-- Connector Gender -->
                                    <td>
                                        <?php if ($product->connector_gender): ?>
                                            <span class="badge badge-<?= $product->connector_gender == 'Male' ? 'info' : ($product->connector_gender == 'Female' ? 'warning' : 'secondary') ?>">
                                                <?= h($product->connector_gender) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Device Category -->
                                    <td>
                                        <?= h($product->device_category) ?: '<span class="text-muted">-</span>' ?>
                                        <?php if ($product->device_brand): ?>
                                            <br><small class="text-muted"><?= h($product->device_brand) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Compatibility Level -->
                                    <td>
                                        <?php if ($product->compatibility_level): ?>
                                            <?php 
                                            $compatClass = [
                                                'Full' => 'success',
                                                'Partial' => 'warning', 
                                                'Limited' => 'info',
                                                'Incompatible' => 'danger'
                                            ][$product->compatibility_level] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?= $compatClass ?>"><?= h($product->compatibility_level) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Price -->
                                    <td>
                                        <?php if ($product->price): ?>
                                            <?= number_format($product->price, 2) ?> <?= h($product->currency) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Numeric Rating -->
                                    <td>
                                        <?php if ($product->numeric_rating): ?>
                                            <span class="badge badge-info"><?= number_format($product->numeric_rating, 1) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Is Certified -->
                                    <td>
                                        <?php if ($product->is_certified): ?>
                                            <i class="fas fa-check-circle text-success" title="Certified"></i>
                                            <?php if ($product->certification_date): ?>
                                                <br><small class="text-muted"><?= $product->certification_date->format('M Y') ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle text-muted" title="Not Certified"></i>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Status -->
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'approved' => 'success', 
                                            'rejected' => 'danger'
                                        ][$product->verification_status] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>">
                                            <?= __(ucfirst($product->verification_status)) ?>
                                        </span>
                                        <?php if ($product->is_published): ?>
                                            <span class="badge badge-success ml-1"><?= __('Published') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Reliability Score -->
                                    <td>
                                        <?php if ($product->reliability_score > 0): ?>
                                            <?= $this->Html->link(
                                                '<span class="badge badge-info">' . number_format($product->reliability_score, 1) . '/5.0</span>',
                                                ['controller' => 'Reliability', 'action' => 'view', 'model' => 'Products', 'id' => $product->id],
                                                [
                                                    'escape' => false,
                                                    'title' => __('View detailed reliability breakdown')
                                                ]
                                            ) ?>
                                        <?php else: ?>
                                            <?= $this->Html->link(
                                                '<span class="text-muted">Calculate</span>',
                                                ['controller' => 'Reliability', 'action' => 'view', 'model' => 'Products', 'id' => $product->id],
                                                [
                                                    'escape' => false,
                                                    'title' => __('View reliability details and calculate score')
                                                ]
                                            ) ?>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Views -->
                                    <td><?= number_format($product->view_count) ?></td>
                                    
                                    <!-- Created -->
                                    <td>
                                        <?= $product->created->format('M j, Y') ?><br>
                                        <small class="text-muted">by <?= h($product->user ? $product->user->username : 'Unknown') ?></small>
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
                                            
                                            <!-- Toggle Featured -->
                                            <?= $this->Form->postLink(
                                                $product->featured ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star"></i>',
                                                ['action' => 'toggleFeatured', $product->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-warning',
                                                    'escape' => false,
                                                    'title' => $product->featured ? __('Remove from Featured') : __('Make Featured'),
                                                    'confirm' => __('Are you sure?')
                                                ]
                                            ) ?>
                                            
                                            <!-- Toggle Published -->
                                            <?= $this->Form->postLink(
                                                $product->is_published ? '<i class="fas fa-toggle-on text-success"></i>' : '<i class="fas fa-toggle-off text-secondary"></i>',
                                                ['action' => 'togglePublished', $product->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-info',
                                                    'escape' => false,
                                                    'title' => $product->is_published ? __('Unpublish') : __('Publish'),
                                                    'confirm' => __('Are you sure?')
                                                ]
                                            ) ?>
                                            
                                            <!-- Delete -->
                                            <?= $this->Form->postLink(
                                                '<i class="fas fa-trash"></i>',
                                                ['action' => 'delete', $product->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-danger',
                                                    'escape' => false,
                                                    'title' => __('Delete'),
                                                    'confirm' => __('Are you sure you want to delete {0}?', $product->title)
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
                    
                    <!-- Mass Edit Modal -->
                    <div class="modal fade" id="massEditModal" tabindex="-1" aria-labelledby="massEditModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <?= $this->Form->create(null, [
                                    'type' => 'post',
                                    'id' => 'mass-edit-form',
                                    'url' => ['action' => 'bulkUpdateFields']
                                ]) ?>
                                <div class="modal-header">
                                    <h5 class="modal-title" id="massEditModalLabel"><?= __('Mass Edit Products') ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= __('Close') ?>"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <?= __('Only enabled fields will be updated. Leave fields disabled to keep their current values.') ?>
                                    </div>
                                    
                                    <!-- Selected products count -->
                                    <div class="mb-3">
                                        <strong><?= __('Selected Products:') ?> <span id="modal-selected-count">0</span></strong>
                                    </div>
                                    
                                    <!-- Verification Status -->
                                    <div class="mb-3">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input field-toggle" id="toggle-verification-status" data-field="verification_status">
                                            <label class="form-check-label" for="toggle-verification-status">
                                                <strong><?= __('Update Verification Status') ?></strong>
                                            </label>
                                        </div>
                                        <?= $this->Form->control('verification_status', [
                                            'type' => 'select',
                                            'options' => [
                                                'pending' => __('Pending'),
                                                'approved' => __('Approved'),
                                                'rejected' => __('Rejected')
                                            ],
                                            'class' => 'form-control dependent-field',
                                            'data-depends' => 'toggle-verification-status',
                                            'disabled' => true,
                                            'label' => false
                                        ]) ?>
                                    </div>
                                    
                                    <!-- Publishing Status -->
                                    <div class="mb-3">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input field-toggle" id="toggle-published" data-field="is_published">
                                            <label class="form-check-label" for="toggle-published">
                                                <strong><?= __('Update Published Status') ?></strong>
                                            </label>
                                        </div>
                                        <?= $this->Form->control('is_published', [
                                            'type' => 'select',
                                            'options' => [
                                                '1' => __('Published'),
                                                '0' => __('Unpublished')
                                            ],
                                            'class' => 'form-control dependent-field',
                                            'data-depends' => 'toggle-published',
                                            'disabled' => true,
                                            'label' => false
                                        ]) ?>
                                    </div>
                                    
                                    <!-- Featured Status -->
                                    <div class="mb-3">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input field-toggle" id="toggle-featured" data-field="featured">
                                            <label class="form-check-label" for="toggle-featured">
                                                <strong><?= __('Update Featured Status') ?></strong>
                                            </label>
                                        </div>
                                        <?= $this->Form->control('featured', [
                                            'type' => 'select',
                                            'options' => [
                                                '1' => __('Featured'),
                                                '0' => __('Not Featured')
                                            ],
                                            'class' => 'form-control dependent-field',
                                            'data-depends' => 'toggle-featured',
                                            'disabled' => true,
                                            'label' => false
                                        ]) ?>
                                    </div>
                                    
                                    <!-- Manufacturer -->
                                    <div class="mb-3">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input field-toggle" id="toggle-manufacturer" data-field="manufacturer">
                                            <label class="form-check-label" for="toggle-manufacturer">
                                                <strong><?= __('Update Manufacturer') ?></strong>
                                            </label>
                                        </div>
                                        <?= $this->Form->control('manufacturer', [
                                            'type' => 'text',
                                            'class' => 'form-control dependent-field',
                                            'data-depends' => 'toggle-manufacturer',
                                            'disabled' => true,
                                            'label' => false,
                                            'placeholder' => __('Enter manufacturer name')
                                        ]) ?>
                                    </div>
                                    
                                    <!-- Price and Currency -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input field-toggle" id="toggle-price" data-field="price">
                                                <label class="form-check-label" for="toggle-price">
                                                    <strong><?= __('Update Price') ?></strong>
                                                </label>
                                            </div>
                                            <?= $this->Form->control('price', [
                                                'type' => 'number',
                                                'class' => 'form-control dependent-field',
                                                'data-depends' => 'toggle-price',
                                                'disabled' => true,
                                                'label' => false,
                                                'placeholder' => '0.00',
                                                'step' => '0.01',
                                                'min' => '0'
                                            ]) ?>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check mb-2">
                                                <input type="checkbox" class="form-check-input field-toggle" id="toggle-currency" data-field="currency">
                                                <label class="form-check-label" for="toggle-currency">
                                                    <strong><?= __('Update Currency') ?></strong>
                                                </label>
                                            </div>
                                            <?= $this->Form->control('currency', [
                                                'type' => 'select',
                                                'options' => [
                                                    'USD' => 'USD',
                                                    'EUR' => 'EUR',
                                                    'GBP' => 'GBP',
                                                    'CAD' => 'CAD',
                                                    'AUD' => 'AUD',
                                                    'JPY' => 'JPY'
                                                ],
                                                'class' => 'form-control dependent-field',
                                                'data-depends' => 'toggle-currency',
                                                'disabled' => true,
                                                'label' => false
                                            ]) ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden field for selected IDs -->
                                    <div id="mass-edit-selected-ids"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Cancel') ?></button>
                                    <button type="submit" class="btn btn-primary"><?= __('Update Selected Products') ?></button>
                                </div>
                                <?= $this->Form->end() ?>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-4">
                        <p><?= __('No products found.') ?></p>
                        <?= $this->Html->link(
                            __('Add the first product'),
                            ['action' => 'add'],
                            ['class' => 'btn btn-primary']
                        ) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
