<?php
/**
 * @var \App\View\AppView $this
 * @var string $domain
 * @var array $verificationResult
 */
?>

<header class="py-3 mb-4 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <h1 class="h4 mb-0">
                <i class="bi bi-shield-check me-2"></i>
                <?= __('Domain Verification') ?>
            </h1>
        </div>
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="bi bi-arrow-left me-1"></i>' . __('Back to Link Existing'),
                ['action' => 'linkExisting'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Domain Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle link-existing me-3">
                        <i class="bi bi-globe"></i>
                    </div>
                    <div>
                        <h2 class="h5 mb-1"><?= __('Verifying Domain: {0}', h($domain)) ?></h2>
                        <p class="text-muted mb-0"><?= __('Checking connectivity and verification status') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Status -->
        <?php
        $statusClass = 'primary';
        $statusIcon = 'clock';
        $statusText = __('Pending');
        
        switch ($verificationResult['status']) {
            case 'verified':
                $statusClass = 'success';
                $statusIcon = 'check-circle';
                $statusText = __('Verified');
                break;
            case 'failed':
                $statusClass = 'danger';
                $statusIcon = 'x-circle';
                $statusText = __('Failed');
                break;
            case 'error':
                $statusClass = 'warning';
                $statusIcon = 'exclamation-triangle';
                $statusText = __('Error');
                break;
        }
        ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-<?= $statusClass ?> text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-<?= $statusIcon ?> me-2"></i>
                    <?= __('Verification Status: {0}', $statusText) ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-<?= $statusClass ?> mb-3">
                    <i class="bi bi-<?= $statusIcon ?> me-2"></i>
                    <?= h($verificationResult['message']) ?>
                </div>

                <?php if (!empty($verificationResult['checks'])): ?>
                    <h5 class="mb-3"><?= __('Verification Checks') ?></h5>
                    
                    <?php foreach ($verificationResult['checks'] as $checkType => $checkResult): ?>
                        <div class="verification-check mb-3">
                            <div class="d-flex align-items-start">
                                <div class="check-icon me-3">
                                    <?php if ($checkResult['passed']): ?>
                                        <i class="bi bi-check-circle text-success fs-4"></i>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle text-danger fs-4"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="check-content flex-grow-1">
                                    <h6 class="check-title mb-1">
                                        <?php
                                        switch ($checkType) {
                                            case 'connectivity':
                                                echo __('Domain Connectivity');
                                                break;
                                            case 'dns':
                                                echo __('DNS Verification');
                                                break;
                                            case 'file':
                                                echo __('File Verification');
                                                break;
                                            case 'meta':
                                                echo __('Meta Tag Verification');
                                                break;
                                            default:
                                                echo h(ucfirst($checkType));
                                        }
                                        ?>
                                    </h6>
                                    <p class="check-message mb-0 text-muted">
                                        <?= h($checkResult['message']) ?>
                                    </p>
                                </div>
                                <div class="check-status">
                                    <?php if ($checkResult['passed']): ?>
                                        <span class="badge bg-success"><?= __('Passed') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?= __('Failed') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($verificationResult['status'] === 'failed' || $verificationResult['status'] === 'error'): ?>
            <!-- Troubleshooting Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-tools me-2"></i>
                        <?= __('Troubleshooting Guide') ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="troubleshoot-item">
                                <h6><i class="bi bi-file-text text-primary me-2"></i><?= __('File Verification') ?></h6>
                                <p class="small text-muted mb-2"><?= __('Create a file named:') ?></p>
                                <code>willow-cms-verification.txt</code>
                                <p class="small text-muted mt-2"><?= __('Upload to your domain root directory and ensure it\'s accessible at:') ?></p>
                                <code><?= h($domain) ?>/willow-cms-verification.txt</code>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="troubleshoot-item">
                                <h6><i class="bi bi-code-slash text-info me-2"></i><?= __('Meta Tag Verification') ?></h6>
                                <p class="small text-muted mb-2"><?= __('Add this meta tag to your page <head>:') ?></p>
                                <code>&lt;meta name="willow-cms-verification" content="unique-token" /&gt;</code>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="troubleshoot-item">
                                <h6><i class="bi bi-globe text-warning me-2"></i><?= __('Common Issues') ?></h6>
                                <ul class="small text-muted">
                                    <li><?= __('Check domain is accessible') ?></li>
                                    <li><?= __('Verify SSL certificate') ?></li>
                                    <li><?= __('Ensure no firewall blocking') ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($verificationResult['status'] === 'verified'): ?>
            <!-- Success Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="bi bi-check2-all me-2"></i>
                        <?= __('Next Steps') ?>
                    </h3>
                </div>
                <div class="card-body">
                    <p class="mb-3"><?= __('Great! Your domain has been successfully verified. Here\'s what you can do next:') ?></p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-grid gap-2">
                                <?= $this->Html->link(
                                    '<i class="bi bi-graph-up me-2"></i>' . __('View Cost Analysis'),
                                    ['controller' => 'Pages', 'action' => 'costAnalysis'],
                                    ['class' => 'btn btn-primary', 'escape' => false]
                                ) ?>
                                
                                <?= $this->Html->link(
                                    '<i class="bi bi-gear me-2"></i>' . __('Configure Integration'),
                                    ['controller' => 'Pages', 'action' => 'add'],
                                    ['class' => 'btn btn-outline-primary', 'escape' => false]
                                ) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-title"><?= __('What\'s Next?') ?></h6>
                                    <ul class="list-unstyled mb-0 small">
                                        <li><i class="bi bi-check text-success me-2"></i><?= __('Domain verified successfully') ?></li>
                                        <li><i class="bi bi-arrow-right text-primary me-2"></i><?= __('Review cost analysis') ?></li>
                                        <li><i class="bi bi-arrow-right text-primary me-2"></i><?= __('Set up content integration') ?></li>
                                        <li><i class="bi bi-arrow-right text-primary me-2"></i><?= __('Configure deployment settings') ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?php if ($verificationResult['status'] !== 'verified'): ?>
                                    <?= $this->Html->link(
                                        '<i class="bi bi-arrow-clockwise me-2"></i>' . __('Retry Verification'),
                                        ['action' => 'verifyConnection', '?' => ['domain' => $domain]],
                                        ['class' => 'btn btn-primary', 'escape' => false]
                                    ) ?>
                                <?php endif; ?>
                                
                                <?= $this->Html->link(
                                    '<i class="bi bi-arrow-left me-2"></i>' . __('Try Different Domain'),
                                    ['action' => 'linkExisting'],
                                    ['class' => 'btn btn-outline-secondary', 'escape' => false]
                                ) ?>
                            </div>
                            <div>
                                <?= $this->Html->link(
                                    '<i class="bi bi-plus-circle me-2"></i>' . __('Create New Instead'),
                                    ['action' => 'createNew'],
                                    ['class' => 'btn btn-success', 'escape' => false]
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.25rem;
    color: white;
    flex-shrink: 0;
}

.icon-circle.link-existing {
    background: linear-gradient(135deg, #4f84ff 0%, #2196f3 100%);
}

.verification-check {
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
}

.verification-check:last-child {
    margin-bottom: 0 !important;
}

.check-icon {
    flex-shrink: 0;
}

.check-title {
    color: #333;
    font-weight: 600;
}

.troubleshoot-item {
    padding: 1rem;
    height: 100%;
    border-left: 3px solid #dee2e6;
    background-color: #f8f9fa;
    border-radius: 0 0.375rem 0.375rem 0;
}

.troubleshoot-item h6 {
    color: #495057;
    margin-bottom: 0.75rem;
}

.troubleshoot-item code {
    font-size: 0.85rem;
    background-color: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    display: block;
    margin: 0.25rem 0;
    word-break: break-all;
}

/* Status animations */
.card-header.bg-success {
    animation: successPulse 2s ease-in-out infinite;
}

@keyframes successPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.9; }
}

.bi-check-circle.text-success {
    animation: checkBounce 0.6s ease-out;
}

@keyframes checkBounce {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Dark theme adjustments */
@media (prefers-color-scheme: dark) {
    .verification-check,
    .troubleshoot-item {
        background-color: var(--bs-gray-800);
        border-color: var(--bs-gray-600);
    }
    
    .troubleshoot-item code {
        background-color: var(--bs-gray-700);
        color: var(--bs-light);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .troubleshoot-item {
        margin-bottom: 1rem;
        border-left: none;
        border-top: 3px solid #dee2e6;
        border-radius: 0.375rem;
    }
    
    .verification-check {
        margin-bottom: 1rem;
    }
}
</style>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh for pending verification
    <?php if ($verificationResult['status'] === 'pending'): ?>
    setTimeout(function() {
        window.location.reload();
    }, 30000); // Refresh every 30 seconds for pending verification
    <?php endif; ?>
    
    // Add success animations if verified
    <?php if ($verificationResult['status'] === 'verified'): ?>
    // Trigger success animations
    const successElements = document.querySelectorAll('.bi-check-circle.text-success');
    successElements.forEach((element, index) => {
        element.style.animationDelay = (index * 0.2) + 's';
    });
    <?php endif; ?>
    
    // Copy functionality for code blocks
    const codeElements = document.querySelectorAll('code');
    codeElements.forEach(codeEl => {
        codeEl.addEventListener('click', function() {
            if (this.textContent.trim().length > 0) {
                navigator.clipboard.writeText(this.textContent).then(() => {
                    // Show brief success indication
                    const originalBg = this.style.backgroundColor;
                    this.style.backgroundColor = '#d4edda';
                    setTimeout(() => {
                        this.style.backgroundColor = originalBg;
                    }, 1000);
                });
            }
        });
    });
});
<?php $this->Html->scriptEnd(); ?>