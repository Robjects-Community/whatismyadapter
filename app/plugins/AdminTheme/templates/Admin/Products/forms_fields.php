<?php
/**
 * Forms Fields Configuration View
 * @var \App\View\AppView $this
 * @var array $forms
 * @var array $field_types
 */
?>
<?= $this->Html->css('AdminTheme.forms', ['block' => true]) ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-wpforms mr-2"></i>
                        <?= __('Dynamic Form Fields') ?>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createFormModal">
                            <i class="fas fa-plus mr-2"></i><?= __('Create New Form') ?>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Form Builder Interface -->
                    <div class="row">
                        <div class="col-md-3">
                            <!-- Field Types Palette -->
                            <div class="card">
                                <div class="card-header">
                                    <h5><?= __('Field Types') ?></h5>
                                </div>
                                <div class="card-body p-2">
                                    <div class="field-palette">
                                        <?php foreach ($field_types ?? [] as $type => $config): ?>
                                        <div class="field-type-item draggable" 
                                             data-field-type="<?= $type ?>"
                                             data-field-config='<?= json_encode($config) ?>'>
                                            <div class="field-type-card">
                                                <i class="<?= $config['icon'] ?> mr-2"></i>
                                                <span><?= $config['label'] ?></span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Settings -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5><?= __('Form Settings') ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="form-title"><?= __('Form Title') ?></label>
                                        <input type="text" class="form-control" id="form-title" placeholder="<?= __('Enter form title') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="form-description"><?= __('Description') ?></label>
                                        <textarea class="form-control" id="form-description" rows="2" 
                                                  placeholder="<?= __('Enter form description') ?>"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="form-active" checked>
                                            <label class="custom-control-label" for="form-active">
                                                <?= __('Active') ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="form-public">
                                            <label class="custom-control-label" for="form-public">
                                                <?= __('Public Access') ?>
                                            </label>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-success btn-block" id="save-form">
                                        <i class="fas fa-save mr-2"></i><?= __('Save Form') ?>
                                    </button>
                                    <button type="button" class="btn btn-info btn-block" id="preview-form">
                                        <i class="fas fa-eye mr-2"></i><?= __('Preview') ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <!-- Form Builder Canvas -->
                            <div class="card">
                                <div class="card-header">
                                    <h5><?= __('Form Builder') ?></h5>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-sm btn-secondary" id="clear-form">
                                            <i class="fas fa-trash mr-1"></i><?= __('Clear All') ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="form-builder-canvas" class="form-builder-canvas">
                                        <div class="drop-zone-placeholder">
                                            <i class="fas fa-plus-circle fa-3x text-muted mb-3"></i>
                                            <h4 class="text-muted"><?= __('Drag fields here to build your form') ?></h4>
                                            <p class="text-muted"><?= __('Start by dragging field types from the left panel') ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Forms -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h4><?= __('Existing Forms') ?></h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= __('Form Name') ?></th>
                                            <th><?= __('Fields') ?></th>
                                            <th><?= __('Submissions') ?></th>
                                            <th><?= __('Status') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($forms)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                <?= __('No forms found. Create your first form using the builder above.') ?>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($forms as $form): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= h($form['title']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= h($form['description']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <?= $form['field_count'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">
                                                        <?= $form['submission_count'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($form['is_active']): ?>
                                                        <span class="badge badge-success"><?= __('Active') ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary"><?= __('Inactive') ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($form['is_public']): ?>
                                                        <span class="badge badge-info"><?= __('Public') ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small><?= $form['created']->format('Y-m-d H:i') ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary edit-form" 
                                                                data-form-id="<?= $form['id'] ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info duplicate-form" 
                                                                data-form-id="<?= $form['id'] ?>">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-success view-submissions" 
                                                                data-form-id="<?= $form['id'] ?>">
                                                            <i class="fas fa-list"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-form" 
                                                                data-form-id="<?= $form['id'] ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
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
            </div>
        </div>
    </div>
</div>

<!-- Field Configuration Modal -->
<div class="modal fade" id="fieldConfigModal" tabindex="-1" role="dialog" aria-labelledby="fieldConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fieldConfigModalLabel">
                    <i class="fas fa-cog mr-2"></i><?= __('Configure Field') ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="field-config-content">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= __('Cancel') ?></button>
                <button type="button" class="btn btn-primary" id="save-field-config"><?= __('Save Field') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Form Preview Modal -->
<div class="modal fade" id="formPreviewModal" tabindex="-1" role="dialog" aria-labelledby="formPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formPreviewModalLabel">
                    <i class="fas fa-eye mr-2"></i><?= __('Form Preview') ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="form-preview-content">
                <!-- Preview content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php $this->append('script'); ?>
<script>
$(document).ready(function() {
    let formFields = [];
    
    // Make field types draggable
    $('.draggable').draggable({
        helper: 'clone',
        revert: 'invalid',
        zIndex: 1000
    });

    // Make form canvas droppable
    $('#form-builder-canvas').droppable({
        accept: '.draggable',
        drop: function(event, ui) {
            const fieldType = ui.helper.data('field-type');
            const fieldConfig = ui.helper.data('field-config');
            
            // Hide placeholder if it exists
            $('.drop-zone-placeholder').hide();
            
            // Add field to form
            addFieldToForm(fieldType, fieldConfig);
        }
    });

    function addFieldToForm(fieldType, fieldConfig) {
        const fieldId = 'field_' + Date.now();
        const fieldHtml = createFieldElement(fieldId, fieldType, fieldConfig);
        
        $('#form-builder-canvas').append(fieldHtml);
        formFields.push({
            id: fieldId,
            type: fieldType,
            config: fieldConfig
        });
        
        // Make new field sortable
        makeFieldsSortable();
    }

    function createFieldElement(fieldId, fieldType, fieldConfig) {
        return `
            <div class="form-field-item" data-field-id="${fieldId}" data-field-type="${fieldType}">
                <div class="field-controls">
                    <button type="button" class="btn btn-sm btn-outline-primary configure-field">
                        <i class="fas fa-cog"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-field">
                        <i class="fas fa-times"></i>
                    </button>
                    <span class="drag-handle">
                        <i class="fas fa-grip-vertical"></i>
                    </span>
                </div>
                <div class="field-preview">
                    <label>${fieldConfig.label}</label>
                    <input type="${fieldType}" class="form-control" placeholder="${fieldConfig.placeholder || ''}" disabled>
                </div>
            </div>
        `;
    }

    function makeFieldsSortable() {
        $('#form-builder-canvas').sortable({
            items: '.form-field-item',
            handle: '.drag-handle',
            placeholder: 'field-placeholder',
            tolerance: 'pointer'
        });
    }

    // Configure field
    $(document).on('click', '.configure-field', function() {
        const fieldId = $(this).closest('.form-field-item').data('field-id');
        // Show field configuration modal
        $('#fieldConfigModal').modal('show');
    });

    // Remove field
    $(document).on('click', '.remove-field', function() {
        const fieldItem = $(this).closest('.form-field-item');
        const fieldId = fieldItem.data('field-id');
        
        // Remove from DOM
        fieldItem.remove();
        
        // Remove from array
        formFields = formFields.filter(field => field.id !== fieldId);
        
        // Show placeholder if no fields
        if (formFields.length === 0) {
            $('.drop-zone-placeholder').show();
        }
    });

    // Save form
    $('#save-form').on('click', function() {
        const formData = {
            title: $('#form-title').val(),
            description: $('#form-description').val(),
            active: $('#form-active').is(':checked'),
            public: $('#form-public').is(':checked'),
            fields: formFields
        };
        
        console.log('Saving form:', formData);
        // Handle form saving
    });

    // Preview form
    $('#preview-form').on('click', function() {
        // Generate preview and show modal
        $('#formPreviewModal').modal('show');
    });

    // Clear form
    $('#clear-form').on('click', function() {
        if (confirm('<?= __('Are you sure you want to clear all fields?') ?>')) {
            $('#form-builder-canvas').empty();
            $('.drop-zone-placeholder').show();
            formFields = [];
        }
    });

    // Edit existing form
    $('.edit-form').on('click', function() {
        const formId = $(this).data('form-id');
        // Load form data and populate builder
        console.log('Editing form:', formId);
    });

    // Duplicate form
    $('.duplicate-form').on('click', function() {
        const formId = $(this).data('form-id');
        // Handle form duplication
        console.log('Duplicating form:', formId);
    });

    // View submissions
    $('.view-submissions').on('click', function() {
        const formId = $(this).data('form-id');
        // Navigate to submissions view
        window.location.href = '<?= $this->Url->build(['action' => 'formsStats']) ?>?form_id=' + formId;
    });

    // Delete form
    $('.delete-form').on('click', function() {
        const formId = $(this).data('form-id');
        if (confirm('<?= __('Are you sure you want to delete this form?') ?>')) {
            // Handle form deletion
            console.log('Deleting form:', formId);
        }
    });
});
</script>
<?php $this->end(); ?>