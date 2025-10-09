<?php
/**
 * Forms Quiz Management View
 * @var \App\View\AppView $this
 * @var array $quiz_forms
 * @var array $quiz_templates
 */

$this->assign('title', __('Quiz Management'));
$this->start('breadcrumb');
echo $this->Html->link(__('Dashboard'), ['controller' => 'Dashboard', 'action' => 'index']);
echo ' / ' . $this->Html->link(__('Forms Dashboard'), ['action' => 'formsDashboard']);
echo ' / ' . __('Quiz Management');
$this->end();
?>
<?= $this->Html->css('AdminTheme.forms', ['block' => true]) ?>
<?= $this->Html->css('AdminTheme.dashboard', ['block' => true]) ?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2 text-gray-800">
                <i class="fas fa-question-circle text-info me-2"></i>
                <?= __('Quiz Management') ?>
            </h1>
            <p class="mb-0 text-muted"><?= __('Create and manage admin quizzes and knowledge tests') ?></p>
        </div>
        <div class="d-flex gap-2">
            <?= $this->Html->link(
                '<i class="fas fa-user-check me-1"></i>' . __('Customer Quiz'),
                ['action' => 'formsCustomerQuiz'],
                ['class' => 'btn btn-outline-warning', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-arrow-left me-1"></i>' . __('Back to Forms'),
                ['action' => 'formsDashboard'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle mr-2"></i>
                        <?= __('Quiz Configuration') ?>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createQuizModal">
                            <i class="fas fa-plus mr-2"></i><?= __('Create New Quiz') ?>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Quiz Templates Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4><?= __('Quiz Templates') ?></h4>
                            <div class="row">
                                <?php foreach ($quiz_templates ?? [] as $template): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= h($template['name']) ?></h5>
                                            <p class="card-text"><?= h($template['description']) ?></p>
                                            <div class="d-flex justify-content-between">
                                                <span class="badge badge-info">
                                                    <?= $template['question_count'] ?> <?= __('Questions') ?>
                                                </span>
                                                <span class="badge badge-success">
                                                    <?= h($template['difficulty']) ?>
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-primary use-template" 
                                                        data-template-id="<?= $template['id'] ?>">
                                                    <?= __('Use Template') ?>
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary edit-template" 
                                                        data-template-id="<?= $template['id'] ?>">
                                                    <?= __('Edit') ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Active Quizzes Section -->
                    <div class="row">
                        <div class="col-12">
                            <h4><?= __('Active Quizzes') ?></h4>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><?= __('Quiz Name') ?></th>
                                            <th><?= __('Questions') ?></th>
                                            <th><?= __('Submissions') ?></th>
                                            <th><?= __('Average Score') ?></th>
                                            <th><?= __('Status') ?></th>
                                            <th><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($quiz_forms)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                <?= __('No quizzes found. Create your first quiz to get started.') ?>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($quiz_forms as $quiz): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= h($quiz['title']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= h($quiz['description']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        <?= $quiz['question_count'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">
                                                        <?= $quiz['submission_count'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">
                                                        <?= number_format($quiz['average_score'], 1) ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($quiz['is_active']): ?>
                                                        <span class="badge badge-success"><?= __('Active') ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary"><?= __('Inactive') ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary edit-quiz" 
                                                                data-quiz-id="<?= $quiz['id'] ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-success view-results" 
                                                                data-quiz-id="<?= $quiz['id'] ?>">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-quiz" 
                                                                data-quiz-id="<?= $quiz['id'] ?>">
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

    <!-- Navigation Link to Customer Quiz -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="fas fa-info-circle me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1"><?= __('Looking for Customer Quiz Settings?') ?></h5>
                    <p class="mb-0"><?= __('The customer-facing product finder quiz has its own dedicated configuration page.') ?></p>
                </div>
                <div>
                    <?= $this->Html->link(
                        '<i class="fas fa-user-check me-2"></i>' . __('Configure Customer Quiz'),
                        ['action' => 'formsCustomerQuiz'],
                        ['class' => 'btn btn-outline-info', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

<!-- Create Quiz Modal -->
<div class="modal fade" id="createQuizModal" tabindex="-1" role="dialog" aria-labelledby="createQuizModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createQuizModalLabel">
                    <i class="fas fa-plus mr-2"></i><?= __('Create New Quiz') ?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createQuizForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="quiz-title"><?= __('Quiz Title') ?></label>
                        <input type="text" class="form-control" id="quiz-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="quiz-description"><?= __('Description') ?></label>
                        <textarea class="form-control" id="quiz-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quiz-time-limit"><?= __('Time Limit (minutes)') ?></label>
                                <input type="number" class="form-control" id="quiz-time-limit" name="time_limit" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quiz-passing-score"><?= __('Passing Score (%)') ?></label>
                                <input type="number" class="form-control" id="quiz-passing-score" name="passing_score" 
                                       min="0" max="100" value="70">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="quiz-shuffle-questions" name="shuffle_questions">
                            <label class="custom-control-label" for="quiz-shuffle-questions">
                                <?= __('Shuffle Questions') ?>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="quiz-show-results" name="show_results" checked>
                            <label class="custom-control-label" for="quiz-show-results">
                                <?= __('Show Results to Users') ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= __('Cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= __('Create Quiz') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->append('script'); ?>
<script>
// Function to update confidence threshold display
function updateThresholdDisplay(value) {
    document.getElementById('threshold-display').textContent = value + '%';
}

$(document).ready(function() {
    // Use template functionality
    $('.use-template').on('click', function() {
        var templateId = $(this).data('template-id');
        // Handle template usage
        console.log('Using template:', templateId);
    });

    // Edit template functionality
    $('.edit-template').on('click', function() {
        var templateId = $(this).data('template-id');
        // Handle template editing
        console.log('Editing template:', templateId);
    });

    // Create quiz form submission
    $('#createQuizForm').on('submit', function(e) {
        e.preventDefault();
        // Handle quiz creation
        console.log('Creating quiz');
        $('#createQuizModal').modal('hide');
    });

    // Edit quiz functionality
    $('.edit-quiz').on('click', function() {
        var quizId = $(this).data('quiz-id');
        // Handle quiz editing
        console.log('Editing quiz:', quizId);
    });

    // View results functionality
    $('.view-results').on('click', function() {
        var quizId = $(this).data('quiz-id');
        // Handle viewing results
        console.log('Viewing results for quiz:', quizId);
    });

    // Delete quiz functionality
    $('.delete-quiz').on('click', function() {
        var quizId = $(this).data('quiz-id');
        if (confirm('<?= __('Are you sure you want to delete this quiz?') ?>')) {
            // Handle quiz deletion
            console.log('Deleting quiz:', quizId);
        }
    });

    // General quiz management functionality
    console.log('Quiz management interface initialized');
});
</script>
<?php $this->end(); ?>