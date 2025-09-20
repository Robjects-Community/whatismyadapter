<?php
/**
 * ProductFormFields Management Table Element
 * 
 * A reusable component for displaying and managing product form fields
 * within the admin interface. Shows fields in a condensed table format
 * with inline actions for quick management.
 *
 * @var \App\View\AppView $this
 * @var array $productFormFields Collection of product form fields
 */
?>

<?php if (empty($productFormFields)): ?>
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="fas fa-list-alt fa-3x text-muted"></i>
        </div>
        <h6 class="text-muted mb-2"><?= __('No Custom Fields Yet') ?></h6>
        <p class="text-muted mb-4">
            <?= __('Create dynamic form fields to customize your product submission form. Add fields like dropdowns, text inputs, or file uploads to collect specific information from users.') ?>
        </p>
        <div class="d-flex justify-content-center gap-2">
            <?= $this->Html->link(
                '<i class="fas fa-plus me-1"></i>' . __('Add Your First Field'),
                ['controller' => 'ProductFormFields', 'action' => 'add'],
                ['class' => 'btn btn-primary', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-magic me-1"></i>' . __('Import Template'),
                '#',
                ['class' => 'btn btn-outline-info', 'escape' => false, 'data-bs-toggle' => 'modal', 'data-bs-target' => '#templateModal']
            ) ?>
        </div>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th width="5%">
                        <i class="fas fa-arrows-alt-v text-muted" title="<?= __('Drag to reorder') ?>"></i>
                    </th>
                    <th width="20%"><?= __('Field Name') ?></th>
                    <th width="15%"><?= __('Type') ?></th>
                    <th width="15%"><?= __('Group') ?></th>
                    <th width="10%" class="text-center"><?= __('Required') ?></th>
                    <th width="10%" class="text-center"><?= __('AI') ?></th>
                    <th width="10%" class="text-center"><?= __('Status') ?></th>
                    <th width="15%" class="text-end"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody id="sortableFields" data-controller="ProductFormFields" data-action="reorder">
                <?php foreach ($productFormFields as $field): ?>
                    <tr data-field-id="<?= h($field->id) ?>" class="sortable-row">
                        <td class="text-center">
                            <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: move;"></i>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark"><?= h($field->field_label) ?></span>
                                <small class="text-muted font-monospace"><?= h($field->field_name) ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info">
                                <?= h(ucfirst($field->field_type)) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($field->field_group): ?>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    <?= h($field->field_group) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($field->is_required): ?>
                                <i class="fas fa-asterisk text-danger" title="<?= __('Required') ?>"></i>
                            <?php else: ?>
                                <i class="fas fa-circle text-light" title="<?= __('Optional') ?>"></i>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($field->ai_enabled): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-success toggle-ai-btn" 
                                        data-field-id="<?= h($field->id) ?>" 
                                        title="<?= __('AI Enabled - Click to disable') ?>">
                                    <i class="fas fa-robot"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary toggle-ai-btn" 
                                        data-field-id="<?= h($field->id) ?>" 
                                        title="<?= __('AI Disabled - Click to enable') ?>">
                                    <i class="fas fa-robot"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($field->is_active): ?>
                                <span class="badge bg-success-subtle text-success"><?= __('Active') ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning"><?= __('Hidden') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <?= $this->Html->link(
                                    '<i class="fas fa-eye"></i>',
                                    ['controller' => 'ProductFormFields', 'action' => 'view', $field->id],
                                    [
                                        'class' => 'btn btn-outline-secondary',
                                        'title' => __('View Details'),
                                        'escape' => false,
                                        'data-bs-toggle' => 'tooltip'
                                    ]
                                ) ?>
                                <?= $this->Html->link(
                                    '<i class="fas fa-edit"></i>',
                                    ['controller' => 'ProductFormFields', 'action' => 'edit', $field->id],
                                    [
                                        'class' => 'btn btn-outline-primary',
                                        'title' => __('Edit Field'),
                                        'escape' => false,
                                        'data-bs-toggle' => 'tooltip'
                                    ]
                                ) ?>
                                <?= $this->Form->postLink(
                                    '<i class="fas fa-trash"></i>',
                                    ['controller' => 'ProductFormFields', 'action' => 'delete', $field->id],
                                    [
                                        'class' => 'btn btn-outline-danger',
                                        'title' => __('Delete Field'),
                                        'escape' => false,
                                        'confirm' => __('Are you sure you want to delete the field "{0}"? This action cannot be undone.', $field->field_label),
                                        'data-bs-toggle' => 'tooltip'
                                    ]
                                ) ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Quick Stats Row -->
    <div class="border-top bg-light px-4 py-3">
        <div class="row text-center">
            <div class="col-3">
                <div class="h6 mb-1 text-primary"><?= count($productFormFields) ?></div>
                <div class="small text-muted"><?= __('Total Fields') ?></div>
            </div>
            <div class="col-3">
                <div class="h6 mb-1 text-success">
                    <?= count(array_filter($productFormFields, function($f) { return $f->is_required; })) ?>
                </div>
                <div class="small text-muted"><?= __('Required') ?></div>
            </div>
            <div class="col-3">
                <div class="h6 mb-1 text-info">
                    <?= count(array_filter($productFormFields, function($f) { return $f->ai_enabled; })) ?>
                </div>
                <div class="small text-muted"><?= __('AI Enabled') ?></div>
            </div>
            <div class="col-3">
                <div class="h6 mb-1 text-warning">
                    <?= count(array_unique(array_column($productFormFields, 'field_group'))) ?>
                </div>
                <div class="small text-muted"><?= __('Groups') ?></div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Footer -->
    <div class="border-top px-4 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary" id="bulkToggleAi">
                    <i class="fas fa-robot me-1"></i><?= __('Toggle AI (Selected)') ?>
                </button>
                <button type="button" class="btn btn-outline-warning" id="bulkHide">
                    <i class="fas fa-eye-slash me-1"></i><?= __('Hide (Selected)') ?>
                </button>
            </div>
            <div>
                <small class="text-muted me-3">
                    <i class="fas fa-info-circle me-1"></i>
                    <?= __('Drag rows to reorder fields on the form') ?>
                </small>
                <?= $this->Html->link(
                    '<i class="fas fa-undo me-1"></i>' . __('Reset Order'),
                    ['controller' => 'ProductFormFields', 'action' => 'resetOrder'],
                    [
                        'class' => 'btn btn-outline-secondary btn-sm me-2',
                        'escape' => false,
                        'confirm' => __('Reset all fields to default order?')
                    ]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-plus me-1"></i>' . __('Add Field'),
                    ['controller' => 'ProductFormFields', 'action' => 'add'],
                    ['class' => 'btn btn-primary btn-sm', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php $this->append('css'); ?>
<style>
/* Enhanced table styling for ProductFormFields */
.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.sortable-row {
    transition: all 0.2s ease;
}

.sortable-row.ui-sortable-helper {
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: rotate(2deg);
}

.drag-handle:hover {
    color: #0d6efd !important;
}

.toggle-ai-btn {
    border: none;
    transition: all 0.2s ease;
}

.toggle-ai-btn:hover {
    transform: scale(1.1);
}

/* Interactive field type badges */
.badge {
    font-size: 0.75em;
    font-weight: 500;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
}
</style>
<?php $this->end(); ?>

<?php $this->append('script'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize sortable if we have fields
    const sortableElement = document.getElementById('sortableFields');
    if (sortableElement && sortableElement.children.length > 0) {
        // Using a basic drag and drop implementation
        let draggedElement = null;
        
        sortableElement.addEventListener('dragstart', function(e) {
            if (e.target.classList.contains('drag-handle')) {
                draggedElement = e.target.closest('tr');
                draggedElement.style.opacity = '0.5';
            }
        });
        
        sortableElement.addEventListener('dragend', function(e) {
            if (draggedElement) {
                draggedElement.style.opacity = '1';
                draggedElement = null;
            }
        });
        
        sortableElement.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        sortableElement.addEventListener('drop', function(e) {
            e.preventDefault();
            if (draggedElement && e.target.closest('tr') !== draggedElement) {
                const targetRow = e.target.closest('tr');
                if (targetRow) {
                    const parent = targetRow.parentNode;
                    parent.insertBefore(draggedElement, targetRow.nextSibling);
                    
                    // Update order (you would implement AJAX call here)
                    updateFieldOrder();
                }
            }
        });
        
        // Make drag handles draggable
        const dragHandles = sortableElement.querySelectorAll('.drag-handle');
        dragHandles.forEach(handle => {
            handle.closest('tr').setAttribute('draggable', true);
        });
    }

    // AI Toggle functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.toggle-ai-btn')) {
            const btn = e.target.closest('.toggle-ai-btn');
            const fieldId = btn.dataset.fieldId;
            
            // Visual feedback
            btn.disabled = true;
            const icon = btn.querySelector('i');
            const originalClass = icon.className;
            icon.className = 'fas fa-spinner fa-spin';
            
            // AJAX call to toggle AI
            fetch(`/admin/product-form-fields/toggle-ai/${fieldId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button state
                    if (data.ai_enabled) {
                        btn.className = 'btn btn-sm btn-outline-success toggle-ai-btn';
                        btn.title = 'AI Enabled - Click to disable';
                    } else {
                        btn.className = 'btn btn-sm btn-outline-secondary toggle-ai-btn';
                        btn.title = 'AI Disabled - Click to enable';
                    }
                    
                    // Show success message
                    showToast('AI status updated successfully', 'success');
                } else {
                    showToast('Failed to update AI status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating AI status', 'error');
            })
            .finally(() => {
                icon.className = originalClass;
                btn.disabled = false;
            });
        }
    });

    function updateFieldOrder() {
        const rows = Array.from(sortableElement.querySelectorAll('tr[data-field-id]'));
        const fieldIds = rows.map(row => row.dataset.fieldId);
        
        fetch('/admin/product-form-fields/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({ field_ids: fieldIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Field order updated', 'success');
            }
        })
        .catch(error => {
            console.error('Error updating order:', error);
        });
    }

    function showToast(message, type) {
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
});
</script>
<?php $this->end(); ?>
