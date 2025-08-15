<?php
/**
 * @var \App\View\AppView $this
 * @var array $formSettings Current form configuration settings
 * @var array $submissionStats Statistics about user submissions
 * @var array $recentSubmissions Recent user submissions
 */

$this->assign('title', __('Product Forms Configuration'));
$this->assign('page_title', __('Product Forms Configuration'));
$this->assign('page_subtitle', __('Configure frontend product submission forms and workflow'));

// Include the products tabs navigation
echo $this->element('AdminTheme.nav/products_tabs');
?>

<div class="products-forms-config">
    <div class="row">
        <!-- Configuration Form Column -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        <?= __('Form Configuration') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        <?= __('Configure how frontend product submission forms work. These settings control the user experience and admin workflow for product submissions.') ?>
                    </p>

                    <?= $this->Form->create(null, [
                        'method' => 'post',
                        'class' => 'needs-validation',
                        'novalidate' => true,
                    ]) ?>

                    <div class="row">
                        <!-- Public Submissions -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <?= $this->Form->label('enable_public_submissions', __('Enable Public Submissions'), [
                                    'class' => 'form-label fw-bold',
                                ]) ?>
                                <div class="form-check form-switch">
                                    <?= $this->Form->checkbox('enable_public_submissions', [
                                        'value' => 'true',
                                        'checked' => $formSettings['enable_public_submissions'] === 'true',
                                        'class' => 'form-check-input',
                                        'id' => 'enable_public_submissions',
                                    ]) ?>
                                </div>
                                <small class="form-text text-muted">
                                    <?= __('Allow non-admin users to submit products via frontend forms') ?>
                                </small>
                            </div>
                        </div>

                        <!-- Require Admin Approval -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <?= $this->Form->label('require_admin_approval', __('Require Admin Approval'), [
                                    'class' => 'form-label fw-bold',
                                ]) ?>
                                <div class="form-check form-switch">
                                    <?= $this->Form->checkbox('require_admin_approval', [
                                        'value' => 'true',
                                        'checked' => $formSettings['require_admin_approval'] === 'true',
                                        'class' => 'form-check-input',
                                        'id' => 'require_admin_approval',
                                    ]) ?>
                                </div>
                                <small class="form-text text-muted">
                                    <?= __('User submissions require admin approval before publication') ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Default Status -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <?= $this->Form->label('default_status', __('Default Status'), [
                                    'class' => 'form-label fw-bold',
                                ]) ?>
                                <?= $this->Form->select('default_status', [
                                    'pending' => __('Pending Review'),
                                    'approved' => __('Approved'),
                                    'rejected' => __('Rejected'),
                                ], [
                                    'value' => $formSettings['default_status'],
                                    'class' => 'form-select',
                                    'id' => 'default_status',
                                ]) ?>
                                <small class="form-text text-muted">
                                    <?= __('Initial verification status for user-submitted products') ?>
                                </small>
                            </div>
                        </div>

                        <!-- Max File Size -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <?= $this->Form->label('max_file_size', __('Max File Size (MB)'), [
                                    'class' => 'form-label fw-bold',
                                ]) ?>
                                <div class="input-group">
                                    <?= $this->Form->number('max_file_size', [
                                        'value' => $formSettings['max_file_size'],
                                        'class' => 'form-control',
                                        'min' => 1,
                                        'max' => 100,
                                        'step' => 0.1,
                                        'id' => 'max_file_size',
                                    ]) ?>
                                    <span class="input-group-text">MB</span>
                                </div>
                                <small class="form-text text-muted">
                                    <?= __('Maximum file size for product image uploads') ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Allowed File Types -->
                    <div class="mb-3">
                        <?= $this->Form->label('allowed_file_types', __('Allowed File Types'), [
                            'class' => 'form-label fw-bold',
                        ]) ?>
                        <?= $this->Form->text('allowed_file_types', [
                            'value' => $formSettings['allowed_file_types'],
                            'class' => 'form-control',
                            'placeholder' => 'jpg,jpeg,png,gif,webp',
                            'id' => 'allowed_file_types',
                        ]) ?>
                        <small class="form-text text-muted">
                            <?= __('Comma-separated list of allowed file extensions (without dots)') ?>
                        </small>
                    </div>

                    <!-- Required Fields -->
                    <div class="mb-3">
                        <?= $this->Form->label('required_fields', __('Required Fields'), [
                            'class' => 'form-label fw-bold',
                        ]) ?>
                        <?= $this->Form->text('required_fields', [
                            'value' => $formSettings['required_fields'],
                            'class' => 'form-control',
                            'placeholder' => 'title,description,manufacturer,price',
                            'id' => 'required_fields',
                        ]) ?>
                        <small class="form-text text-muted">
                            <?= __('Comma-separated list of required form fields') ?>
                        </small>
                        <div class="mt-1">
                            <small class="text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                <?= __('Available fields: title, description, manufacturer, model_number, price, image, tags') ?>
                            </small>
                        </div>
                    </div>

                    <!-- Notification Email -->
                    <div class="mb-3">
                        <?= $this->Form->label('notification_email', __('Notification Email'), [
                            'class' => 'form-label fw-bold',
                        ]) ?>
                        <?= $this->Form->email('notification_email', [
                            'value' => $formSettings['notification_email'],
                            'class' => 'form-control',
                            'placeholder' => 'admin@example.com',
                            'id' => 'notification_email',
                        ]) ?>
                        <small class="form-text text-muted">
                            <?= __('Email address to notify when new products are submitted (leave empty to disable)') ?>
                        </small>
                    </div>

                    <!-- Success Message -->
                    <div class="mb-4">
                        <?= $this->Form->label('success_message', __('Success Message'), [
                            'class' => 'form-label fw-bold',
                        ]) ?>
                        <?= $this->Form->textarea('success_message', [
                            'value' => $formSettings['success_message'],
                            'class' => 'form-control',
                            'rows' => 3,
                            'placeholder' => 'Your product has been submitted and is awaiting review.',
                            'id' => 'success_message',
                        ]) ?>
                        <small class="form-text text-muted">
                            <?= __('Message shown to users after successful product submission') ?>
                        </small>
                    </div>

                    <div class="d-flex justify-content-end">
                        <?= $this->Form->button(__('Save Configuration'), [
                            'type' => 'submit',
                            'class' => 'btn btn-primary btn-lg',
                        ]) ?>
                    </div>

                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>

        <!-- Statistics Column -->
        <div class="col-md-4">
            <!-- Submission Statistics -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        <?= __('Submission Statistics') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item mb-3">
                                <div class="stat-number text-primary fs-4 fw-bold">
                                    <?= number_format($submissionStats['total_submissions']) ?>
                                </div>
                                <div class="stat-label text-muted small">
                                    <?= __('Total Submissions') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item mb-3">
                                <div class="stat-number text-warning fs-4 fw-bold">
                                    <?= number_format($submissionStats['pending_submissions']) ?>
                                </div>
                                <div class="stat-label text-muted small">
                                    <?= __('Pending Review') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number text-success fs-4 fw-bold">
                                    <?= number_format($submissionStats['approved_submissions']) ?>
                                </div>
                                <div class="stat-label text-muted small">
                                    <?= __('Approved') ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <div class="stat-number text-danger fs-4 fw-bold">
                                    <?= number_format($submissionStats['rejected_submissions']) ?>
                                </div>
                                <div class="stat-label text-muted small">
                                    <?= __('Rejected') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($submissionStats['pending_submissions'] > 0) : ?>
                        <div class="mt-3">
                            <?= $this->Html->link(
                                __('Review Pending Products'),
                                ['action' => 'pendingReview'],
                                ['class' => 'btn btn-outline-warning btn-sm w-100'],
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Submissions -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        <?= __('Recent Submissions') ?>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentSubmissions)) : ?>
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            <?= __('No user submissions yet') ?>
                        </div>
                    <?php else : ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($recentSubmissions, 0, 5) as $submission) : ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <?= $this->Html->link(
                                                    h($submission->title),
                                                    ['action' => 'view', $submission->id],
                                                    ['class' => 'text-decoration-none'],
                                                ) ?>
                                            </h6>
                                            <p class="mb-1 small text-muted">
                                                <?= __('by {0}', h($submission->user->username ?? 'Unknown User')) ?>
                                            </p>
                                            <small class="text-muted">
                                                <?= $submission->created->timeAgoInWords() ?>
                                            </small>
                                        </div>
                                        <span class="badge <?=
                                            $submission->verification_status === 'approved' ? 'bg-success' :
                                            ($submission->verification_status === 'rejected' ? 'bg-danger' : 'bg-warning')
                                        ?>">
                                            <?= ucfirst(h($submission->verification_status)) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($recentSubmissions) > 5) : ?>
                            <div class="card-footer text-center">
                                <?= $this->Html->link(
                                    __('View All Submissions'),
                                    ['action' => 'index'],
                                    ['class' => 'btn btn-outline-secondary btn-sm'],
                                ) ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Guide -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        <?= __('Configuration Guide') ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><?= __('Form Workflow') ?></h6>
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong><?= __('Pending Status:') ?></strong> <?= __('Products await admin review and are not published') ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong><?= __('Approved Status:') ?></strong> <?= __('Products are automatically published') ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-times-circle text-danger me-2"></i>
                                    <strong><?= __('Rejected Status:') ?></strong> <?= __('Products remain unpublished') ?>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><?= __('Best Practices') ?></h6>
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning me-2"></i>
                                    <?= __('Start with "pending" status for quality control') ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning me-2"></i>
                                    <?= __('Keep file sizes under 10MB for performance') ?>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning me-2"></i>
                                    <?= __('Set notification email to stay informed of submissions') ?>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong><?= __('Frontend Integration:') ?></strong>
                        <?= __(
                            'Users can submit products at {0} when public submissions are enabled.',
                            $this->Html->link(
                                '/products/add',
                                ['controller' => 'Products', 'action' => 'add', 'prefix' => false],
                                ['class' => 'alert-link'],
                            ),
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->append('css'); ?>
<style>
.products-forms-config .stat-item {
    padding: 0.5rem;
    border-radius: 0.25rem;
}

.products-forms-config .stat-number {
    line-height: 1;
}

.products-forms-config .form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.products-forms-config .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.products-forms-config .list-group-item {
    border-left: none;
    border-right: none;
}

.products-forms-config .list-group-item:first-child {
    border-top: none;
}

.products-forms-config .list-group-item:last-child {
    border-bottom: none;
}
</style>
<?php $this->end(); ?>

<?php $this->append('script'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Interactive features
    const enableSubmissions = document.getElementById('enable_public_submissions');
    const requireApproval = document.getElementById('require_admin_approval');
    const defaultStatus = document.getElementById('default_status');

    function updateFormLogic() {
        if (enableSubmissions.checked) {
            requireApproval.closest('.col-md-6').style.opacity = '1';
            defaultStatus.closest('.col-md-6').style.opacity = '1';
        } else {
            requireApproval.closest('.col-md-6').style.opacity = '0.5';
            defaultStatus.closest('.col-md-6').style.opacity = '0.5';
        }
    }

    enableSubmissions.addEventListener('change', updateFormLogic);
    updateFormLogic(); // Initial state
});
</script>
<?php $this->end(); ?>
