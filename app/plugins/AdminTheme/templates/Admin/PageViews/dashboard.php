<?php
/**
 * @var \App\View\AppView $this
 * @var int $totalViews
 * @var int $uniqueVisitors
 * @var \Cake\Collection\CollectionInterface $viewsOverTime
 * @var \Cake\Collection\CollectionInterface $topArticles
 * @var array $browserStats
 * @var array $hourlyDistribution
 * @var array $topReferrers
 * @var \DateTime $startDate
 * @var \DateTime $endDate
 */
?>
<div class="analytics-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?= __('Analytics Dashboard') ?></h1>
        <div class="date-filter">
            <form class="d-flex gap-2" method="get">
                <input type="date" 
                       name="start" 
                       value="<?= $startDate->format('Y-m-d') ?>" 
                       class="form-control">
                <input type="date" 
                       name="end" 
                       value="<?= $endDate->format('Y-m-d') ?>" 
                       class="form-control">
                <button type="submit" class="btn btn-primary"><?= __('Filter') ?></button>
            </form>
        </div>
    </div>

    <!-- KPI Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0"><?= __('Total Views') ?></h6>
                            <h2 class="mb-0"><?= number_format($totalViews) ?></h2>
                        </div>
                        <i class="fas fa-eye fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0"><?= __('Unique Visitors') ?></h6>
                            <h2 class="mb-0"><?= number_format($uniqueVisitors) ?></h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0"><?= __('Avg Views/Day') ?></h6>
                            <h2 class="mb-0">
                                <?= $totalViews > 0 ? number_format($totalViews / max(1, $startDate->diff($endDate)->days)) : '0' ?>
                            </h2>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0"><?= __('Pages/Visitor') ?></h6>
                            <h2 class="mb-0">
                                <?= $uniqueVisitors > 0 ? number_format($totalViews / $uniqueVisitors, 1) : '0' ?>
                            </h2>
                        </div>
                        <i class="fas fa-file-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Views Over Time Chart -->
        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= __('Views Over Time') ?></h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Browser Stats Chart -->
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= __('Browser Usage') ?></h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="browserChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile-responsive data tables -->
    <div class="row g-3 mb-4">
        <!-- Top Articles -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= __('Top Articles') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($topArticles) && $topArticles->count() > 0): ?>
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?= __('Article') ?></th>
                                        <th class="text-end"><?= __('Views') ?></th>
                                        <th><?= __('Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topArticles as $article): ?>
                                    <tr>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?= h($article->article->title ?? 'N/A') ?>">
                                                <?= h($article->article->title ?? 'N/A') ?>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-primary"><?= number_format($article->count) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($article->article): ?>
                                                <?= $this->Html->link(
                                                    '<i class="fas fa-chart-bar"></i>',
                                                    ['action' => 'pageViewStats', $article->article->id],
                                                    ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => __('View Stats')]
                                                ) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Mobile Cards -->
                        <div class="d-md-none">
                            <?php foreach ($topArticles as $article): ?>
                            <div class="card mb-2">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-2">
                                            <h6 class="card-title mb-1"><?= h($article->article->title ?? 'N/A') ?></h6>
                                            <span class="badge bg-primary"><?= number_format($article->count) ?> <?= __('views') ?></span>
                                        </div>
                                        <?php if ($article->article): ?>
                                            <?= $this->Html->link(
                                                '<i class="fas fa-chart-bar"></i>',
                                                ['action' => 'pageViewStats', $article->article->id],
                                                ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false]
                                            ) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info"><?= __('No article data available for the selected period.') ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Top Referrers -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= __('Top Referrers') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($topReferrers)): ?>
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?= __('Source') ?></th>
                                        <th class="text-end"><?= __('Visits') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topReferrers as $referrer): ?>
                                    <tr>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?= h($referrer['domain']) ?>">
                                                <?= h($referrer['domain']) ?>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-secondary"><?= number_format($referrer['count']) ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Mobile Cards -->
                        <div class="d-md-none">
                            <?php foreach ($topReferrers as $referrer): ?>
                            <div class="card mb-2">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0"><?= h($referrer['domain']) ?></h6>
                                        </div>
                                        <span class="badge bg-secondary"><?= number_format($referrer['count']) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info"><?= __('No referrer data available for the selected period.') ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Hourly Distribution Chart -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= __('Hourly Distribution') ?></h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 200px;">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Views Over Time Chart
    const viewsCtx = document.getElementById('viewsChart').getContext('2d');
    const viewsData = <?= json_encode($viewsOverTime->toArray()) ?>;
    
    new Chart(viewsCtx, {
        type: 'line',
        data: {
            datasets: [{
                label: '<?= __('Page Views') ?>',
                data: viewsData.map(item => ({
                    x: new Date(item.date),
                    y: item.count
                })),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'day',
                        displayFormats: {
                            day: 'MMM dd'
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Browser Stats Chart
    const browserCtx = document.getElementById('browserChart').getContext('2d');
    const browserData = <?= json_encode($browserStats) ?>;
    
    new Chart(browserCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(browserData),
            datasets: [{
                data: Object.values(browserData),
                backgroundColor: [
                    '#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1',
                    '#fd7e14', '#20c997', '#6c757d', '#e83e8c', '#17a2b8'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });

    // Hourly Distribution Chart
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    const hourlyData = <?= json_encode($hourlyDistribution) ?>;
    
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: Array.from({length: 24}, (_, i) => i + ':00'),
            datasets: [{
                label: '<?= __('Views') ?>',
                data: hourlyData,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: '#0d6efd',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<style>
.analytics-dashboard .card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
}

.analytics-dashboard .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.date-filter input,
.date-filter button {
    min-width: auto;
}

@media (max-width: 576px) {
    .date-filter {
        width: 100%;
    }
    
    .date-filter form {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .d-flex.justify-content-between.align-items-center {
        flex-direction: column;
        align-items: stretch !important;
        gap: 1rem;
    }
}

.chart-container {
    min-height: 200px;
}
</style>