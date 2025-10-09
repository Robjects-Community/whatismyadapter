<?php
/**
 * Forms Dashboard View
 * @var \App\View\AppView $this
 * @var array $forms_stats
 */

$this->assign('title', __('Forms Dashboard'));
$this->start('breadcrumb');
echo $this->Html->link(__('Dashboard'), ['controller' => 'Dashboard', 'action' => 'index']);
echo ' / ' . __('Forms Dashboard');
$this->end();
?>
<?= $this->Html->css('AdminTheme.forms', ['block' => true]) ?>
<?= $this->Html->css('AdminTheme.dashboard', ['block' => true]) ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800">
                <i class="fas fa-tachometer-alt text-primary me-2"></i>
                <?= __('Forms Dashboard') ?>
            </h1>
            <p class="mb-0 text-muted"><?= __('Manage your product forms, submissions, and analytics') ?></p>
        </div>
        <div class="d-flex gap-2">
            <?= $this->Html->link(
                '<i class="fas fa-plus me-1"></i>' . __('New Form'),
                ['action' => 'formsFields'],
                ['class' => 'btn btn-primary', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-download me-1"></i>' . __('Export Data'),
                ['action' => 'export'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Statistics Cards Row -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 hover-shadow">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                <?= __('Total Forms') ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800 counter-animate">
                                                <?= $forms_stats['total_forms'] ?? 0 ?>
                                            </div>
                                            <div class="mt-2">
                                                <?= $this->Html->link(
                                                    __('View All') . ' <i class="fas fa-arrow-right ms-1"></i>',
                                                    ['action' => 'formsFields'],
                                                    ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false]
                                                ) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-wpforms fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 hover-shadow">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                <?= __('Active Forms') ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800 counter-animate">
                                                <?= $forms_stats['active_forms'] ?? 0 ?>
                                            </div>
                                            <div class="mt-2">
                                                <span class="badge badge-success">
                                                    <i class="fas fa-arrow-up me-1"></i>
                                                    <?= __('12% increase') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2 hover-shadow">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                <?= __('Total Submissions') ?>
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800 counter-animate">
                                                        <?= $forms_stats['total_submissions'] ?? 0 ?>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div class="progress-bar bg-info" role="progressbar" 
                                                             style="width: <?= isset($forms_stats['submission_rate']) ? $forms_stats['submission_rate'] : 50 ?>%" 
                                                             aria-valuenow="<?= isset($forms_stats['submission_rate']) ? $forms_stats['submission_rate'] : 50 ?>" 
                                                             aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 hover-shadow">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                <?= __('Customer Quiz') ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800 counter-animate">
                                                <?= $forms_stats['quiz_sessions'] ?? 0 ?>
                                            </div>
                                            <div class="mt-2">
                                                <?= $this->Html->link(
                                                    '<i class="fas fa-cog me-1"></i>' . __('Configure'),
                                                    ['action' => 'formsCustomerQuiz'],
                                                    ['class' => 'btn btn-sm btn-outline-warning', 'escape' => false]
                                                ) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Dashboard Content -->
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-lg-8">
                            <!-- Recent Activity Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-clock me-2"></i><?= __('Recent Activity') ?>
                                    </h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow">
                                            <div class="dropdown-header"><?= __('Filter Options:') ?></div>
                                            <a class="dropdown-item" href="#"><?= __('Today') ?></a>
                                            <a class="dropdown-item" href="#"><?= __('This Week') ?></a>
                                            <a class="dropdown-item" href="#"><?= __('This Month') ?></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-primary"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title"><?= __('New form submission received') ?></h6>
                                                <p class="timeline-text text-muted">
                                                    <?= __('Product Registration Form - Electronics Category') ?>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i><?= __('2 minutes ago') ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title"><?= __('Form activated') ?></h6>
                                                <p class="timeline-text text-muted">
                                                    <?= __('Customer Feedback Survey is now live') ?>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i><?= __('1 hour ago') ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-info"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title"><?= __('New quiz form created') ?></h6>
                                                <p class="timeline-text text-muted">
                                                    <?= __('Product Knowledge Quiz - Draft saved') ?>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i><?= __('3 hours ago') ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-warning"></div>
                                            <div class="timeline-content">
                                                <h6 class="timeline-title"><?= __('Form updated') ?></h6>
                                                <p class="timeline-text text-muted">
                                                    <?= __('Contact Information Form - Added new fields') ?>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i><?= __('Yesterday') ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <?= $this->Html->link(
                                            __('View All Activity'),
                                            ['action' => 'activity'],
                                            ['class' => 'btn btn-outline-primary btn-sm']
                                        ) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Forms Performance Chart -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-chart-area me-2"></i><?= __('Forms Performance') ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="formsChart" width="100%" height="30"></canvas>
                                    </div>
                                    <hr>
                                    <div class="row text-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1"><?= __('This Month') ?></div>
                                            <div class="h5 mb-0 text-gray-800"><?= $forms_stats['monthly_submissions'] ?? 0 ?></div>
                                        </div>
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1"><?= __('Avg. Response Time') ?></div>
                                            <div class="h5 mb-0 text-gray-800"><?= $forms_stats['avg_response_time'] ?? '2.3' ?>min</div>
                                        </div>
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1"><?= __('Completion Rate') ?></div>
                                            <div class="h5 mb-0 text-gray-800"><?= $forms_stats['completion_rate'] ?? 85 ?>%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-lg-4">
                            <!-- Quick Actions Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-bolt me-2"></i><?= __('Quick Actions') ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <?= $this->Html->link(
                                            '<i class="fas fa-plus me-2"></i>' . __('Create New Form'),
                                            ['action' => 'formsFields'],
                                            ['class' => 'btn btn-primary', 'escape' => false]
                                        ) ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-question me-2"></i>' . __('Manage Quizzes'),
                                            ['action' => 'formsQuiz'],
                                            ['class' => 'btn btn-info', 'escape' => false]
                                        ) ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-user-check me-2"></i>' . __('Customer Quiz'),
                                            ['action' => 'formsCustomerQuiz'],
                                            ['class' => 'btn btn-warning', 'escape' => false]
                                        ) ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-chart-bar me-2"></i>' . __('View Statistics'),
                                            ['action' => 'formsStats'],
                                            ['class' => 'btn btn-success', 'escape' => false]
                                        ) ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-cog me-2"></i>' . __('Settings'),
                                            ['action' => 'settings'],
                                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                                        ) ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Top Forms Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-trophy me-2"></i><?= __('Top Performing Forms') ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item px-0">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="icon-circle bg-primary">
                                                        <i class="fas fa-wpforms text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?= __('Product Registration') ?></h6>
                                                    <p class="mb-1 text-muted small"><?= __('247 submissions') ?></p>
                                                </div>
                                                <span class="badge bg-success">98%</span>
                                            </div>
                                        </div>
                                        
                                        <div class="list-group-item px-0">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="icon-circle bg-success">
                                                        <i class="fas fa-comment text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?= __('Customer Feedback') ?></h6>
                                                    <p class="mb-1 text-muted small"><?= __('156 submissions') ?></p>
                                                </div>
                                                <span class="badge bg-info">92%</span>
                                            </div>
                                        </div>
                                        
                                        <div class="list-group-item px-0">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="icon-circle bg-info">
                                                        <i class="fas fa-question text-white"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?= __('Product Quiz') ?></h6>
                                                    <p class="mb-1 text-muted small"><?= __('89 submissions') ?></p>
                                                </div>
                                                <span class="badge bg-warning">87%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- System Status Card -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-info-circle me-2"></i><?= __('System Status') ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-sm"><?= __('Forms Configuration') ?></span>
                                            <span class="text-sm font-weight-bold"><?= $forms_stats['completion_rate'] ?? 75 ?>%</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: <?= $forms_stats['completion_rate'] ?? 75 ?>%" 
                                                 aria-valuenow="<?= $forms_stats['completion_rate'] ?? 75 ?>" 
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-sm"><?= __('Database Health') ?></span>
                                            <span class="text-sm font-weight-bold">95%</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-sm"><?= __('Security Status') ?></span>
                                            <span class="text-sm font-weight-bold">100%</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional CSS for enhanced styling -->
<style>
.hover-shadow:hover {
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1) !important;
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.counter-animate {
    transition: all 0.5s ease;
}

.timeline {
    position: relative;
    margin: 0;
    padding: 0;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 2rem;
    height: calc(100% + 0.5rem);
    width: 2px;
    background: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0.25rem;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.timeline-content {
    margin-top: -0.25rem;
}

.timeline-title {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
}

.icon-circle {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-sm {
    height: 0.5rem;
}

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

.chart-area {
    position: relative;
    height: 10rem;
    width: 100%;
}

@media (max-width: 768px) {
    .timeline-item {
        padding-left: 1.5rem;
    }
    
    .timeline-marker {
        width: 1rem;
        height: 1rem;
    }
    
    .timeline-item:not(:last-child):before {
        left: 0.5rem;
    }
}
</style>

<!-- JavaScript for enhanced functionality -->
<?= $this->Html->script('AdminTheme.dashboard', ['block' => true]) ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize counter animations
    function animateCounters() {
        const counters = document.querySelectorAll('.counter-animate');
        counters.forEach(counter => {
            const target = parseInt(counter.textContent);
            const increment = target / 50;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target;
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current);
                }
            }, 30);
        });
    }
    
    // Initialize charts
    function initializeChart() {
        const ctx = document.getElementById('formsChart');
        if (ctx) {
            // Chart.js implementation would go here
            console.log('Forms chart initialized');
        }
    }
    
    // Real-time updates simulation
    function simulateRealTimeUpdates() {
        setInterval(() => {
            // Simulate random activity updates
            const activityItems = document.querySelectorAll('.timeline-item');
            if (activityItems.length > 0) {
                const randomItem = activityItems[Math.floor(Math.random() * activityItems.length)];
                randomItem.style.backgroundColor = 'rgba(78, 115, 223, 0.05)';
                
                setTimeout(() => {
                    randomItem.style.backgroundColor = '';
                }, 2000);
            }
        }, 10000); // Every 10 seconds
    }
    
    // Initialize progress bars animation
    function animateProgressBars() {
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach((bar, index) => {
            const width = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.transition = 'width 1s ease-in-out';
                bar.style.width = width;
            }, index * 200);
        });
    }
    
    // Tooltip initialization
    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Auto-refresh dashboard data
    function setupAutoRefresh() {
        const refreshInterval = 30000; // 30 seconds
        
        setInterval(() => {
            // In a real implementation, this would fetch new data via AJAX
            console.log('Dashboard data refreshed');
            
            // Update timestamp
            const timestamp = new Date().toLocaleTimeString();
            const timestampElement = document.getElementById('last-update');
            if (timestampElement) {
                timestampElement.textContent = `Last updated: ${timestamp}`;
            }
        }, refreshInterval);
    }
    
    // Initialize all dashboard features
    animateCounters();
    initializeChart();
    animateProgressBars();
    initializeTooltips();
    simulateRealTimeUpdates();
    setupAutoRefresh();
    
    // Add last update timestamp
    const headerElement = document.querySelector('.container-fluid h1').parentElement;
    if (headerElement) {
        const timestampDiv = document.createElement('div');
        timestampDiv.innerHTML = '<small id="last-update" class="text-muted">Last updated: ' + new Date().toLocaleTimeString() + '</small>';
        headerElement.appendChild(timestampDiv);
    }
    
    console.log('Forms Dashboard initialized successfully');
});
</script>
</div>