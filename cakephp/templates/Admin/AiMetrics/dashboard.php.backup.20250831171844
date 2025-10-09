<?php
$this->assign('title', __('AI Metrics Dashboard'));
$this->Html->css('willow-admin', ['block' => true]);
?>
<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('AI Metrics Dashboard') ?> 
                <span id="live-indicator" class="badge badge-success" style="display: none;">LIVE</span>
                <span id="offline-indicator" class="badge badge-secondary">OFFLINE</span>
            </h3>
            <p class="text-muted">
                <?= __('Last 30 days overview') ?>
                <span id="last-updated" class="small text-info"></span>
            </p>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary" data-timeframe="1h">1H</button>
                <button type="button" class="btn btn-outline-secondary" data-timeframe="24h">24H</button>
                <button type="button" class="btn btn-outline-secondary" data-timeframe="7d">7D</button>
                <button type="button" class="btn btn-outline-secondary active" data-timeframe="30d">30D</button>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Total API Calls') ?></h5>
                <h2 id="total-calls-value" class="text-primary"><?= number_format($totalCalls) ?></h2>
                <small id="total-calls-period" class="text-muted"><?= __('Last 30 days') ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Success Rate') ?></h5>
                <h2 id="success-rate-value" class="<?= $successRate >= 95 ? 'text-success' : ($successRate >= 85 ? 'text-warning' : 'text-danger') ?>">
                    <?= number_format($successRate, 1) ?>%
                </h2>
                <small class="text-muted"><?= __('API Success Rate') ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Total Cost') ?></h5>
                <h2 id="total-cost-value" class="text-info">$<?= number_format($totalCost, 2) ?></h2>
                <small id="total-cost-period" class="text-muted"><?= __('Last 30 days') ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Rate Limit') ?></h5>
                <h2 id="rate-limit-value" class="<?= $currentUsage['remaining'] > 10 ? 'text-success' : 'text-warning' ?>">
                    <?= $currentUsage['current'] ?>/<?= $currentUsage['limit'] ?>
                </h2>
                <small class="text-muted"><?= __('This hour') ?></small>
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
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?= __('Task Type') ?></th>
                            <th><?= __('Count') ?></th>
                            <th><?= __('Avg Time (ms)') ?></th>
                            <th><?= __('Success Rate') ?></th>
                            <th><?= __('Total Cost') ?></th>
                            <th><?= __('Total Tokens') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($taskMetrics as $metric): ?>
                        <?php 
                            // Add service indicator badge based on task type
                            $serviceType = 'Unknown';
                            $badgeClass = 'badge-secondary';
                            if (strpos($metric->task_type, 'google_') === 0) {
                                $serviceType = 'Google Translate';
                                $badgeClass = 'badge-info';
                            } elseif (strpos($metric->task_type, 'anthropic_') === 0) {
                                $serviceType = 'Anthropic Claude';
                                $badgeClass = 'badge-primary';
                            }
                        ?>
                        <tr>
                            <td>
                                <div><?= h($metric->task_type) ?></div>
                                <small class="badge <?= $badgeClass ?>"><?= $serviceType ?></small>
                            </td>
                            <td><?= number_format($metric->count) ?></td>
                            <td><?= number_format($metric->avg_time, 0) ?></td>
                            <td>
                                <span class="badge <?= $metric->success_rate >= 95 ? 'badge-success' : ($metric->success_rate >= 85 ? 'badge-warning' : 'badge-danger') ?>">
                                    <?= number_format($metric->success_rate, 1) ?>%
                                </span>
                            </td>
                            <td>$<?= number_format($metric->total_cost, 2) ?></td>
                            <td>
                                <?php if ($metric->total_tokens): ?>
                                    <?= number_format($metric->total_tokens) ?>
                                <?php else: ?>
                                    <small class="text-muted">N/A</small>
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
<!-- Recent Errors -->
<?php if (!empty($recentErrors)): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Recent Errors') ?></h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th><?= __('Date') ?></th>
                            <th><?= __('Task Type') ?></th>
                            <th><?= __('Error Message') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentErrors as $error): ?>
                        <tr>
                            <td><?= $error->created->format('M j, Y H:i') ?></td>
                            <td><?= h($error->task_type) ?></td>
                            <td><?= h($error->error_message) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Queue Status Section -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Queue Status') ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center">
                        <h4 id="active-jobs" class="text-warning">0</h4>
                        <small class="text-muted"><?= __('Active Jobs') ?></small>
                    </div>
                    <div class="col-6 text-center">
                        <h4 id="pending-jobs" class="text-info">0</h4>
                        <small class="text-muted"><?= __('Pending Jobs') ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Recent Activity') ?></h5>
            </div>
            <div class="card-body">
                <div id="activity-sparkline" style="height: 60px;"></div>
                <small class="text-muted"><?= __('API calls per minute (last hour)') ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Live Activity Feed') ?></h5>
            </div>
            <div class="card-body">
                <div id="recent-activity-list">
                    <p class="text-muted text-center"><?= __('No recent activity') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let currentTimeframe = '30d';
    let updateInterval;
    let isLive = false;
    
    // Initialize real-time updates
    function initializeRealTime() {
        // Start polling immediately
        updateMetrics();
        
        // Set up interval for updates every 10 seconds
        updateInterval = setInterval(updateMetrics, 10000);
        
        // Update indicators
        document.getElementById('live-indicator').style.display = 'inline';
        document.getElementById('offline-indicator').style.display = 'none';
        isLive = true;
    }
    
    // Stop real-time updates
    function stopRealTime() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
        document.getElementById('live-indicator').style.display = 'none';
        document.getElementById('offline-indicator').style.display = 'inline';
        isLive = false;
    }
    
    // Update metrics via AJAX
    function updateMetrics() {
        fetch('/admin/ai-metrics/realtime-data?timeframe=' + currentTimeframe, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include' // Include session cookies for authentication
        })
        .then(response => {
            if (response.status === 403 || response.status === 401) {
                throw new Error('Authentication failed');
            }
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateDashboard(data.data);
                updateLastUpdated();
                // If we were offline, update status
                if (!isLive) {
                    document.getElementById('live-indicator').style.display = 'inline';
                    document.getElementById('offline-indicator').style.display = 'none';
                    isLive = true;
                }
            } else {
                console.error('API returned error:', data.error || 'Unknown error');
                handleApiError(data.message || 'Failed to fetch real-time data');
            }
        })
        .catch(error => {
            console.error('Error fetching real-time data:', error);
            handleApiError(error.message);
        });
    }
    
    // Handle API errors and update UI accordingly
    function handleApiError(errorMessage) {
        stopRealTime();
        
        // Show error in activity feed
        const container = document.getElementById('recent-activity-list');
        if (container) {
            const errorItem = document.createElement('div');
            errorItem.className = 'alert alert-warning py-2 my-2';
            errorItem.innerHTML = `
                <small><strong>Connection Error:</strong> ${escapeHtml(errorMessage)}</small>
                <br><small class="text-muted">Will retry in 10 seconds...</small>
            `;
            container.insertBefore(errorItem, container.firstChild);
        }
        
        // Try to reconnect after a delay
        setTimeout(() => {
            console.log('Attempting to reconnect...');
            updateMetrics();
        }, 10000); // Retry after 10 seconds
    }
    
    // Update dashboard elements
    function updateDashboard(data) {
        // Update summary cards using specific IDs
        updateCardById('total-calls-value', formatNumber(data.totalCalls));
        updateCardById('success-rate-value', data.successRate.toFixed(1) + '%', getSuccessRateClass(data.successRate));
        updateCardById('total-cost-value', '$' + data.totalCost.toFixed(2));
        updateCardById('rate-limit-value', data.currentUsage.current + '/' + data.currentUsage.limit, 
                       data.currentUsage.remaining > 10 ? 'text-success' : 'text-warning');
        
        // Update period labels based on timeframe
        updateTimeframePeriods();
        
        // Update queue status
        if (document.getElementById('active-jobs')) {
            document.getElementById('active-jobs').textContent = data.queueStatus.active;
        }
        if (document.getElementById('pending-jobs')) {
            document.getElementById('pending-jobs').textContent = data.queueStatus.pending;
        }
        
        // Update task metrics table
        updateTaskMetricsTable(data.taskMetrics);
        
        // Update recent activity
        updateRecentActivity(data.recentActivity);
        
        // Update sparkline (simple text representation)
        updateSparkline(data.sparkline);
    }
    
    // Helper function to update card content by ID
    function updateCardById(elementId, value, className = null) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = value;
            if (className) {
                // Remove existing text- classes and add new one
                element.className = element.className.split(' ').filter(cls => !cls.startsWith('text-')).join(' ') + ' ' + className;
            }
        }
    }
    
    // Update timeframe period labels
    function updateTimeframePeriods() {
        const periodMapping = {
            '1h': 'Last hour',
            '24h': 'Last 24 hours', 
            '7d': 'Last 7 days',
            '30d': 'Last 30 days'
        };
        
        const periodText = periodMapping[currentTimeframe] || 'Current period';
        
        const totalCallsPeriod = document.getElementById('total-calls-period');
        const totalCostPeriod = document.getElementById('total-cost-period');
        
        if (totalCallsPeriod) totalCallsPeriod.textContent = periodText;
        if (totalCostPeriod) totalCostPeriod.textContent = periodText;
    }
    
    // Get appropriate CSS class for success rate
    function getSuccessRateClass(rate) {
        if (rate >= 95) return 'text-success';
        if (rate >= 85) return 'text-warning';
        return 'text-danger';
    }
    
    // Update task metrics table
    function updateTaskMetricsTable(taskMetrics) {
        const tbody = document.querySelector('.table tbody');
        if (!tbody || !taskMetrics || taskMetrics.length === 0) return;
        
        tbody.innerHTML = '';
        taskMetrics.forEach(metric => {
            const serviceType = getServiceType(metric.task_type);
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div>${escapeHtml(metric.task_type)}</div>
                    <small class="badge ${serviceType.class}">${serviceType.name}</small>
                </td>
                <td>${formatNumber(metric.count)}</td>
                <td>${formatNumber(metric.avg_time, 0)}</td>
                <td>
                    <span class="badge ${getSuccessRateBadgeClass(metric.success_rate)}">
                        ${formatNumber(metric.success_rate, 1)}%
                    </span>
                </td>
                <td>$${formatNumber(metric.total_cost, 2)}</td>
                <td>
                    ${metric.total_tokens ? formatNumber(metric.total_tokens) : '<small class="text-muted">N/A</small>'}
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    // Update recent activity feed
    function updateRecentActivity(recentActivity) {
        const container = document.getElementById('recent-activity-list');
        if (!container) return;
        
        if (!recentActivity || recentActivity.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">No recent activity</p>';
            return;
        }
        
        container.innerHTML = '';
        recentActivity.slice(0, 5).forEach(activity => {
            const item = document.createElement('div');
            item.className = 'border-bottom py-2';
            const date = new Date(activity.created);
            item.innerHTML = `
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>${escapeHtml(activity.task_type)}</strong>
                        ${activity.success ? '<span class="badge badge-success badge-sm">Success</span>' : '<span class="badge badge-danger badge-sm">Failed</span>'}
                    </div>
                    <small class="text-muted">${date.toLocaleTimeString()}</small>
                </div>
                ${activity.error_message ? '<small class="text-danger">' + escapeHtml(activity.error_message) + '</small>' : ''}
            `;
            container.appendChild(item);
        });
    }
    
    // Update sparkline with simple text representation
    function updateSparkline(sparklineData) {
        const container = document.getElementById('activity-sparkline');
        if (!container || !sparklineData || sparklineData.length === 0) return;
        
        const max = Math.max(...sparklineData, 1);
        const bars = sparklineData.map(value => {
            const height = Math.max((value / max) * 50, 2);
            return `<div style="display: inline-block; width: 4px; height: ${height}px; background-color: #007bff; margin-right: 1px; vertical-align: bottom;"></div>`;
        }).join('');
        
        container.innerHTML = `<div style="text-align: center;">${bars}</div>`;
    }
    
    // Update last updated timestamp
    function updateLastUpdated() {
        const lastUpdated = document.getElementById('last-updated');
        if (lastUpdated) {
            lastUpdated.textContent = '(Updated: ' + new Date().toLocaleTimeString() + ')';
        }
    }
    
    // Helper functions
    function getServiceType(taskType) {
        if (taskType.startsWith('google_')) {
            return { name: 'Google Translate', class: 'badge-info' };
        } else if (taskType.startsWith('anthropic_')) {
            return { name: 'Anthropic Claude', class: 'badge-primary' };
        }
        return { name: 'Unknown', class: 'badge-secondary' };
    }
    
    function getSuccessRateBadgeClass(rate) {
        if (rate >= 95) return 'badge-success';
        if (rate >= 85) return 'badge-warning';
        return 'badge-danger';
    }
    
    function formatNumber(num, decimals = 0) {
        return parseFloat(num).toLocaleString(undefined, { 
            minimumFractionDigits: decimals, 
            maximumFractionDigits: decimals 
        });
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    // Event listeners for timeframe buttons
    document.querySelectorAll('[data-timeframe]').forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            document.querySelectorAll('[data-timeframe]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Update timeframe and refresh data
            currentTimeframe = this.getAttribute('data-timeframe');
            updateMetrics();
        });
    });
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeRealTime();
    });
    
    // Clean up on page unload
    window.addEventListener('beforeunload', function() {
        stopRealTime();
    });
})();
</script>
