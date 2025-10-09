<?php
$this->assign('title', __('AI Metrics Dashboard'));
$this->Html->css('willow-admin', ['block' => true]);
$this->Html->script('Chart.min', ['block' => true]);
?>

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('AI Metrics Dashboard') ?></h3>
            <p class="text-muted"><?= __('Real-time AI operations monitoring and analytics') ?></p>
            <div class="actions">
                <?= $this->Html->link(
                    '<i class="fas fa-list"></i> ' . __('View All Metrics'),
                    ['action' => 'index'],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-cog"></i> ' . __('AI Settings'),
                    ['controller' => 'Settings', 'action' => 'index', '?' => ['category' => 'AI']],
                    ['class' => 'btn btn-secondary', 'escape' => false]
                ) ?>
                <button id="refreshBtn" class="btn btn-info">
                    <i class="fas fa-sync-alt"></i> <?= __('Refresh') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row" id="metricsCards">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Total API Calls') ?></h5>
                <h2 class="text-primary" id="totalCalls"><?= number_format($totalCalls) ?></h2>
                <small class="text-muted"><?= __('Last 30 days') ?></small>
                <div class="mt-2">
                    <canvas id="callsSparkline" width="100" height="30"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Success Rate') ?></h5>
                <h2 id="successRate" class="<?= $successRate >= 95 ? 'text-success' : ($successRate >= 85 ? 'text-warning' : 'text-danger') ?>">
                    <?= number_format($successRate, 1) ?>%
                </h2>
                <small class="text-muted"><?= __('API Success Rate') ?></small>
                <div class="progress mt-2" style="height: 8px;">
                    <div class="progress-bar <?= $successRate >= 95 ? 'bg-success' : ($successRate >= 85 ? 'bg-warning' : 'bg-danger') ?>" 
                         style="width: <?= $successRate ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Total Cost') ?></h5>
                <h2 class="text-info" id="totalCost">$<?= number_format($totalCost, 2) ?></h2>
                <small class="text-muted"><?= __('Last 30 days') ?></small>
                <div class="mt-2">
                    <?php 
                    $dailyLimit = (float)($this->getRequest()->getAttribute('identity')?->getOriginalData()['AI']['dailyCostLimit'] ?? 50.00);
                    $percentage = $dailyLimit > 0 ? min(100, ($totalCost / $dailyLimit) * 100) : 0;
                    ?>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?= $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success') ?>" 
                             style="width: <?= $percentage ?>%"></div>
                    </div>
                    <small class="text-muted"><?= number_format($percentage, 1) ?>% of daily limit</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Rate Limit') ?></h5>
                <h2 id="rateLimit" class="<?= $currentUsage['remaining'] > 10 ? 'text-success' : 'text-warning' ?>">
                    <?= $currentUsage['current'] ?>/<?= $currentUsage['limit'] ?>
                </h2>
                <small class="text-muted"><?= __('This hour') ?></small>
                <div class="mt-2">
                    <?php $ratePct = $currentUsage['limit'] > 0 ? ($currentUsage['current'] / $currentUsage['limit']) * 100 : 0; ?>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar <?= $ratePct > 80 ? 'bg-danger' : ($ratePct > 60 ? 'bg-warning' : 'bg-success') ?>" 
                             style="width: <?= $ratePct ?>%"></div>
                    </div>
                    <small class="text-muted">Resets at <?= $currentUsage['reset_time'] ?? 'next hour' ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><?= __('API Calls Over Time') ?></h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-timeframe="30d">30 Days</button>
                    <button type="button" class="btn btn-outline-primary" data-timeframe="7d">7 Days</button>
                    <button type="button" class="btn btn-outline-primary" data-timeframe="24h">24 Hours</button>
                    <button type="button" class="btn btn-outline-primary" data-timeframe="1h">1 Hour</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="timeSeriesChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Task Distribution') ?></h5>
            </div>
            <div class="card-body">
                <canvas id="taskTypeChart" width="200" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Task Metrics Table -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Metrics by Task Type') ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="taskMetricsTable">
                        <thead>
                            <tr>
                                <th><?= __('Task Type') ?></th>
                                <th><?= __('Count') ?></th>
                                <th><?= __('Avg Time (ms)') ?></th>
                                <th><?= __('Success Rate') ?></th>
                                <th><?= __('Total Cost') ?></th>
                                <th><?= __('Total Tokens') ?></th>
                                <th><?= __('Status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($taskMetrics as $metric): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-<?= $this->cell('Icon::getTaskTypeIcon', [$metric->task_type]) ?>"></i>
                                    <?= h(ucwords(str_replace('_', ' ', $metric->task_type))) ?>
                                </td>
                                <td><?= number_format($metric->count) ?></td>
                                <td>
                                    <span class="<?= $metric->avg_time > 10000 ? 'text-warning' : 'text-success' ?>">
                                        <?= number_format($metric->avg_time, 0) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $metric->success_rate >= 95 ? 'badge-success' : ($metric->success_rate >= 85 ? 'badge-warning' : 'badge-danger') ?>">
                                        <?= number_format($metric->success_rate, 1) ?>%
                                    </span>
                                </td>
                                <td>$<?= number_format($metric->total_cost, 4) ?></td>
                                <td><?= number_format($metric->total_tokens) ?></td>
                                <td>
                                    <?php if ($metric->success_rate >= 95): ?>
                                        <i class="fas fa-check-circle text-success" title="Healthy"></i>
                                    <?php elseif ($metric->success_rate >= 85): ?>
                                        <i class="fas fa-exclamation-triangle text-warning" title="Needs Attention"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle text-danger" title="Critical"></i>
                                    <?php endif; ?>
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

<!-- Recent Errors -->
<?php if (!empty($recentErrors)): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Recent Errors') ?></h5>
                <small class="text-muted"><?= __('Last 5 errors') ?></small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th><?= __('Date') ?></th>
                                <th><?= __('Task Type') ?></th>
                                <th><?= __('Error Message') ?></th>
                                <th><?= __('Model') ?></th>
                                <th><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentErrors as $error): ?>
                            <tr>
                                <td>
                                    <?= $error->created->format('M j, Y H:i') ?><br>
                                    <small class="text-muted"><?= $error->created->timeAgoInWords() ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-outline-secondary">
                                        <?= h(ucwords(str_replace('_', ' ', $error->task_type))) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-danger" title="<?= h($error->error_message) ?>">
                                        <?= h(substr($error->error_message, 0, 100)) ?>
                                        <?= strlen($error->error_message) > 100 ? '...' : '' ?>
                                    </span>
                                </td>
                                <td><?= h($error->model_used) ?: '-' ?></td>
                                <td>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $error->id],
                                        ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => __('View Details')]
                                    ) ?>
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
<?php endif; ?>

<!-- Live Activity Feed -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>
                    <?= __('Live Activity') ?>
                    <span class="badge badge-success ml-2" id="liveIndicator">
                        <i class="fas fa-circle"></i> Live
                    </span>
                </h5>
                <small class="text-muted"><?= __('Real-time AI operations (auto-refreshes every 30 seconds)') ?></small>
            </div>
            <div class="card-body">
                <div id="liveActivity" style="max-height: 300px; overflow-y: auto;">
                    <div class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin"></i> <?= __('Loading live activity...') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard functionality
    const dashboard = {
        charts: {},
        refreshInterval: null,
        
        init() {
            this.setupEventListeners();
            this.initializeCharts();
            this.startAutoRefresh();
        },
        
        setupEventListeners() {
            // Refresh button
            document.getElementById('refreshBtn').addEventListener('click', () => {
                this.refreshData();
            });
            
            // Timeframe buttons
            document.querySelectorAll('[data-timeframe]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    document.querySelectorAll('[data-timeframe]').forEach(b => b.classList.remove('active'));
                    e.target.classList.add('active');
                    this.refreshData(e.target.dataset.timeframe);
                });
            });
        },
        
        initializeCharts() {
            // Task type pie chart
            const taskCtx = document.getElementById('taskTypeChart').getContext('2d');
            const taskData = <?= json_encode(array_map(function($m) { 
                return ['label' => ucwords(str_replace('_', ' ', $m->task_type)), 'value' => $m->count]; 
            }, $taskMetrics)) ?>;
            
            this.charts.taskType = new Chart(taskCtx, {
                type: 'doughnut',
                data: {
                    labels: taskData.map(d => d.label),
                    datasets: [{
                        data: taskData.map(d => d.value),
                        backgroundColor: [
                            '#007bff', '#28a745', '#ffc107', '#dc3545', 
                            '#6c757d', '#17a2b8', '#fd7e14', '#6f42c1'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'bottom' }
                }
            });
            
            // Sparkline chart
            const sparklineCtx = document.getElementById('callsSparkline').getContext('2d');
            this.charts.sparkline = new Chart(sparklineCtx, {
                type: 'line',
                data: {
                    labels: Array.from({length: 24}, (_, i) => i),
                    datasets: [{
                        data: Array.from({length: 24}, () => Math.floor(Math.random() * 10)),
                        borderColor: '#007bff',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    scales: { 
                        x: { display: false }, 
                        y: { display: false } 
                    },
                    plugins: { legend: { display: false } }
                }
            });
        },
        
        refreshData(timeframe = '30d') {
            const btn = document.getElementById('refreshBtn');
            const icon = btn.querySelector('i');
            
            // Show loading state
            icon.classList.add('fa-spin');
            btn.disabled = true;
            
            fetch(`<?= $this->Url->build(['action' => 'realtimeData']) ?>?timeframe=${timeframe}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.updateMetrics(data.data);
                        this.updateLiveActivity(data.data.recentActivity);
                    } else {
                        console.error('Failed to refresh data:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error refreshing data:', error);
                })
                .finally(() => {
                    icon.classList.remove('fa-spin');
                    btn.disabled = false;
                });
        },
        
        updateMetrics(data) {
            // Update key metrics
            document.getElementById('totalCalls').textContent = new Intl.NumberFormat().format(data.totalCalls);
            document.getElementById('successRate').textContent = data.successRate + '%';
            document.getElementById('totalCost').textContent = '$' + data.totalCost.toFixed(2);
            document.getElementById('rateLimit').textContent = `${data.currentUsage.current}/${data.currentUsage.limit}`;
            
            // Update sparkline with new data
            if (data.sparkline && this.charts.sparkline) {
                this.charts.sparkline.data.datasets[0].data = data.sparkline;
                this.charts.sparkline.update('none');
            }
        },
        
        updateLiveActivity(activities) {
            const container = document.getElementById('liveActivity');
            if (!activities || activities.length === 0) {
                container.innerHTML = '<div class="text-center text-muted">No recent activity</div>';
                return;
            }
            
            container.innerHTML = activities.map(activity => `
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <strong>${activity.task_type.replace(/_/g, ' ')}</strong>
                        <small class="text-muted ml-2">${activity.model_used || 'Unknown model'}</small>
                        ${activity.success ? 
                            '<span class="badge badge-success ml-2">Success</span>' : 
                            '<span class="badge badge-danger ml-2">Failed</span>'
                        }
                    </div>
                    <small class="text-muted">${new Date(activity.created).toLocaleTimeString()}</small>
                </div>
            `).join('');
        },
        
        startAutoRefresh() {
            // Refresh every 30 seconds
            this.refreshInterval = setInterval(() => {
                this.refreshData();
            }, 30000);
        },
        
        destroy() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
            Object.values(this.charts).forEach(chart => chart.destroy());
        }
    };
    
    // Initialize dashboard
    dashboard.init();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        dashboard.destroy();
    });
});
</script>

<style>
.actions-card .actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.card-body .progress {
    margin-top: 8px;
}

#liveActivity {
    font-size: 0.9em;
}

#liveIndicator {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.table td, .table th {
    vertical-align: middle;
}

.badge-outline-secondary {
    color: #6c757d;
    border: 1px solid #6c757d;
    background: transparent;
}
</style>
