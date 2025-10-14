<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var \Cake\Collection\CollectionInterface $viewsOverTime
 */
?>
<div class="page-view-stats content container-fluid mt-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <h2 class="mb-3 mb-md-0"><?= __('Page View Statistics for: {0}', h($article->title)) ?></h2>
        <div class="kpi-card">
            <div class="card bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-eye me-2"></i>
                        <div>
                            <small><?= __('Total Views') ?></small>
                            <h4 class="mb-0" id="total-views"><?= number_format(array_sum(array_column($viewsOverTime->toArray(), 'count'))) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= __('Filter by Date Range') ?></h5>
                </div>
                <div class="card-body">
                    <div class="date-filter-form">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="start-date" class="form-label"><?= __('Start Date') ?></label>
                                <input type="date" id="start-date" class="form-control">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label for="end-date" class="form-label"><?= __('End Date') ?></label>
                                <input type="date" id="end-date" class="form-control">
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-end">
                                <button id="filter-btn" class="btn btn-primary w-100"><?= __('Apply Filter') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= __('Views Over Time') ?></h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($viewsOverTime) && $viewsOverTime->count() > 0): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?= __('Daily Breakdown') ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><?= __('Date') ?></th>
                                        <th class="text-end"><?= __('Views') ?></th>
                                        <th><?= __('Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody id="stats-table-body">
                                    <?php foreach ($viewsOverTime as $view): ?>
                                    <tr>
                                        <td><?= h((new DateTime($view->date))->format('M j, Y')) ?></td>
                                        <td class="text-end">
                                            <span class="badge bg-primary"><?= number_format($view->count) ?></span>
                                        </td>
                                        <td>
                                            <?= $this->Html->link(
                                                '<i class="fas fa-list"></i> ' . __('Details'),
                                                ['action' => 'viewRecords', $article->id, '?' => ['date' => $view->date]],
                                                ['title' => __('View detailed records for this date'), 'class' => 'btn btn-sm btn-outline-primary', 'escape' => false]
                                            ) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="d-md-none p-3" id="stats-mobile-cards">
                            <?php foreach ($viewsOverTime as $view): ?>
                            <div class="card mb-2 mobile-stat-card">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1"><?= h((new DateTime($view->date))->format('M j, Y')) ?></h6>
                                            <span class="badge bg-primary"><?= number_format($view->count) ?> <?= __('views') ?></span>
                                        </div>
                                        <div>
                                            <?= $this->Html->link(
                                                '<i class="fas fa-list"></i>',
                                                ['action' => 'viewRecords', $article->id, '?' => ['date' => $view->date]],
                                                ['title' => __('View details'), 'class' => 'btn btn-sm btn-outline-primary', 'escape' => false]
                                            ) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <?= __('No page view data available for this article.') ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('viewsChart').getContext('2d');
    let chart;
    const filterBtn = document.getElementById('filter-btn');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const totalViews = document.getElementById('total-views');
    const tableBody = document.getElementById('stats-table-body');
    const mobileCards = document.getElementById('stats-mobile-cards');

    // Initialize chart with current data
    initChart(<?= json_encode($viewsOverTime->toArray()) ?>);

    filterBtn.addEventListener('click', function() {
        if (!startDate.value || !endDate.value) {
            alert('<?= __('Please select both start and end dates') ?>');
            return;
        }

        // Show loading state
        filterBtn.disabled = true;
        filterBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= __('Loading...') ?>';

        const url = `/admin/page-views/filter-stats/<?= $article->id ?>?start=${startDate.value}&end=${endDate.value}`;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                updateTable(data.viewsOverTime);
                updateMobileCards(data.viewsOverTime);
                updateChart(data.viewsOverTime);
                totalViews.textContent = new Intl.NumberFormat().format(data.totalViews);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?= __('An error occurred while fetching data. Please try again.') ?>');
            })
            .finally(() => {
                // Reset button state
                filterBtn.disabled = false;
                filterBtn.innerHTML = '<?= __('Apply Filter') ?>';
            });
    });

    function initChart(data) {
        const chartData = prepareChartData(data);
        chart = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            displayFormats: {
                                day: 'MMM dd'
                            }
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
    }

    function updateChart(data) {
        const chartData = prepareChartData(data);
        chart.data = chartData;
        chart.update();
    }

    function prepareChartData(data) {
        return {
            datasets: [{
                label: '<?= __('Page Views') ?>',
                data: data.map(item => ({x: new Date(item.date), y: item.count})),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        };
    }

    function updateTable(viewsData) {
        if (!tableBody) return;
        
        tableBody.innerHTML = '';
        viewsData.forEach(view => {
            const date = new Date(view.date);
            const formattedDate = date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
            
            const row = `
                <tr>
                    <td>${formattedDate}</td>
                    <td class="text-end">
                        <span class="badge bg-primary">${new Intl.NumberFormat().format(view.count)}</span>
                    </td>
                    <td>
                        <a href="/admin/page-views/view-records/<?= $article->id ?>?date=${view.date}" 
                           class="btn btn-sm btn-outline-primary" 
                           title="<?= __('View detailed records for this date') ?>">
                            <i class="fas fa-list"></i> <?= __('Details') ?>
                        </a>
                    </td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    }

    function updateMobileCards(viewsData) {
        if (!mobileCards) return;
        
        mobileCards.innerHTML = '';
        viewsData.forEach(view => {
            const date = new Date(view.date);
            const formattedDate = date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
            
            const card = `
                <div class="card mb-2 mobile-stat-card">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">${formattedDate}</h6>
                                <span class="badge bg-primary">${new Intl.NumberFormat().format(view.count)} <?= __('views') ?></span>
                            </div>
                            <div>
                                <a href="/admin/page-views/view-records/<?= $article->id ?>?date=${view.date}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="<?= __('View details') ?>">
                                    <i class="fas fa-list"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            mobileCards.innerHTML += card;
        });
    }
});
</script>

<style>
.kpi-card {
    min-width: 200px;
}

.chart-container {
    min-height: 200px;
}

.mobile-stat-card {
    border: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s;
}

.mobile-stat-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

@media (max-width: 768px) {
    .d-flex.flex-column.flex-md-row {
        gap: 1rem;
    }
    
    .kpi-card {
        width: 100%;
        min-width: auto;
    }
    
    .date-filter-form .row {
        gap: 0.5rem;
    }
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #6c757d;
    font-size: 0.875rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: box-shadow 0.15s ease-in-out;
}
</style>