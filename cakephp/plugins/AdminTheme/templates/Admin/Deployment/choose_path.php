<?php
/**
 * @var \App\View\AppView $this
 */
?>

<header class="py-3 mb-4 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <h1 class="h4 mb-0">
                <i class="bi bi-diagram-3 me-2"></i>
                <?= __('Choose Your Deployment Path') ?>
            </h1>
        </div>
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="bi bi-arrow-left me-1"></i>' . __('Back to Dashboard'),
                ['controller' => 'Articles', 'action' => 'index'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>

<div class="deployment-path-chooser">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Introduction -->
            <div class="text-center mb-5">
                <h2 class="h3 mb-3"><?= __('Choose Your Deployment Path') ?></h2>
                <p class="lead text-muted">
                    <?= __('Select how you want to deploy your Willow CMS project. Choose from linking an existing domain/URL or creating a complete new deployment.') ?>
                </p>
            </div>

            <!-- Path Options -->
            <div class="row g-4">
                <!-- Link Existing URL/Domain -->
                <div class="col-md-6">
                    <div class="deployment-option-card h-100">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center p-4">
                                <!-- Icon -->
                                <div class="deployment-icon mb-4">
                                    <div class="icon-circle link-existing">
                                        <i class="bi bi-link-45deg"></i>
                                    </div>
                                </div>

                                <!-- Title -->
                                <h3 class="h4 mb-3"><?= __('Link Existing URL/Domain') ?></h3>

                                <!-- Description -->
                                <p class="text-muted mb-4">
                                    <?= __('Connect to an existing website, domain, or PHP application that you already have running.') ?>
                                </p>

                                <!-- Features List -->
                                <div class="features-list mb-4">
                                    <div class="feature-item">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span><?= __('Connect to live domain') ?></span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span><?= __('Validate existing setup') ?></span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span><?= __('Cost analysis for current infrastructure') ?></span>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="mt-auto">
                                    <?= $this->Html->link(
                                        __('Choose This Path'),
                                        ['action' => 'linkExisting'],
                                        ['class' => 'btn btn-primary btn-lg px-4']
                                    ) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Create New Deployment -->
                <div class="col-md-6">
                    <div class="deployment-option-card h-100">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center p-4">
                                <!-- Icon -->
                                <div class="deployment-icon mb-4">
                                    <div class="icon-circle create-new">
                                        <i class="bi bi-plus-circle"></i>
                                    </div>
                                </div>

                                <!-- Title -->
                                <h3 class="h4 mb-3"><?= __('Create New Deployment') ?></h3>

                                <!-- Description -->
                                <p class="text-muted mb-4">
                                    <?= __('Build a fresh deployment from scratch with custom files, configurations, and documentation.') ?>
                                </p>

                                <!-- Features List -->
                                <div class="features-list mb-4">
                                    <div class="feature-item">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span><?= __('Upload custom files (HTML, CSS, JS)') ?></span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span><?= __('Generate deployment documentation') ?></span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span><?= __('Complete platform recommendation') ?></span>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="mt-auto">
                                    <?= $this->Html->link(
                                        __('Choose This Path'),
                                        ['action' => 'createNew'],
                                        ['class' => 'btn btn-success btn-lg px-4']
                                    ) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h4 class="card-title">
                                <i class="bi bi-info-circle me-2 text-primary"></i>
                                <?= __('Need Help Choosing?') ?>
                            </h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><?= __('Choose Link Existing if:') ?></h6>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-arrow-right text-primary me-2"></i><?= __('You already have a domain or website') ?></li>
                                        <li><i class="bi bi-arrow-right text-primary me-2"></i><?= __('You want to integrate with existing infrastructure') ?></li>
                                        <li><i class="bi bi-arrow-right text-primary me-2"></i><?= __('You need cost analysis of current setup') ?></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><?= __('Choose Create New if:') ?></h6>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-arrow-right text-success me-2"></i><?= __('You\'re starting from scratch') ?></li>
                                        <li><i class="bi bi-arrow-right text-success me-2"></i><?= __('You want to upload custom design files') ?></li>
                                        <li><i class="bi bi-arrow-right text-success me-2"></i><?= __('You need platform recommendations') ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.deployment-path-chooser {
    min-height: 70vh;
    padding: 2rem 0;
}

.deployment-option-card {
    transition: transform 0.2s ease;
}

.deployment-option-card:hover {
    transform: translateY(-5px);
}

.deployment-icon {
    display: flex;
    justify-content: center;
    align-items: center;
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 2rem;
    color: white;
    margin: 0 auto;
}

.icon-circle.link-existing {
    background: linear-gradient(135deg, #4f84ff 0%, #2196f3 100%);
}

.icon-circle.create-new {
    background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
}

.features-list {
    text-align: left;
    max-width: 300px;
    margin: 0 auto;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Dark theme support */
@media (prefers-color-scheme: dark) {
    .bg-light {
        background-color: var(--bs-gray-800) !important;
        color: var(--bs-light) !important;
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .deployment-path-chooser {
        padding: 1rem 0;
    }
    
    .icon-circle {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .card-body {
        padding: 2rem !important;
    }
}
</style>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    // Add subtle animations to the cards
    const cards = document.querySelectorAll('.deployment-option-card');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 200);
    });

    // Add click tracking for analytics (optional)
    cards.forEach((card) => {
        card.addEventListener('click', function() {
            const pathType = this.querySelector('.icon-circle').classList.contains('link-existing') 
                ? 'link-existing' 
                : 'create-new';
            
            // You can add analytics tracking here
            console.log('Deployment path selected:', pathType);
        });
    });
});
<?php $this->Html->scriptEnd(); ?>