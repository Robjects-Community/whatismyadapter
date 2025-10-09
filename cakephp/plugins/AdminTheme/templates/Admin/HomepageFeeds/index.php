<?php
/**
 * @var \App\View\AppView $this
 * @var array $feedOptions
 * @var array $feedStats
 */
$this->extend('/layout/default');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Homepage Feeds Configuration</h1>
                    <p class="text-muted">Manage which content feeds appear on the homepage</p>
                </div>
                <div class="btn-group">
                    <?= $this->Html->link(
                        '<i class="bi bi-eye"></i> Preview Homepage',
                        ['action' => 'preview'],
                        ['class' => 'btn btn-outline-primary', 'escape' => false]
                    ) ?>
                    <?= $this->Html->link(
                        '<i class="bi bi-arrow-clockwise"></i> Reset to Defaults',
                        ['action' => 'reset'],
                        [
                            'class' => 'btn btn-outline-warning',
                            'escape' => false,
                            'confirm' => __('Are you sure you want to reset all homepage feeds to default settings?')
                        ]
                    ) ?>
                </div>
            </div>

            <div class="row">
                <!-- Feed Configuration -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="h5 mb-0">
                                <i class="bi bi-toggles"></i> Feed Configuration
                            </h2>
                        </div>
                        <div class="card-body">
                            <?= $this->Form->create(null, [
                                'url' => ['action' => 'configure'],
                                'type' => 'post'
                            ]) ?>
                            
                            <div class="feed-options">
                                <?php foreach ($feedOptions as $key => $option): ?>
                                <div class="feed-option mb-4">
                                    <div class="card <?= $option['enabled'] ? 'border-success' : 'border-secondary' ?>">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-1">
                                                    <div class="form-check form-switch">
                                                        <?= $this->Form->checkbox($key, [
                                                            'checked' => $option['enabled'],
                                                            'class' => 'form-check-input feed-toggle',
                                                            'data-target' => $key . '-card',
                                                            'id' => $key . '_checkbox'
                                                        ]) ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="<?= $key ?>_checkbox" class="form-check-label fw-bold">
                                                        <?= h($option['label']) ?>
                                                    </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <p class="text-muted mb-0 small">
                                                        <?= h($option['description']) ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-3 text-end">
                                                    <?php if (isset($feedStats[str_replace(['_articles', '_galleries'], ['', '_galleries'], $key)])): ?>
                                                    <?php $statKey = str_replace(['_articles', '_galleries'], ['', '_galleries'], $key); ?>
                                                    <div class="feed-stats">
                                                        <?php if ($key === 'featured_articles'): ?>
                                                            <span class="badge bg-warning">
                                                                <?= $feedStats['articles']['featured'] ?? 0 ?> featured
                                                            </span>
                                                        <?php elseif ($key === 'recent_articles'): ?>
                                                            <span class="badge bg-info">
                                                                <?= $feedStats['articles']['recent'] ?? 0 ?> recent
                                                            </span>
                                                        <?php elseif ($key === 'products'): ?>
                                                            <span class="badge bg-success">
                                                                <?= $feedStats['products']['total'] ?? 0 ?> active
                                                            </span>
                                                        <?php elseif ($key === 'image_galleries'): ?>
                                                            <span class="badge bg-primary">
                                                                <?= $feedStats['galleries']['total'] ?? 0 ?> published
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <?= $this->Html->link(
                                                '<i class="bi bi-arrow-left"></i> Back to Admin',
                                                ['controller' => 'Dashboard', 'action' => 'index'],
                                                ['class' => 'btn btn-outline-secondary', 'escape' => false]
                                            ) ?>
                                        </div>
                                        <div>
                                            <?= $this->Form->button(
                                                '<i class="bi bi-check-lg"></i> Save Configuration',
                                                [
                                                    'type' => 'submit',
                                                    'class' => 'btn btn-primary',
                                                    'escape' => false
                                                ]
                                            ) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?= $this->Form->end() ?>
                        </div>
                    </div>
                </div>

                <!-- Statistics Sidebar -->
                <div class="col-lg-4">
                    <!-- Overall Statistics -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h2 class="h5 mb-0">
                                <i class="bi bi-graph-up"></i> Content Statistics
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            <i class="bi bi-newspaper"></i> Articles
                                        </span>
                                        <span class="fw-bold text-primary">
                                            <?= $feedStats['articles']['total'] ?? 0 ?>
                                        </span>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: <?= min(100, ($feedStats['articles']['total'] ?? 0) * 2) ?>%"></div>
                                    </div>
                                </div>

                                <div class="stat-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            <i class="bi bi-box-seam"></i> Products
                                        </span>
                                        <span class="fw-bold text-success">
                                            <?= $feedStats['products']['total'] ?? 0 ?>
                                        </span>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?= min(100, ($feedStats['products']['total'] ?? 0) * 5) ?>%"></div>
                                    </div>
                                </div>

                                <div class="stat-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            <i class="bi bi-images"></i> Galleries
                                        </span>
                                        <span class="fw-bold text-info">
                                            <?= $feedStats['galleries']['total'] ?? 0 ?>
                                        </span>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: <?= min(100, ($feedStats['galleries']['total'] ?? 0) * 10) ?>%"></div>
                                    </div>
                                </div>

                                <div class="stat-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            <i class="bi bi-tags"></i> Tags
                                        </span>
                                        <span class="fw-bold text-warning">
                                            <?= $feedStats['tags']['total'] ?? 0 ?>
                                        </span>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: <?= min(100, ($feedStats['tags']['total'] ?? 0) * 3) ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h2 class="h5 mb-0">
                                <i class="bi bi-clock-history"></i> Recent Activity
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="activity-list">
                                <div class="activity-item d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Recent Articles (30 days)</span>
                                    <span class="badge bg-primary"><?= $feedStats['articles']['recent'] ?? 0 ?></span>
                                </div>
                                <div class="activity-item d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Recent Products (30 days)</span>
                                    <span class="badge bg-success"><?= $feedStats['products']['recent'] ?? 0 ?></span>
                                </div>
                                <div class="activity-item d-flex justify-content-between mb-2">
                                    <span class="text-muted small">Recent Galleries (30 days)</span>
                                    <span class="badge bg-info"><?= $feedStats['galleries']['recent'] ?? 0 ?></span>
                                </div>
                                <div class="activity-item d-flex justify-content-between mb-2">
                                    <span class="text-muted small">High Reliability Products</span>
                                    <span class="badge bg-warning"><?= $feedStats['products']['high_reliability'] ?? 0 ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Card -->
                    <div class="card border-info">
                        <div class="card-header bg-info bg-opacity-10 border-info">
                            <h2 class="h5 mb-0 text-info">
                                <i class="bi bi-info-circle"></i> Feed Information
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="help-content">
                                <h6 class="small fw-bold text-muted mb-2">How Feeds Work:</h6>
                                <ul class="list-unstyled small text-muted">
                                    <li class="mb-1">• <strong>Featured Articles:</strong> Shows promoted articles and those with rating ≥ 4.5</li>
                                    <li class="mb-1">• <strong>Recent Articles:</strong> Displays latest 6 published articles</li>
                                    <li class="mb-1">• <strong>Products:</strong> Shows latest products sorted by reliability score</li>
                                    <li class="mb-1">• <strong>Image Galleries:</strong> Displays recent published galleries</li>
                                    <li class="mb-1">• <strong>Static Pages:</strong> Custom pages (coming soon)</li>
                                </ul>
                                
                                <div class="mt-3">
                                    <small class="text-info">
                                        <i class="bi bi-lightbulb"></i> 
                                        Changes take effect immediately. Use "Preview Homepage" to see results.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for Feed Configuration -->
<style>
.feed-option .card {
    transition: all 0.3s ease;
}

.feed-option .card.border-success {
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
}

.feed-option .card.border-secondary {
    opacity: 0.7;
}

.feed-toggle {
    width: 3rem;
    height: 1.5rem;
}

.stats-grid .stat-item {
    padding: 0.5rem 0;
}

.activity-list {
    max-height: 200px;
    overflow-y: auto;
}

.help-content ul li {
    padding-left: 0.5rem;
}

@media (max-width: 768px) {
    .col-md-1, .col-md-3, .col-md-5, .col-md-3 {
        margin-bottom: 0.5rem;
    }
    
    .feed-option .row .col-md-3:last-child {
        text-align: left !important;
    }
}
</style>

<!-- JavaScript for Interactive Features -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle feed toggle switches
    const feedToggles = document.querySelectorAll('.feed-toggle');
    
    feedToggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const card = this.closest('.feed-option').querySelector('.card');
            
            if (this.checked) {
                card.classList.remove('border-secondary');
                card.classList.add('border-success');
            } else {
                card.classList.remove('border-success');
                card.classList.add('border-secondary');
            }
        });
    });
    
    // Auto-save functionality (optional)
    const form = document.querySelector('form');
    let saveTimeout;
    
    feedToggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            clearTimeout(saveTimeout);
            const saveButton = document.querySelector('button[type="submit"]');
            saveButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Auto-saving...';
            saveButton.disabled = true;
            
            saveTimeout = setTimeout(function() {
                saveButton.innerHTML = '<i class="bi bi-check-lg"></i> Save Configuration';
                saveButton.disabled = false;
            }, 1000);
        });
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>