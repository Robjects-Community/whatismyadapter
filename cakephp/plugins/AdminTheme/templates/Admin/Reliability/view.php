<?php
$this->assign('title', __('Reliability Details'));
$this->Html->css('willow-admin', ['block' => true]);

// Helper function to format field names
$formatFieldName = function($fieldName) {
    return __(ucwords(str_replace('_', ' ', $fieldName)));
};

// Helper function to get score badge class (expects 0-1 range)
$getScoreBadgeClass = function($score) {
    if ($score >= 0.9) return 'success';
    if ($score >= 0.7) return 'info'; 
    if ($score >= 0.5) return 'warning';
    return 'danger';
};

// Helper function to scale score to 5.0 range
$scaleScore = function($score) {
    return $score * 5.0;
};
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><?= $this->Html->link(__('Admin'), ['controller' => 'Dashboard', 'action' => 'index']) ?></li>
        <li class="breadcrumb-item"><?= $this->Html->link(__('Products'), ['controller' => 'Products', 'action' => 'index']) ?></li>
        <li class="breadcrumb-item active" aria-current="page"><?= __('Reliability Details') ?></li>
    </ol>
</nav>

<!-- Header -->
<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('Reliability Analysis') ?></h3>
            <div class="actions">
                <?= $this->Html->link(
                    '<i class="fas fa-arrow-left"></i> ' . __('Back to Products'),
                    ['controller' => 'Products', 'action' => 'index'],
                    ['class' => 'btn btn-secondary', 'escape' => false]
                ) ?>
                <?= $this->Form->postLink(
                    '<i class="fas fa-sync-alt"></i> ' . __('Recalculate'),
                    ['action' => 'recalc', $model, $id],
                    [
                        'class' => 'btn btn-primary',
                        'escape' => false,
                        'confirm' => __('Are you sure you want to recalculate reliability scores?')
                    ]
                ) ?>
                <?= $this->Form->postLink(
                    '<i class="fas fa-shield-alt"></i> ' . __('Verify Checksums'),
                    ['action' => 'verifyChecksums', $model, $id],
                    [
                        'class' => 'btn btn-info',
                        'escape' => false,
                        'confirm' => __('Verify the integrity of all reliability logs for this item?')
                    ]
                ) ?>
            </div>
        </div>
    </div>
</div>

<!-- Entity Info -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4><?= __('Product Information') ?></h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5><?= h($entity->title) ?></h5>
                        <p class="text-muted mb-1">
                            <strong><?= __('Manufacturer:') ?></strong> <?= h($entity->manufacturer) ?>
                        </p>
                        <p class="text-muted mb-1">
                            <strong><?= __('Model Number:') ?></strong> <?= h($entity->model_number) ?>
                        </p>
                        <p class="text-muted mb-0">
                            <strong><?= __('ID:') ?></strong> <code><?= h($entity->id) ?></code>
                        </p>
                    </div>
                    <?php if ($entity->image): ?>
                    <div class="col-md-4 text-right">
                        <img src="<?= h($entity->image) ?>" alt="<?= h($entity->alt_text) ?>" 
                             class="img-thumbnail" style="max-width: 150px; height: auto;">
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reliability Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4><?= __('Overall Reliability Score') ?></h4>
            </div>
            <div class="card-body">
                <?php if ($reliabilitySummary): ?>
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <div class="display-4">
                                <span class="badge badge-<?= $getScoreBadgeClass($reliabilitySummary->total_score) ?> p-3">
                                    <?= number_format($scaleScore($reliabilitySummary->total_score), 2) ?>/5.0
                                </span>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong><?= __('Completeness Score:') ?></strong> <?= number_format($reliabilitySummary->completeness_percent, 1) ?>%</p>
                                    <p><strong><?= __('Last Updated:') ?></strong> <?= $reliabilitySummary->modified->format('M j, Y H:i:s') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong><?= __('Fields Analyzed:') ?></strong> <?= number_format($reliabilitySummary->total_fields) ?></p>
                                    <p><strong><?= __('Checksum:') ?></strong> <code class="small"><?= substr(h($reliabilitySummary->checksum_sha256), 0, 16) ?>...</code></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <?= __('No reliability data available. Click "Recalculate" to generate scores.') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Field-Level Breakdown -->
<?php if (!empty($fieldsData)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><?= __('Field-Level Analysis') ?></h4>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-info" id="toggleFieldDetails">
                        <i class="fas fa-list"></i> <?= __('Show All Fields') ?>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#fieldExplanationModal">
                        <i class="fas fa-question-circle"></i> <?= __('Field Guide') ?>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Current Field Scores -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?= __('Field Name') ?></th>
                                <th><?= __('Score') ?></th>
                                <th><?= __('Weight') ?></th>
                                <th><?= __('Impact') ?></th>
                                <th><?= __('Status') ?></th>
                                <th><?= __('Last Updated') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fieldsData as $field): ?>
                            <?php 
                            $fieldValue = $entity->get($field->field);
                            $isEmpty = empty($fieldValue);
                            $isCritical = in_array($field->field, ['technical_specifications', 'testing_standard', 'certifying_organization', 'numeric_rating']);
                            ?>
                            <tr class="<?= $isEmpty && $isCritical ? 'table-warning' : '' ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($isCritical): ?>
                                            <i class="fas fa-exclamation-triangle text-warning mr-2" title="Critical verification field"></i>
                                        <?php endif; ?>
                                        <div>
                                            <strong 
                                                class="field-tooltip" 
                                                data-toggle="tooltip" 
                                                data-placement="top" 
                                                data-html="true"
                                                title="<?= h($field->notes) ?><?= $isEmpty ? '<br><strong>Current Value:</strong> <span class=\"text-danger\">Empty</span>' : '<br><strong>Current Value:</strong> ' . h(substr(strip_tags($fieldValue), 0, 50)) . (strlen(strip_tags($fieldValue)) > 50 ? '...' : '') ?>"
                                            >
                                                <?= $formatFieldName($field->field) ?>
                                                <?php if ($isCritical): ?>
                                                    <span class="badge badge-warning badge-sm ml-1">Critical</span>
                                                <?php endif; ?>
                                            </strong><br>
                                            <small class="text-muted">
                                                <code><?= h($field->field) ?></code>
                                                <?php if ($isEmpty): ?>
                                                    <span class="text-danger ml-2"><i class="fas fa-times"></i> Missing</span>
                                                <?php else: ?>
                                                    <span class="text-success ml-2"><i class="fas fa-check"></i> Present</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $getScoreBadgeClass($field->score) ?> score-tooltip"
                                          data-toggle="tooltip" 
                                          data-placement="top" 
                                          title="Raw Score: <?= number_format($field->score, 3) ?> / 1.0<br>Scaled Score: <?= number_format($field->score * 5, 2) ?> / 5.0"
                                          data-html="true">
                                        <?= number_format($scaleScore($field->score), 2) ?>/5.0
                                    </span>
                                </td>
                                <td>
                                    <span class="weight-tooltip" 
                                          data-toggle="tooltip" 
                                          data-placement="top" 
                                          title="This field contributes <?= number_format($field->weight * 100, 1) ?>% to the overall reliability score"
                                          data-html="true">
                                        <?= number_format($field->weight, 3) ?>
                                        <small class="text-muted">(<?= number_format($field->weight * 100, 1) ?>%)</small>
                                    </span>
                                </td>
                                <td>
                                    <?php $impact = $field->score * $field->weight * 5; ?>
                                    <span class="impact-tooltip" 
                                          data-toggle="tooltip" 
                                          data-placement="top" 
                                          title="This field adds <?= number_format($impact, 3) ?> points to the total score out of 5.0"
                                          data-html="true">
                                        +<?= number_format($impact, 3) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($field->score === 0.0 && $isCritical): ?>
                                        <span class="badge badge-danger" title="Critical field missing - severely impacts reliability">
                                            <i class="fas fa-exclamation-circle"></i> Critical Issue
                                        </span>
                                    <?php elseif ($field->score === 0.0): ?>
                                        <span class="badge badge-warning" title="Field is empty">
                                            <i class="fas fa-minus-circle"></i> Missing
                                        </span>
                                    <?php elseif ($field->score < 0.5): ?>
                                        <span class="badge badge-warning" title="Field needs improvement">
                                            <i class="fas fa-exclamation-triangle"></i> Needs Work
                                        </span>
                                    <?php elseif ($field->score < 0.9): ?>
                                        <span class="badge badge-info" title="Field is adequate">
                                            <i class="fas fa-check-circle"></i> Good
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success" title="Field is excellent">
                                            <i class="fas fa-star"></i> Excellent
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $field->modified->format('M j, Y') ?><br>
                                    <small class="text-muted"><?= $field->modified->format('H:i:s') ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- All Fields Overview (Initially Hidden) -->
                <div id="allFieldsOverview" class="mt-4" style="display: none;">
                    <h5><?= __('Complete Field Analysis') ?></h5>
                    <div class="row">
                        <?php 
                        $allFields = [
                            'technical_specifications' => ['weight' => 0.25, 'category' => 'Critical Verification', 'description' => 'JSON formatted technical specifications proving product authenticity'],
                            'testing_standard' => ['weight' => 0.20, 'category' => 'Critical Verification', 'description' => 'Industry testing standard (ISO, ANSI, IEC, etc.) for product certification'],
                            'certifying_organization' => ['weight' => 0.15, 'category' => 'Critical Verification', 'description' => 'Third-party certification body (UL, FCC, CE, etc.) that verified the product'],
                            'numeric_rating' => ['weight' => 0.10, 'category' => 'Critical Verification', 'description' => 'Quantified performance rating or measurement from testing'],
                            'title' => ['weight' => 0.08, 'category' => 'Basic Information', 'description' => 'Product title - clear and descriptive naming'],
                            'description' => ['weight' => 0.08, 'category' => 'Basic Information', 'description' => 'Detailed product description with adequate length and quality'],
                            'manufacturer' => ['weight' => 0.05, 'category' => 'Basic Information', 'description' => 'Product manufacturer or brand name'],
                            'model_number' => ['weight' => 0.03, 'category' => 'Basic Information', 'description' => 'Specific model or part number for product identification'],
                            'price' => ['weight' => 0.03, 'category' => 'Basic Information', 'description' => 'Product price in valid numeric format'],
                            'currency' => ['weight' => 0.01, 'category' => 'Basic Information', 'description' => 'Currency code (USD, EUR, GBP, etc.)'],
                            'image' => ['weight' => 0.01, 'category' => 'Basic Information', 'description' => 'Product image with valid format and path'],
                            'alt_text' => ['weight' => 0.01, 'category' => 'Basic Information', 'description' => 'Descriptive alt text for product image accessibility']
                        ];
                        
                        $categories = [];
                        foreach ($allFields as $fieldName => $info) {
                            $categories[$info['category']][] = ['name' => $fieldName, 'info' => $info];
                        }
                        ?>
                        
                        <?php foreach ($categories as $categoryName => $categoryFields): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card border-<?= $categoryName === 'Critical Verification' ? 'danger' : 'secondary' ?>">
                                <div class="card-header bg-<?= $categoryName === 'Critical Verification' ? 'danger' : 'secondary' ?> text-white">
                                    <h6 class="mb-0">
                                        <?= $categoryName === 'Critical Verification' ? '<i class="fas fa-shield-alt"></i>' : '<i class="fas fa-info-circle"></i>' ?>
                                        <?= h($categoryName) ?>
                                        <small class="float-right">
                                            <?= number_format(array_sum(array_column($categoryFields, 'info.weight')) * 100, 1) ?>% weight
                                        </small>
                                    </h6>
                                </div>
                                <div class="card-body p-2">
                                    <?php foreach ($categoryFields as $fieldData): ?>
                                    <?php 
                                    $fieldName = $fieldData['name'];
                                    $fieldInfo = $fieldData['info'];
                                    $hasScore = isset($fieldsData[$fieldName]);
                                    $score = $hasScore ? $fieldsData[$fieldName]->score : 0;
                                    $fieldValue = $entity->get($fieldName);
                                    $isEmpty = empty($fieldValue);
                                    ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <strong class="<?= $isEmpty && $categoryName === 'Critical Verification' ? 'text-danger' : '' ?>">
                                                    <?= $formatFieldName($fieldName) ?>
                                                    <?php if ($isEmpty && $categoryName === 'Critical Verification'): ?>
                                                        <i class="fas fa-exclamation-circle text-danger ml-1" title="Critical field missing!"></i>
                                                    <?php endif; ?>
                                                </strong>
                                                <br>
                                                <small class="text-muted"><?= h($fieldInfo['description']) ?></small>
                                                <br>
                                                <small>
                                                    <strong>Weight:</strong> <?= number_format($fieldInfo['weight'] * 100, 1) ?>% |
                                                    <strong>Status:</strong> 
                                                    <?php if ($isEmpty): ?>
                                                        <span class="text-danger">Missing</span>
                                                    <?php else: ?>
                                                        <span class="text-success">Present</span>
                                                        <?php if (is_string($fieldValue) && strlen($fieldValue) > 30): ?>
                                                            (<em><?= h(substr(strip_tags($fieldValue), 0, 30)) ?>...</em>)
                                                        <?php elseif (is_string($fieldValue)): ?>
                                                            (<em><?= h($fieldValue) ?></em>)
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="text-right">
                                                <?php if ($hasScore): ?>
                                                    <span class="badge badge-<?= $getScoreBadgeClass($score) ?>">
                                                        <?= number_format($scaleScore($score), 1) ?>/5
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">N/A</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Field Explanation Modal -->
<div class="modal fade" id="fieldExplanationModal" tabindex="-1" role="dialog" aria-labelledby="fieldExplanationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fieldExplanationModalLabel">
                    <i class="fas fa-info-circle"></i> <?= __('Reliability Field Guide') ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted"><?= __('Understanding how each field contributes to the overall reliability score:') ?></p>
                
                <div class="accordion" id="fieldGuideAccordion">
                    <!-- Critical Verification Fields -->
                    <div class="card">
                        <div class="card-header" id="criticalFieldsHeading">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-danger" type="button" data-toggle="collapse" data-target="#criticalFieldsCollapse" aria-expanded="true" aria-controls="criticalFieldsCollapse">
                                    <i class="fas fa-shield-alt"></i> <?= __('Critical Verification Fields (70% Weight)') ?>
                                    <i class="fas fa-chevron-down float-right"></i>
                                </button>
                            </h6>
                        </div>
                        <div id="criticalFieldsCollapse" class="collapse show" aria-labelledby="criticalFieldsHeading" data-parent="#fieldGuideAccordion">
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong><?= __('These fields are essential for product verification and carry the highest weight.') ?></strong>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-cogs"></i> <?= __('Technical Specifications (25%)') ?></h6>
                                        <p class="small text-muted"><?= __('Structured JSON data containing detailed technical parameters, performance metrics, and specifications that prove product authenticity and capabilities.') ?></p>
                                        
                                        <h6><i class="fas fa-certificate"></i> <?= __('Testing Standard (20%)') ?></h6>
                                        <p class="small text-muted"><?= __('Industry-recognized testing standards (ISO, ANSI, IEC, ASTM, etc.) under which the product was evaluated and certified.') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-building"></i> <?= __('Certifying Organization (15%)') ?></h6>
                                        <p class="small text-muted"><?= __('Third-party certification bodies (UL, FCC, CE, CSA, etc.) that independently verified and certified the product.') ?></p>
                                        
                                        <h6><i class="fas fa-tachometer-alt"></i> <?= __('Numeric Rating (10%)') ?></h6>
                                        <p class="small text-muted"><?= __('Quantified performance ratings, test scores, or measurements that provide objective product evaluation data.') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Basic Information Fields -->
                    <div class="card">
                        <div class="card-header" id="basicFieldsHeading">
                            <h6 class="mb-0">
                                <button class="btn btn-link text-secondary" type="button" data-toggle="collapse" data-target="#basicFieldsCollapse" aria-expanded="false" aria-controls="basicFieldsCollapse">
                                    <i class="fas fa-info-circle"></i> <?= __('Basic Information Fields (30% Weight)') ?>
                                    <i class="fas fa-chevron-down float-right"></i>
                                </button>
                            </h6>
                        </div>
                        <div id="basicFieldsCollapse" class="collapse" aria-labelledby="basicFieldsHeading" data-parent="#fieldGuideAccordion">
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong><?= __('These fields provide essential product information and help establish basic credibility.') ?></strong>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-tag"></i> <?= __('Title (8%)') ?></h6>
                                        <p class="small text-muted"><?= __('Clear, descriptive product name that accurately represents the item.') ?></p>
                                        
                                        <h6><i class="fas fa-align-left"></i> <?= __('Description (8%)') ?></h6>
                                        <p class="small text-muted"><?= __('Comprehensive product description with adequate detail and quality content.') ?></p>
                                        
                                        <h6><i class="fas fa-industry"></i> <?= __('Manufacturer (5%)') ?></h6>
                                        <p class="small text-muted"><?= __('Product manufacturer, brand name, or company responsible for production.') ?></p>
                                        
                                        <h6><i class="fas fa-barcode"></i> <?= __('Model Number (3%)') ?></h6>
                                        <p class="small text-muted"><?= __('Specific model identifier, part number, or SKU for precise product identification.') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-dollar-sign"></i> <?= __('Price (3%)') ?></h6>
                                        <p class="small text-muted"><?= __('Valid product price in proper numeric format.') ?></p>
                                        
                                        <h6><i class="fas fa-coins"></i> <?= __('Currency (1%)') ?></h6>
                                        <p class="small text-muted"><?= __('Currency designation (USD, EUR, GBP, etc.) for price clarity.') ?></p>
                                        
                                        <h6><i class="fas fa-image"></i> <?= __('Image (1%)') ?></h6>
                                        <p class="small text-muted"><?= __('Product image with valid format, path, and appropriate resolution.') ?></p>
                                        
                                        <h6><i class="fas fa-eye"></i> <?= __('Alt Text (1%)') ?></h6>
                                        <p class="small text-muted"><?= __('Descriptive alternative text for product image accessibility compliance.') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <h6><?= __('Scoring Logic') ?></h6>
                <ul class="small text-muted">
                    <li><?= __('Each field is scored from 0.0 to 1.0 based on data quality and completeness') ?></li>
                    <li><?= __('Missing critical verification fields receive 0.0 scores, severely impacting overall reliability') ?></li>
                    <li><?= __('Weighted scores are summed and displayed on a 5.0 scale for easy interpretation') ?></li>
                    <li><?= __('Products scoring below 2.5/5.0 are flagged as potentially unverified or unreliable') ?></li>
                    <li><?= __('Checksums ensure data integrity and detect unauthorized modifications') ?></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= __('Close') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip({
        html: true,
        container: 'body'
    });
    
    // Toggle all fields overview
    $('#toggleFieldDetails').click(function() {
        const $overview = $('#allFieldsOverview');
        const $button = $(this);
        
        if ($overview.is(':visible')) {
            $overview.slideUp();
            $button.html('<i class="fas fa-list"></i> <?= __('Show All Fields') ?>');
            $button.removeClass('btn-info').addClass('btn-outline-info');
        } else {
            $overview.slideDown();
            $button.html('<i class="fas fa-list-ul"></i> <?= __('Hide Field Details') ?>');
            $button.removeClass('btn-outline-info').addClass('btn-info');
        }
    });
    
    // Handle accordion chevron rotation
    $('.collapse').on('show.bs.collapse', function() {
        $(this).prev('.card-header').find('.fa-chevron-down').addClass('rotate-180');
    });
    
    $('.collapse').on('hide.bs.collapse', function() {
        $(this).prev('.card-header').find('.fa-chevron-down').removeClass('rotate-180');
    });
});
</script>

<style>
.rotate-180 {
    transform: rotate(180deg);
    transition: transform 0.3s ease;
}

.field-tooltip:hover {
    cursor: help;
}

.score-tooltip, .weight-tooltip, .impact-tooltip {
    cursor: help;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.badge-sm {
    font-size: 0.65em;
}

#allFieldsOverview .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

#allFieldsOverview .border-bottom:last-child {
    border-bottom: none !important;
}

.modal-lg {
    max-width: 900px;
}
</style>

<!-- History/Audit Logs -->
<?php 
$logsCollection = $logs;
if (is_object($logs) && method_exists($logs, 'all')) {
    $logsCollection = $logs->all();
}
?>
<?php if (!empty($logsCollection) && count($logsCollection) > 0): ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4><?= __('Reliability History') ?></h4>
                <small class="text-muted"><?= __('Showing most recent 20 entries') ?></small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th><?= __('Date') ?></th>
                                <th><?= __('Score Change') ?></th>
                                <th><?= __('Source') ?></th>
                                <th><?= __('Actor') ?></th>
                                <th><?= __('Message') ?></th>
                                <th><?= __('Checksum') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logsCollection as $log): ?>
                            <tr>
                                <td>
                                    <?= $log->created->format('M j, Y') ?><br>
                                    <small class="text-muted"><?= $log->created->format('H:i:s') ?></small>
                                </td>
                                <td>
                                    <?php
                                    $scoreDiff = $log->to_total_score - $log->from_total_score;
                                    $changeClass = $scoreDiff > 0 ? 'success' : ($scoreDiff < 0 ? 'danger' : 'secondary');
                                    $changeIcon = $scoreDiff > 0 ? 'arrow-up' : ($scoreDiff < 0 ? 'arrow-down' : 'minus');
                                    ?>
                                    <span class="text-<?= $changeClass ?>">
                                        <i class="fas fa-<?= $changeIcon ?>"></i>
                                        <?= number_format($log->from_total_score, 2) ?> → <?= number_format($log->to_total_score, 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-outline-secondary"><?= h($log->source) ?></span>
                                </td>
                                <td>
                                    <?php if ($log->actor_user_id): ?>
                                        <i class="fas fa-user"></i> User #<?= h($log->actor_user_id) ?>
                                    <?php elseif ($log->actor_service): ?>
                                        <i class="fas fa-cogs"></i> <?= h($log->actor_service) ?>
                                    <?php else: ?>
                                        <i class="fas fa-robot"></i> System
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= h($log->message) ?></small>
                                </td>
                                <td>
                                    <code class="small"><?= substr(h($log->checksum_sha256), 0, 8) ?>...</code>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <?= __('No reliability history available for this item.') ?>
        </div>
    </div>
</div>
<?php endif; ?>
