<?php
/**
 * Forms Statistics and Submissions View
 * @var \App\View\AppView $this
 * @var array $statistics
 * @var array $recent_submissions
 * @var array $forms
 */
?>
<?= $this->Html->css('AdminTheme.forms', ['block' => true]) ?>
<?= $this->Html->script('AdminTheme.chart.min', ['block' => true]) ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        <?= __('Forms Statistics & Submissions') ?>
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-filter mr-2"></i><?= __('Filter') ?>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" data-period="today"><?= __('Today') ?></a>
                                <a class="dropdown-item" href="#" data-period="week"><?= __('This Week') ?></a>
                                <a class="dropdown-item" href="#" data-period="month"><?= __('This Month') ?></a>
                                <a class="dropdown-item" href="#" data-period="year"><?= __('This Year') ?></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-period="all"><?= __('All Time') ?></a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success" id="export-data">
                            <i class="fas fa-download mr-2"></i><?= __('Export') ?>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Overview -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3><?= $statistics['total_submissions'] ?? 0 ?></h3>
                                    <p><?= __('Total Submissions') ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?= $statistics['submissions_today'] ?? 0 ?></h3>
                                    <p><?= __('Today\'s Submissions') ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?= $statistics['active_forms'] ?? 0 ?></h3>
                                    <p><?= __('Active Forms') ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-wpforms"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?= number_format($statistics['avg_completion_rate'] ?? 0, 1) ?>%</h3>
                                    <p><?= __('Avg. Completion Rate') ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5><?= __('Submissions Over Time') ?></h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="submissionsChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><?= __('Forms by Popularity') ?></h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="formsChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Performance Table -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><?= __('Form Performance') ?></h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th><?= __('Form Name') ?></th>
                                                    <th><?= __('Submissions') ?></th>
                                                    <th><?= __('Completion Rate') ?></th>
                                                    <th><?= __('Avg. Time') ?></th>
                                                    <th><?= __('Last Submission') ?></th>
                                                    <th><?= __('Actions') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($forms ?? [] as $form): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= h($form['title']) ?></strong>
                                                        <?php if ($form['type'] === 'quiz'): ?>
                                                            <span class="badge badge-info ml-2">Quiz</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary">
                                                            <?= $form['submission_count'] ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar <?= $form['completion_rate'] > 75 ? 'bg-success' : ($form['completion_rate'] > 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                                                 style="width: <?= $form['completion_rate'] ?>%">
                                                                <?= number_format($form['completion_rate'], 1) ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <small><?= $form['avg_completion_time'] ?? __('N/A') ?></small>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?= $form['last_submission'] ? $form['last_submission']->format('M j, Y H:i') : __('Never') ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary view-details" 
                                                                    data-form-id="<?= $form['id'] ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-success export-form-data" 
                                                                    data-form-id="<?= $form['id'] ?>">
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                        </div>
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

                    <!-- Recent Submissions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><?= __('Recent Submissions') ?></h5>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-sm btn-primary" id="refresh-submissions">
                                            <i class="fas fa-sync mr-1"></i><?= __('Refresh') ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th><?= __('Date/Time') ?></th>
                                                    <th><?= __('Form') ?></th>
                                                    <th><?= __('User') ?></th>
                                                    <th><?= __('Status') ?></th>
                                                    <th><?= __('Score') ?></th>
                                                    <th><?= __('Actions') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($recent_submissions)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">
                                                        <i class="fas fa-info-circle mr-2"></i>
                                                        <?= __('No recent submissions found.') ?>
                                                    </td>
                                                </tr>
                                                <?php else: ?>
                                                    <?php foreach ($recent_submissions as $submission): ?>
                                                    <tr>
                                                        <td>
                                                            <small><?= $submission['created']->format('M j, Y H:i') ?></small>
                                                        </td>
                                                        <td>
                                                            <strong><?= h($submission['form_title']) ?></strong>
                                                        </td>
                                                        <td>
                                                            <?php if ($submission['user_name']): ?>
                                                                <?= h($submission['user_name']) ?>
                                                            <?php else: ?>
                                                                <em class="text-muted"><?= __('Anonymous') ?></em>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($submission['is_complete']): ?>
                                                                <span class="badge badge-success"><?= __('Complete') ?></span>
                                                            <?php else: ?>
                                                                <span class="badge badge-warning"><?= __('Partial') ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($submission['score'] !== null): ?>
                                                                <span class="badge badge-<?= $submission['score'] >= 70 ? 'success' : 'danger' ?>">
                                                                    <?= number_format($submission['score'], 1) ?>%
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-muted">—</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-outline-primary view-submission" 
                                                                    data-submission-id="<?= $submission['id'] ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
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
    </div>
</div>

<!-- Submission Details Modal -->
<div class="modal fade" id="submissionModal" tabindex="-1" role="dialog" aria-labelledby="submissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submissionModalLabel">
                    <i class="fas fa-file-alt mr-2"></i><?= __('Submission Details') ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="submission-content">
                <!-- Dynamic content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= __('Close') ?></button>
                <button type="button" class="btn btn-primary" id="export-submission"><?= __('Export') ?></button>
            </div>
        </div>
    </div>
</div>

<?php $this->append('script'); ?>
<script>
$(document).ready(function() {
    // Initialize charts
    initSubmissionsChart();
    initFormsChart();

    function initSubmissionsChart() {
        const ctx = document.getElementById('submissionsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($statistics['chart_labels'] ?? []) ?>,
                datasets: [{
                    label: '<?= __('Submissions') ?>',
                    data: <?= json_encode($statistics['chart_data'] ?? []) ?>,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function initFormsChart() {
        const ctx = document.getElementById('formsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($statistics['form_labels'] ?? []) ?>,
                datasets: [{
                    data: <?= json_encode($statistics['form_data'] ?? []) ?>,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Filter functionality
    $('.dropdown-menu a[data-period]').on('click', function(e) {
        e.preventDefault();
        const period = $(this).data('period');
        // Handle period filter
        console.log('Filtering by period:', period);
        // Reload data for the selected period
    });

    // Export data
    $('#export-data').on('click', function() {
        // Handle data export
        console.log('Exporting data');
        window.open('<?= $this->Url->build(['action' => 'exportStats']) ?>', '_blank');
    });

    // View form details
    $('.view-details').on('click', function() {
        const formId = $(this).data('form-id');
        // Load detailed view for specific form
        console.log('Viewing details for form:', formId);
    });

    // Export form data
    $('.export-form-data').on('click', function() {
        const formId = $(this).data('form-id');
        // Export data for specific form
        console.log('Exporting data for form:', formId);
        window.open('<?= $this->Url->build(['action' => 'exportFormData']) ?>/' + formId, '_blank');
    });

    // View submission details
    $('.view-submission').on('click', function() {
        const submissionId = $(this).data('submission-id');
        
        // Load submission details via AJAX
        $.get('<?= $this->Url->build(['action' => 'getSubmission']) ?>/' + submissionId)
            .done(function(data) {
                $('#submission-content').html(data);
                $('#submissionModal').modal('show');
            })
            .fail(function() {
                alert('<?= __('Error loading submission details') ?>');
            });
    });

    // Refresh submissions
    $('#refresh-submissions').on('click', function() {
        // Reload recent submissions
        location.reload();
    });

    // Export submission
    $('#export-submission').on('click', function() {
        // Get current submission ID from modal data
        const submissionId = $('#submissionModal').data('submission-id');
        if (submissionId) {
            window.open('<?= $this->Url->build(['action' => 'exportSubmission']) ?>/' + submissionId, '_blank');
        }
    });

    // Real-time updates (optional)
    setInterval(function() {
        // Poll for new submissions every 30 seconds
        updateRecentSubmissions();
    }, 30000);

    function updateRecentSubmissions() {
        // AJAX call to get latest submissions
        $.get('<?= $this->Url->build(['action' => 'getRecentSubmissions']) ?>')
            .done(function(data) {
                // Update the recent submissions table
                console.log('Updated submissions:', data);
            });
    }
});
</script>
<?php $this->end(); ?>