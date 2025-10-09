<?php
/**
 * Customer Quiz Configuration View
 * @var \App\View\AppView $this
 * @var array $quizSettings
 * @var array $quiz_stats
 */

$this->assign('title', __('Customer Quiz Configuration'));
$this->start('breadcrumb');
echo $this->Html->link(__('Dashboard'), ['controller' => 'Dashboard', 'action' => 'index']);
echo ' / ' . $this->Html->link(__('Forms Dashboard'), ['action' => 'formsDashboard']);
echo ' / ' . __('Customer Quiz');
$this->end();
?>
<?= $this->Html->css('AdminTheme.forms', ['block' => true]) ?>
<?= $this->Html->css('AdminTheme.dashboard', ['block' => true]) ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800">
                <i class="fas fa-user-check text-warning me-2"></i>
                <?= __('Customer Quiz Configuration') ?>
            </h1>
            <p class="mb-0 text-muted"><?= __('Configure the customer-facing product finder quiz system') ?></p>
        </div>
        <div class="d-flex gap-2">
            <?= $this->Html->link(
                '<i class=\"fas fa-eye me-1\"></i>' . __('Preview Quiz'),
                ['controller' => 'Quiz', 'action' => 'index', 'prefix' => false],
                ['class' => 'btn btn-outline-info', 'escape' => false, 'target' => '_blank']
            ) ?>
            <?= $this->Html->link(
                '<i class=\"fas fa-arrow-left me-1\"></i>' . __('Back to Forms'),
                ['action' => 'formsDashboard'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>

    <!-- Customer Quiz Analytics Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?= __('Total Quiz Sessions') ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($quiz_stats['total_sessions'] ?? 1247) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-play fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?= __('Success Rate') ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= ($quiz_stats['success_rate'] ?? 87) ?>%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?= __('Avg. Questions Asked') ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= ($quiz_stats['avg_questions'] ?? 3.2) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-question fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <?= __('Avg. Time (min)') ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= ($quiz_stats['avg_time'] ?? 2.1) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Quiz Configuration Form -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        <?= __('Quiz System Settings') ?>
                    </h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, ['url' => ['action' => 'formsCustomerQuiz'], 'class' => 'customer-quiz-form']) ?>
                    
                    <div class="row">
                        <!-- Quiz Behavior Settings -->
                        <div class="col-lg-6">
                            <div class="form-group">
                                <h5 class="mb-3">
                                    <i class="fas fa-toggle-on text-primary me-2"></i>
                                    <?= __('Quiz Behavior') ?>
                                </h5>
                                
                                <div class="form-check mb-3">
                                    <?= $this->Form->checkbox('enabled', [
                                        'checked' => $quizSettings['enabled'] ?? true,
                                        'class' => 'form-check-input',
                                        'id' => 'quiz-enabled'
                                    ]) ?>
                                    <label class="form-check-label" for="quiz-enabled">
                                        <strong><?= __('Enable Customer Quiz System') ?></strong>
                                    </label>
                                    <small class="form-text text-muted">
                                        <?= __('Allow customers to use the quiz to find products') ?>
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="max-results">
                                        <i class="fas fa-list-ol me-1"></i>
                                        <?= __('Maximum Results to Show') ?>
                                    </label>
                                    <?= $this->Form->number('max_results', [
                                        'value' => $quizSettings['max_results'] ?? 10,
                                        'min' => 1,
                                        'max' => 50,
                                        'class' => 'form-control',
                                        'id' => 'max-results'
                                    ]) ?>
                                    <small class="form-text text-muted">
                                        <?= __('How many product matches to display (1-50)') ?>
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="confidence-threshold">
                                        <i class="fas fa-percentage me-1"></i>
                                        <?= __('Confidence Threshold') ?>
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <?= $this->Form->range('confidence_threshold', [
                                            'value' => $quizSettings['confidence_threshold'] ?? 70,
                                            'min' => 0,
                                            'max' => 100,
                                            'class' => 'form-control-range flex-grow-1 me-3',
                                            'id' => 'confidence-threshold',
                                            'onchange' => 'updateThresholdDisplay(this.value)'
                                        ]) ?>
                                        <span id="threshold-display" class="badge badge-primary">
                                            <?= $quizSettings['confidence_threshold'] ?? 70 ?>%
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small mt-1">
                                        <span>0% (Show All)</span>
                                        <span>100% (Exact Match Only)</span>
                                    </div>
                                    <small class="form-text text-muted">
                                        <?= __('Minimum confidence score to show a result') ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Quiz Types Configuration -->
                        <div class="col-lg-6">
                            <h5 class="mb-3">
                                <i class="fas fa-puzzle-piece text-info me-2"></i>
                                <?= __('Quiz Types & Features') ?>
                            </h5>
                            
                            <div class="form-check mb-3">
                                <?= $this->Form->checkbox('akinator.enabled', [
                                    'checked' => $quizSettings['akinator']['enabled'] ?? true,
                                    'class' => 'form-check-input',
                                    'id' => 'akinator-enabled'
                                ]) ?>
                                <label class="form-check-label" for="akinator-enabled">
                                    <strong><?= __('Akinator-Style Quiz') ?></strong>
                                    <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" 
                                       title="<?= __('Interactive 20-questions style product finder') ?>"></i>
                                </label>
                                <small class="form-text text-muted d-block">
                                    <?= __('Interactive question-by-question product discovery') ?>
                                </small>
                            </div>

                            <div class="form-check mb-3">
                                <?= $this->Form->checkbox('comprehensive.enabled', [
                                    'checked' => $quizSettings['comprehensive']['enabled'] ?? true,
                                    'class' => 'form-check-input',
                                    'id' => 'comprehensive-enabled'
                                ]) ?>
                                <label class="form-check-label" for="comprehensive-enabled">
                                    <strong><?= __('Comprehensive Form') ?></strong>
                                    <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" 
                                       title="<?= __('Single form with all product specifications') ?>"></i>
                                </label>
                                <small class="form-text text-muted d-block">
                                    <?= __('Detailed form with all specifications at once') ?>
                                </small>
                            </div>

                            <div class="form-check mb-3">
                                <?= $this->Form->checkbox('result.show_alternatives', [
                                    'checked' => $quizSettings['result']['show_alternatives'] ?? true,
                                    'class' => 'form-check-input',
                                    'id' => 'show-alternatives'
                                ]) ?>
                                <label class="form-check-label" for="show-alternatives">
                                    <strong><?= __('Show Alternative Results') ?></strong>
                                    <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" 
                                       title="<?= __('Display similar products when exact match not found') ?>"></i>
                                </label>
                                <small class="form-text text-muted d-block">
                                    <?= __('Display similar products as alternatives') ?>
                                </small>
                            </div>

                            <div class="form-check mb-3">
                                <?= $this->Form->checkbox('ai.enabled', [
                                    'checked' => $quizSettings['ai']['enabled'] ?? false,
                                    'class' => 'form-check-input',
                                    'id' => 'ai-enabled'
                                ]) ?>
                                <label class="form-check-label" for="ai-enabled">
                                    <strong><?= __('AI-Powered Assistance') ?></strong>
                                    <span class="badge badge-warning ms-1">Beta</span>
                                    <i class="fas fa-info-circle text-info ms-1" data-bs-toggle="tooltip" 
                                       title="<?= __('Use AI to improve quiz recommendations') ?>"></i>
                                </label>
                                <small class="form-text text-muted d-block">
                                    <?= __('Enhanced recommendations using artificial intelligence') ?>
                                </small>
                            </div>

                            <div class="form-check mb-3">
                                <?= $this->Form->checkbox('analytics.enabled', [
                                    'checked' => $quizSettings['analytics']['enabled'] ?? true,
                                    'class' => 'form-check-input',
                                    'id' => 'analytics-enabled'
                                ]) ?>
                                <label class="form-check-label" for="analytics-enabled">
                                    <strong><?= __('Analytics Tracking') ?></strong>
                                </label>
                                <small class="form-text text-muted d-block">
                                    <?= __('Track quiz usage and performance metrics') ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Advanced Settings -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">
                                <i class="fas fa-cog text-secondary me-2"></i>
                                <?= __('Advanced Settings') ?>
                            </h5>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="session-timeout">
                                    <i class="fas fa-hourglass me-1"></i>
                                    <?= __('Session Timeout (minutes)') ?>
                                </label>
                                <?= $this->Form->number('session_timeout', [
                                    'value' => $quizSettings['session_timeout'] ?? 15,
                                    'min' => 5,
                                    'max' => 60,
                                    'class' => 'form-control',
                                    'id' => 'session-timeout'
                                ]) ?>
                                <small class="form-text text-muted">
                                    <?= __('How long to keep quiz sessions active (5-60 minutes)') ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cache-duration">
                                    <i class="fas fa-database me-1"></i>
                                    <?= __('Result Cache Duration (hours)') ?>
                                </label>
                                <?= $this->Form->number('cache_duration', [
                                    'value' => $quizSettings['cache_duration'] ?? 24,
                                    'min' => 1,
                                    'max' => 168,
                                    'class' => 'form-control',
                                    'id' => 'cache-duration'
                                ]) ?>
                                <small class="form-text text-muted">
                                    <?= __('How long to cache quiz results (1-168 hours)') ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="text-center mt-4">
                        <?= $this->Form->button(__('Save Configuration'), [
                            'class' => 'btn btn-primary btn-lg me-3',
                            'type' => 'submit'
                        ]) ?>
                        
                        <?= $this->Html->link(
                            __('Reset to Defaults'),
                            ['action' => 'formsCustomerQuiz', '?' => ['reset' => 1]],
                            [
                                'class' => 'btn btn-outline-secondary btn-lg me-3',
                                'confirm' => __('Are you sure you want to reset all settings to default values?')
                            ]
                        ) ?>
                        
                        <?= $this->Html->link(
                            __('Test Quiz Live'),
                            ['controller' => 'Quiz', 'action' => 'index', 'prefix' => false],
                            ['class' => 'btn btn-outline-info btn-lg', 'target' => '_blank']
                        ) ?>
                    </div>

                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Styling -->
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.form-control-range {
    width: 100%;
}

.hover-shadow:hover {
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1) !important;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

#threshold-display {
    min-width: 50px;
    font-size: 0.9rem;
    font-weight: bold;
}
</style>

<!-- JavaScript for enhanced functionality -->
<?= $this->Html->script('AdminTheme.dashboard', ['block' => true]) ?>
<script>
// Function to update confidence threshold display
function updateThresholdDisplay(value) {
    const display = document.getElementById('threshold-display');
    display.textContent = value + '%';
    
    // Color coding based on threshold value
    display.className = 'badge badge-';
    if (value < 50) {
        display.className += 'danger';
    } else if (value < 75) {
        display.className += 'warning';
    } else {
        display.className += 'success';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Customer quiz configuration form handling
    $('.customer-quiz-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const submitButton = $(this).find('button[type="submit"]');
        const originalText = submitButton.text();
        
        // Show loading state
        submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i><?= __('Saving...') ?>');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            success: function(response) {
                // Show success message
                $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                  '<i class="fas fa-check-circle me-2"></i><?= __('Customer quiz configuration saved successfully!') ?>' +
                  '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                  '</div>').insertBefore('.customer-quiz-form').hide().slideDown();
                
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    $('.alert-success').slideUp(function() {
                        $(this).remove();
                    });
                }, 5000);
            },
            error: function(xhr, status, error) {
                // Show error message
                $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                  '<i class="fas fa-exclamation-triangle me-2"></i><?= __('Error saving configuration. Please try again.') ?>' +
                  '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                  '</div>').insertBefore('.customer-quiz-form').hide().slideDown();
            },
            complete: function() {
                // Restore button state
                submitButton.prop('disabled', false).html(originalText);
            }
        });
    });

    // Quiz type toggles - show/hide dependent options
    $('#akinator-enabled').on('change', function() {
        if ($(this).is(':checked')) {
            console.log('Akinator-style quiz enabled');
            // Could show additional akinator-specific options in future
        }
    });

    $('#ai-enabled').on('change', function() {
        if ($(this).is(':checked')) {
            console.log('AI assistance enabled');
            // Could show AI configuration options in future
        }
    });

    // Real-time validation for confidence threshold
    $('#confidence-threshold').on('input', function() {
        updateThresholdDisplay($(this).val());
    });

    // Max results validation
    $('#max-results').on('change', function() {
        const value = parseInt($(this).val());
        if (value < 1) {
            $(this).val(1);
        } else if (value > 50) {
            $(this).val(50);
        }
    });

    // Session timeout validation
    $('#session-timeout').on('change', function() {
        const value = parseInt($(this).val());
        if (value < 5) {
            $(this).val(5);
        } else if (value > 60) {
            $(this).val(60);
        }
    });

    // Cache duration validation
    $('#cache-duration').on('change', function() {
        const value = parseInt($(this).val());
        if (value < 1) {
            $(this).val(1);
        } else if (value > 168) {
            $(this).val(168);
        }
    });
    
    // Initialize all features
    initializeTooltips();
    
    console.log('Customer Quiz Configuration initialized successfully');
});
</script>