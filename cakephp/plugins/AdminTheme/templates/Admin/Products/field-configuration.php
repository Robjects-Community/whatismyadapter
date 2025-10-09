<?php
/**
 * @var \App\View\AppView $this
 * @var array $formFields Current form field configurations
 * @var array $fieldGroups Available field groups
 * @var array $fieldTypes Available field types
 * @var array $submissionStats Statistics about form usage
 */

$this->assign('title', __('Product Form Field Configuration'));
$this->assign('page_title', __('Dynamic Form Fields'));
$this->assign('page_subtitle', __('Configure dynamic product submission form fields with AI auto-fill capabilities'));

// Include the products tabs navigation
echo $this->element('AdminTheme.nav/products_tabs');
?>

<div class="product-field-configuration">
    <div class="row">
        <!-- Configuration Panel -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-sliders-h me-2"></i>
                        <?= __('Dynamic Form Field Management') ?>
                    </h5>
                    <button class="btn btn-light btn-sm" id="add-field-btn">
                        <i class="fas fa-plus me-1"></i>
                        <?= __('Add Field') ?>
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        <?= __('Configure dynamic form fields that will be used in the public product submission form. Each field can have AI-powered auto-fill suggestions to help users complete their submissions more efficiently.') ?>
                    </p>

                    <!-- Field Groups Accordion -->
                    <div class="accordion" id="fieldGroupsAccordion">
                        <?php foreach ($fieldGroups as $groupKey => $group): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?= h(ucfirst($groupKey)) ?>">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= h(ucfirst($groupKey)) ?>" aria-expanded="true">
                                        <i class="<?= h($group['icon']) ?> me-2"></i>
                                        <?= h($group['title']) ?>
                                        <span class="badge bg-secondary ms-2">
                                            <?= count($formFields[$groupKey] ?? []) ?> fields
                                        </span>
                                    </button>
                                </h2>
                                <div id="collapse<?= h(ucfirst($groupKey)) ?>" class="accordion-collapse collapse show" data-bs-parent="#fieldGroupsAccordion">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <p class="text-muted small"><?= h($group['description']) ?></p>
                                            </div>
                                        </div>

                                        <!-- Field List -->
                                        <div class="field-list" data-group="<?= h($groupKey) ?>">
                                            <?php if (!empty($formFields[$groupKey])): ?>
                                                <?php foreach ($formFields[$groupKey] as $field): ?>
                                                    <div class="field-item card mb-3" data-field-id="<?= h($field->id) ?>">
                                                        <div class="card-body">
                                                            <div class="row align-items-center">
                                                                <div class="col-md-4">
                                                                    <div class="field-info">
                                                                        <h6 class="mb-1">
                                                                            <?= h($field->field_label) ?>
                                                                            <?php if ($field->is_required): ?>
                                                                                <span class="badge bg-danger ms-1">Required</span>
                                                                            <?php endif; ?>
                                                                            <?php if ($field->ai_enabled): ?>
                                                                                <span class="badge bg-success ms-1">
                                                                                    <i class="fas fa-magic"></i> AI
                                                                                </span>
                                                                            <?php endif; ?>
                                                                        </h6>
                                                                        <small class="text-muted">
                                                                            Field Name: <?= h($field->field_name) ?> | 
                                                                            Type: <?= h(ucfirst($field->field_type)) ?> |
                                                                            Width: <?= h($field->column_width) ?>/12
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="field-preview">
                                                                        <small class="text-muted d-block">Preview:</small>
                                                                        <?= $this->element('AdminTheme.form_field_preview', ['field' => $field]) ?>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="field-actions text-end">
                                                                        <div class="btn-group" role="group">
                                                                            <button class="btn btn-sm btn-outline-primary edit-field-btn" 
                                                                                    data-field-id="<?= h($field->id) ?>">
                                                                                <i class="fas fa-edit"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-outline-secondary test-ai-btn" 
                                                                                    data-field-name="<?= h($field->field_name) ?>"
                                                                                    <?= $field->ai_enabled ? '' : 'disabled' ?>>
                                                                                <i class="fas fa-magic"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-outline-info move-field-btn"
                                                                                    data-field-id="<?= h($field->id) ?>">
                                                                                <i class="fas fa-arrows-alt"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-outline-warning toggle-field-btn"
                                                                                    data-field-id="<?= h($field->id) ?>"
                                                                                    data-active="<?= $field->is_active ? 'true' : 'false' ?>">
                                                                                <i class="fas fa-<?= $field->is_active ? 'eye-slash' : 'eye' ?>"></i>
                                                                            </button>
                                                                            <button class="btn btn-sm btn-outline-danger delete-field-btn" 
                                                                                    data-field-id="<?= h($field->id) ?>">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- AI Configuration Display -->
                                                            <?php if ($field->ai_enabled): ?>
                                                                <div class="row mt-2">
                                                                    <div class="col-12">
                                                                        <div class="ai-config-display bg-light p-2 rounded">
                                                                            <small class="text-success">
                                                                                <i class="fas fa-magic me-1"></i>
                                                                                <strong>AI Template:</strong> 
                                                                                <?= h(substr($field->ai_prompt_template ?: 'No template set', 0, 100)) ?>
                                                                                <?php if (strlen($field->ai_prompt_template ?: '') > 100): ?>...<?php endif; ?>
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <?= __('No fields configured for this group yet.') ?>
                                                    <button class="btn btn-sm btn-primary ms-2 add-field-to-group-btn" 
                                                            data-group="<?= h($groupKey) ?>">
                                                        <?= __('Add First Field') ?>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics and Tools Panel -->
        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        <?= __('Form Usage Statistics') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item mb-3">
                                <div class="stat-number text-primary fs-4 fw-bold">
                                    <?= count($formFields['basic_information'] ?? []) + 
                                        count($formFields['media'] ?? []) + 
                                        count($formFields['technical_details'] ?? []) + 
                                        count($formFields['categories'] ?? []) ?>
                                </div>
                                <div class="stat-label text-muted small">
                                    <?= __('Total Fields') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item mb-3">
                                <div class="stat-number text-success fs-4 fw-bold">
                                    <?php 
                                    $aiEnabledCount = 0;
                                    foreach ($formFields as $groupFields) {
                                        foreach ($groupFields as $field) {
                                            if ($field->ai_enabled) $aiEnabledCount++;
                                        }
                                    }
                                    echo $aiEnabledCount;
                                    ?>
                                </div>
                                <div class="stat-label text-muted small">
                                    <?= __('AI-Enabled') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number text-warning fs-4 fw-bold">
                                    <?= $submissionStats['total_submissions'] ?? 0 ?>
                                </div>
                                <div class="stat-label text-muted small">
                                    <?= __('Form Submissions') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number text-danger fs-4 fw-bold">
                                    <?php 
                                    $requiredCount = 0;
                                    foreach ($formFields as $groupFields) {
                                        foreach ($groupFields as $field) {
                                            if ($field->is_required) $requiredCount++;
                                        }
                                    }
                                    echo $requiredCount;
                                    ?>
                                </div>
                                <div class="stat-label text-muted small">
                                    <?= __('Required Fields') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Testing Panel -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-magic me-2"></i>
                        <?= __('AI Suggestion Tester') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        <?= __('Test AI suggestions for any field with sample data.') ?>
                    </p>

                    <div class="mb-3">
                        <label class="form-label"><?= __('Test Field') ?></label>
                        <select class="form-select" id="ai-test-field">
                            <option value=""><?= __('Select a field...') ?></option>
                            <?php foreach ($formFields as $groupKey => $groupFields): ?>
                                <?php foreach ($groupFields as $field): ?>
                                    <?php if ($field->ai_enabled): ?>
                                        <option value="<?= h($field->field_name) ?>">
                                            <?= h($field->field_label) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= __('Sample Data (JSON)') ?></label>
                        <textarea class="form-control font-monospace" id="ai-test-data" rows="4" placeholder='{
  "title": "USB-C to HDMI Adapter",
  "manufacturer": "Anker",
  "description": "High-quality adapter for video output"
}'></textarea>
                    </div>

                    <button class="btn btn-success btn-sm w-100" id="test-ai-suggestions-btn">
                        <i class="fas fa-magic me-1"></i>
                        <?= __('Test AI Suggestions') ?>
                    </button>

                    <div id="ai-test-results" class="mt-3 d-none">
                        <div class="alert alert-info">
                            <div class="ai-suggestions-display"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-tools me-2"></i>
                        <?= __('Quick Actions') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" id="bulk-toggle-ai-btn">
                            <i class="fas fa-magic me-1"></i>
                            <?= __('Toggle All AI Fields') ?>
                        </button>
                        <button class="btn btn-outline-warning btn-sm" id="reset-field-order-btn">
                            <i class="fas fa-sort-numeric-down me-1"></i>
                            <?= __('Reset Field Order') ?>
                        </button>
                        <button class="btn btn-outline-info btn-sm" id="export-config-btn">
                            <i class="fas fa-download me-1"></i>
                            <?= __('Export Configuration') ?>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="import-config-btn">
                            <i class="fas fa-upload me-1"></i>
                            <?= __('Import Configuration') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Field Configuration Modal -->
<div class="modal fade" id="fieldConfigModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fieldConfigModalTitle"><?= __('Configure Field') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Field configuration form will be loaded here via AJAX -->
                <div id="field-config-form-container">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden"><?= __('Loading...') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.field-item {
    border-left: 4px solid #dee2e6;
    transition: all 0.2s ease-in-out;
}

.field-item:hover {
    border-left-color: #0d6efd;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.field-item[data-active="false"] {
    opacity: 0.6;
    border-left-color: #ffc107;
}

.ai-config-display {
    font-size: 0.85rem;
    border-left: 3px solid #198754;
}

.stat-number {
    font-weight: 700;
    line-height: 1;
}

.accordion-button:not(.collapsed) {
    background-color: rgba(13, 110, 253, 0.1);
}

.field-preview .form-control,
.field-preview .form-select {
    font-size: 0.8rem;
    height: auto;
    padding: 0.25rem 0.5rem;
}

#ai-test-results .alert {
    max-height: 200px;
    overflow-y: auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Field management event handlers
    const fieldConfigModal = new bootstrap.Modal(document.getElementById('fieldConfigModal'));
    
    // Add field button
    document.getElementById('add-field-btn').addEventListener('click', function() {
        loadFieldConfigForm();
    });
    
    // Add field to specific group buttons
    document.querySelectorAll('.add-field-to-group-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const group = this.dataset.group;
            loadFieldConfigForm(null, group);
        });
    });
    
    // Edit field buttons
    document.querySelectorAll('.edit-field-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const fieldId = this.dataset.fieldId;
            loadFieldConfigForm(fieldId);
        });
    });
    
    // Toggle field active status
    document.querySelectorAll('.toggle-field-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const fieldId = this.dataset.fieldId;
            const isActive = this.dataset.active === 'true';
            toggleFieldStatus(fieldId, !isActive);
        });
    });
    
    // Delete field buttons
    document.querySelectorAll('.delete-field-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const fieldId = this.dataset.fieldId;
            if (confirm('<?= __('Are you sure you want to delete this field?') ?>')) {
                deleteField(fieldId);
            }
        });
    });
    
    // AI suggestion testing
    document.getElementById('test-ai-suggestions-btn').addEventListener('click', function() {
        testAiSuggestions();
    });
    
    // Quick actions
    document.getElementById('bulk-toggle-ai-btn').addEventListener('click', function() {
        toggleAllAiFields();
    });
    
    document.getElementById('reset-field-order-btn').addEventListener('click', function() {
        resetFieldOrder();
    });
    
    document.getElementById('export-config-btn').addEventListener('click', function() {
        exportConfiguration();
    });
    
    function loadFieldConfigForm(fieldId = null, defaultGroup = null) {
        const url = fieldId 
            ? `/admin/product-form-fields/edit/${fieldId}` 
            : `/admin/product-form-fields/add${defaultGroup ? '?group=' + defaultGroup : ''}`;
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('field-config-form-container').innerHTML = html;
                fieldConfigModal.show();
            })
            .catch(error => {
                console.error('Error loading field config form:', error);
                alert('<?= __('Error loading field configuration form.') ?>');
            });
    }
    
    function toggleFieldStatus(fieldId, newStatus) {
        fetch(`/admin/product-form-fields/toggle/${fieldId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ active: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '<?= __('Error toggling field status.') ?>');
            }
        })
        .catch(error => {
            console.error('Error toggling field status:', error);
            alert('<?= __('Error toggling field status.') ?>');
        });
    }
    
    function deleteField(fieldId) {
        fetch(`/admin/product-form-fields/delete/${fieldId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '<?= __('Error deleting field.') ?>');
            }
        })
        .catch(error => {
            console.error('Error deleting field:', error);
            alert('<?= __('Error deleting field.') ?>');
        });
    }
    
    function testAiSuggestions() {
        const fieldName = document.getElementById('ai-test-field').value;
        const testData = document.getElementById('ai-test-data').value;
        
        if (!fieldName) {
            alert('<?= __('Please select a field to test.') ?>');
            return;
        }
        
        let parsedData = {};
        try {
            parsedData = testData ? JSON.parse(testData) : {};
        } catch (e) {
            alert('<?= __('Invalid JSON in test data.') ?>');
            return;
        }
        
        fetch('/admin/product-form-fields/test-ai', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                field_name: fieldName,
                test_data: parsedData
            })
        })
        .then(response => response.json())
        .then(data => {
            displayAiTestResults(data);
        })
        .catch(error => {
            console.error('Error testing AI suggestions:', error);
            alert('<?= __('Error testing AI suggestions.') ?>');
        });
    }
    
    function displayAiTestResults(results) {
        const resultsDiv = document.getElementById('ai-test-results');
        const displayDiv = resultsDiv.querySelector('.ai-suggestions-display');
        
        let html = `<strong><?= __('AI Test Results:') ?></strong><br>`;
        html += `<small><strong><?= __('Confidence:') ?></strong> ${results.confidence_level}%</small><br>`;
        html += `<small><strong><?= __('Reasoning:') ?></strong> ${results.reasoning}</small><br><br>`;
        
        if (results.suggestions && results.suggestions.length > 0) {
            html += `<strong><?= __('Suggestions:') ?></strong><ul>`;
            results.suggestions.forEach(suggestion => {
                html += `<li>${suggestion}</li>`;
            });
            html += `</ul>`;
        } else {
            html += `<em><?= __('No suggestions generated.') ?></em>`;
        }
        
        displayDiv.innerHTML = html;
        resultsDiv.classList.remove('d-none');
    }
    
    function toggleAllAiFields() {
        if (confirm('<?= __('Toggle AI functionality for all applicable fields?') ?>')) {
            fetch('/admin/product-form-fields/bulk-toggle-ai', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?= __('Error toggling AI fields.') ?>');
                }
            });
        }
    }
    
    function resetFieldOrder() {
        if (confirm('<?= __('Reset all fields to default order?') ?>')) {
            fetch('/admin/product-form-fields/reset-order', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || '<?= __('Error resetting field order.') ?>');
                }
            });
        }
    }
    
    function exportConfiguration() {
        window.location.href = '/admin/product-form-fields/export';
    }
});
</script>
