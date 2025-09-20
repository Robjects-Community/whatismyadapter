<?php
/**
 * @var \App\View\AppView $this
 * @var array $candidates
 * @var bool $rateLimitExceeded
 * @var int $limit
 */

$this->assign('title', __('Batch Image Generation'));
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= __('Batch Image Generation') ?></h2>
                <div class="btn-group" role="group">
                    <?= $this->Html->link(__('Dashboard'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Html->link(__('Statistics'), ['action' => 'statistics'], ['class' => 'btn btn-outline-info']) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($rateLimitExceeded): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-danger" role="alert">
                <h5 class="alert-heading"><?= __('Rate Limits Exceeded') ?></h5>
                <p><?= __('The API rate limits have been exceeded. You can either wait for them to reset or use the "Force" option to bypass the limits.') ?></p>
                <hr>
                <p class="mb-0">
                    <small class="text-muted"><?= __('Warning: Forcing may result in API errors or additional charges from providers.') ?></small>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Batch Processing Form -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= __('Processing Options') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    
                    <div class="mb-3">
                        <?= $this->Form->control('limit', [
                            'type' => 'number',
                            'label' => __('Number of Articles to Process'),
                            'value' => $limit,
                            'min' => 1,
                            'max' => 500,
                            'class' => 'form-control',
                            'help' => __('Maximum number of articles to queue for image generation (1-500)')
                        ]) ?>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <?= $this->Form->checkbox('force', [
                                'class' => 'form-check-input',
                                'label' => false,
                                'value' => 1
                            ]) ?>
                            <label class="form-check-label" for="force">
                                <?= __('Force Processing') ?>
                            </label>
                            <div class="form-text">
                                <?= __('Bypass rate limits and safety checks. Use with caution.') ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <?= $this->Form->button(__('Start Batch Processing'), [
                            'class' => 'btn btn-primary',
                            'id' => 'startBatchBtn'
                        ]) ?>
                    </div>

                    <?= $this->Form->end() ?>

                    <hr class="my-4">

                    <div class="text-center">
                        <h6 class="text-muted mb-3"><?= __('Quick Actions') ?></h6>
                        <div class="d-grid gap-2">
                            <?= $this->Html->link(__('Process 10 Articles'), ['?' => ['limit' => 10]], [
                                'class' => 'btn btn-sm btn-outline-primary'
                            ]) ?>
                            <?= $this->Html->link(__('Process 50 Articles'), ['?' => ['limit' => 50]], [
                                'class' => 'btn btn-sm btn-outline-primary'
                            ]) ?>
                            <?= $this->Html->link(__('Process 100 Articles'), ['?' => ['limit' => 100]], [
                                'class' => 'btn btn-sm btn-outline-primary'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles Preview -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <?= __('Articles to be Processed ({0})', number_format(count($candidates))) ?>
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" onclick="selectAll()">
                            <?= __('Select All') ?>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="selectNone()">
                            <?= __('Select None') ?>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($candidates)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h4><?= __('No Articles Need Images') ?></h4>
                            <p class="text-muted">
                                <?= __('All published articles already have images, or no articles match the criteria.') ?>
                            </p>
                            <?= $this->Html->link(__('Back to Dashboard'), ['action' => 'index'], [
                                'class' => 'btn btn-primary'
                            ]) ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll()">
                                        </th>
                                        <th><?= __('Title') ?></th>
                                        <th><?= __('Published') ?></th>
                                        <th><?= __('Words') ?></th>
                                        <th><?= __('Actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($candidates as $index => $article): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="article-checkbox" 
                                                   value="<?= h($article->id) ?>" checked>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <strong><?= h($article->title) ?></strong>
                                                    <br>
                                                    <small class="text-muted">ID: <?= h($article->id) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small>
                                                <?= $article->published ? $article->published->format('M j, Y g:i A') : __('Draft') ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge <?= ($article->word_count ?? 0) > 500 ? 'bg-success' : 'bg-info' ?>">
                                                <?= number_format($article->word_count ?? 0) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?= $this->Html->link('<i class="fas fa-eye"></i>', [
                                                    'controller' => 'Articles',
                                                    'action' => 'view',
                                                    $article->id
                                                ], [
                                                    'class' => 'btn btn-outline-info btn-sm',
                                                    'escape' => false,
                                                    'title' => __('View Article'),
                                                    'data-bs-toggle' => 'tooltip'
                                                ]) ?>
                                                <?= $this->Html->link('<i class="fas fa-edit"></i>', [
                                                    'controller' => 'Articles',
                                                    'action' => 'edit',
                                                    $article->id
                                                ], [
                                                    'class' => 'btn btn-outline-secondary btn-sm',
                                                    'escape' => false,
                                                    'title' => __('Edit Article'),
                                                    'data-bs-toggle' => 'tooltip'
                                                ]) ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <div class="row align-items-center">
                                <div class="col">
                                    <small class="text-muted">
                                        <?= __('Showing {0} articles that need images. Processing will queue these articles for AI image generation.', number_format(count($candidates))) ?>
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <span class="badge bg-primary" id="selectedCount">
                                        <?= count($candidates) ?> <?= __('selected') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.article-checkbox:checked');
    const count = checkboxes.length;
    document.getElementById('selectedCount').textContent = count + ' <?= __('selected') ?>';
    
    // Update the select all checkbox state
    const allCheckboxes = document.querySelectorAll('.article-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    if (count === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (count === allCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
}

function toggleAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.article-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateSelectedCount();
}

function selectAll() {
    document.querySelectorAll('.article-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedCount();
}

function selectNone() {
    document.querySelectorAll('.article-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedCount();
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add event listeners to article checkboxes
    document.querySelectorAll('.article-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Initialize count
    updateSelectedCount();

    // Handle form submission with loading state
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('startBatchBtn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            // Check if any articles are selected
            const selectedCount = document.querySelectorAll('.article-checkbox:checked').length;
            
            if (selectedCount === 0) {
                e.preventDefault();
                alert('<?= __('Please select at least one article to process.') ?>');
                return;
            }

            // Show loading state
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span><?= __('Processing...') ?>';
            submitBtn.disabled = true;

            // Add selected article IDs to form data
            const selectedIds = [];
            document.querySelectorAll('.article-checkbox:checked').forEach(checkbox => {
                selectedIds.push(checkbox.value);
            });

            // Create hidden input with selected IDs
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'selected_articles';
            hiddenInput.value = JSON.stringify(selectedIds);
            form.appendChild(hiddenInput);
        });
    }
});
<?php $this->Html->scriptEnd(); ?>