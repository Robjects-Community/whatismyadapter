<?php
/**
 * Comprehensive Quiz Template
 * 
 * Multi-step form with detailed questions and server-side fallback support
 */

$this->assign('title', __('Comprehensive Product Quiz'));
$this->Html->meta('description', __('Answer detailed questions about your device, usage, and preferences for comprehensive product recommendations.'), ['block' => 'meta']);

// Include the quiz JavaScript module
$this->Html->script('DefaultTheme.quiz', ['block' => 'script']);
?>

<div class="quiz-comprehensive">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="quiz-header text-center mb-4">
                    <h1 class="quiz-title"><?= __('Comprehensive Product Quiz') ?></h1>
                    <p class="quiz-description">
                        <?= __('Answer detailed questions to get personalized product recommendations tailored to your specific needs.') ?>
                    </p>
                </div>

                <!-- Progress Bar -->
                <div class="progress-container mb-4">
                    <div class="progress-header">
                        <span class="step-indicator">
                            <?= __('Step') ?> <span id="current-step">1</span> <?= __('of') ?> <span id="total-steps">5</span>
                        </span>
                        <span class="completion-indicator">
                            <span id="completion-percentage">0</span>% <?= __('Complete') ?>
                        </span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" id="main-progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Quiz Form -->
                <?= $this->Form->create(null, [
                    'id' => 'comprehensive-quiz-form',
                    'url' => ['action' => 'submit'],
                    'class' => 'comprehensive-quiz-form'
                ]) ?>

                <!-- Step 1: Device Information -->
                <div class="quiz-step" id="step-1" data-step="1">
                    <div class="card step-card">
                        <div class="card-header">
                            <h4 class="step-title">
                                <i class="fas fa-laptop text-primary"></i>
                                <?= __('Tell us about your device') ?>
                            </h4>
                            <p class="step-description"><?= __('Help us understand what you need to connect') ?></p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <?= $this->Form->control('device_type', [
                                        'type' => 'select',
                                        'label' => __('Device Type'),
                                        'options' => [
                                            '' => __('Select device type...'),
                                            'laptop' => __('Laptop'),
                                            'desktop' => __('Desktop Computer'),
                                            'tablet' => __('Tablet'),
                                            'smartphone' => __('Smartphone'),
                                            'gaming_console' => __('Gaming Console'),
                                            'tv' => __('TV/Monitor'),
                                            'camera' => __('Camera'),
                                            'audio_device' => __('Audio Device'),
                                            'other' => __('Other')
                                        ],
                                        'class' => 'form-select',
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $this->Form->control('device_brand', [
                                        'type' => 'text',
                                        'label' => __('Brand (Optional)'),
                                        'placeholder' => __('e.g., Apple, Dell, Samsung'),
                                        'class' => 'form-control'
                                    ]) ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <?= $this->Form->control('device_model', [
                                        'type' => 'text',
                                        'label' => __('Model (Optional)'),
                                        'placeholder' => __('e.g., MacBook Pro, XPS 15'),
                                        'class' => 'form-control'
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $this->Form->control('device_year', [
                                        'type' => 'select',
                                        'label' => __('Approximate Year'),
                                        'options' => array_merge(['' => __('Select year...')], 
                                            array_combine(
                                                range(date('Y'), date('Y') - 15), 
                                                range(date('Y'), date('Y') - 15)
                                            )
                                        ),
                                        'class' => 'form-select'
                                    ]) ?>
                                </div>
                            </div>

                            <?= $this->Form->control('existing_ports', [
                                'type' => 'textarea',
                                'label' => __('Existing Ports/Connections'),
                                'placeholder' => __('List the ports your device has (USB, HDMI, etc.)'),
                                'class' => 'form-control',
                                'rows' => 3
                            ]) ?>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Connection Requirements -->
                <div class="quiz-step" id="step-2" data-step="2" style="display: none;">
                    <div class="card step-card">
                        <div class="card-header">
                            <h4 class="step-title">
                                <i class="fas fa-plug text-success"></i>
                                <?= __('What do you want to connect?') ?>
                            </h4>
                            <p class="step-description"><?= __('Select all devices you plan to connect') ?></p>
                        </div>
                        <div class="card-body">
                            <div class="connection-options">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><?= __('Display Devices') ?></h6>
                                        <?= $this->Form->control('connections.display', [
                                            'type' => 'select',
                                            'multiple' => 'checkbox',
                                            'options' => [
                                                'monitor' => __('External Monitor'),
                                                'tv' => __('TV'),
                                                'projector' => __('Projector'),
                                                'dual_monitors' => __('Dual Monitors'),
                                                '4k_display' => __('4K Display')
                                            ],
                                            'label' => false,
                                            'hiddenField' => false
                                        ]) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><?= __('Storage Devices') ?></h6>
                                        <?= $this->Form->control('connections.storage', [
                                            'type' => 'select',
                                            'multiple' => 'checkbox',
                                            'options' => [
                                                'external_drive' => __('External Hard Drive'),
                                                'usb_flash' => __('USB Flash Drive'),
                                                'sd_card' => __('SD Card'),
                                                'ssd' => __('External SSD'),
                                                'network_storage' => __('Network Storage')
                                            ],
                                            'label' => false,
                                            'hiddenField' => false
                                        ]) ?>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h6><?= __('Audio Devices') ?></h6>
                                        <?= $this->Form->control('connections.audio', [
                                            'type' => 'select',
                                            'multiple' => 'checkbox',
                                            'options' => [
                                                'headphones' => __('Headphones'),
                                                'speakers' => __('External Speakers'),
                                                'microphone' => __('Microphone'),
                                                'audio_interface' => __('Audio Interface'),
                                                'sound_system' => __('Home Sound System')
                                            ],
                                            'label' => false,
                                            'hiddenField' => false
                                        ]) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><?= __('Other Devices') ?></h6>
                                        <?= $this->Form->control('connections.other', [
                                            'type' => 'select',
                                            'multiple' => 'checkbox',
                                            'options' => [
                                                'keyboard' => __('Keyboard'),
                                                'mouse' => __('Mouse'),
                                                'printer' => __('Printer'),
                                                'camera' => __('Camera'),
                                                'ethernet' => __('Ethernet/Network'),
                                                'charging' => __('Charging Cable')
                                            ],
                                            'label' => false,
                                            'hiddenField' => false
                                        ]) ?>
                                    </div>
                                </div>
                            </div>

                            <?= $this->Form->control('other_connections', [
                                'type' => 'text',
                                'label' => __('Other devices not listed'),
                                'placeholder' => __('Describe any other devices you need to connect'),
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Usage & Performance -->
                <div class="quiz-step" id="step-3" data-step="3" style="display: none;">
                    <div class="card step-card">
                        <div class="card-header">
                            <h4 class="step-title">
                                <i class="fas fa-cogs text-warning"></i>
                                <?= __('How will you use it?') ?>
                            </h4>
                            <p class="step-description"><?= __('Help us understand your performance requirements') ?></p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <?= $this->Form->control('primary_use', [
                                        'type' => 'radio',
                                        'label' => __('Primary Use Case'),
                                        'options' => [
                                            'basic' => __('Basic computing (web, email, documents)'),
                                            'multimedia' => __('Multimedia (videos, photos, presentations)'),
                                            'gaming' => __('Gaming'),
                                            'professional' => __('Professional work (design, video editing)'),
                                            'development' => __('Software development'),
                                            'business' => __('Business/office use')
                                        ],
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $this->Form->control('performance_level', [
                                        'type' => 'radio',
                                        'label' => __('Performance Needs'),
                                        'options' => [
                                            'basic' => __('Basic - Standard definition, basic tasks'),
                                            'standard' => __('Standard - HD content, office work'),
                                            'high' => __('High - 4K content, gaming, multitasking'),
                                            'professional' => __('Professional - High-end creative work')
                                        ],
                                        'required' => true
                                    ]) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <?= $this->Form->control('portability', [
                                        'type' => 'radio',
                                        'label' => __('Portability Requirements'),
                                        'options' => [
                                            'stationary' => __('Stationary - Stays in one place'),
                                            'occasional' => __('Occasional - Move it sometimes'),
                                            'frequent' => __('Frequent - Travel/move regularly'),
                                            'daily' => __('Daily - Need maximum portability')
                                        ],
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $this->Form->control('power_requirements', [
                                        'type' => 'radio',
                                        'label' => __('Power Requirements'),
                                        'options' => [
                                            'no_preference' => __('No preference'),
                                            'low_power' => __('Low power consumption preferred'),
                                            'battery_powered' => __('Battery powered preferred'),
                                            'high_power' => __('High power is fine for performance')
                                        ]
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Budget & Preferences -->
                <div class="quiz-step" id="step-4" data-step="4" style="display: none;">
                    <div class="card step-card">
                        <div class="card-header">
                            <h4 class="step-title">
                                <i class="fas fa-dollar-sign text-info"></i>
                                <?= __('Budget and preferences') ?>
                            </h4>
                            <p class="step-description"><?= __('Let us know your budget and feature preferences') ?></p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <?= $this->Form->control('budget_range', [
                                        'type' => 'radio',
                                        'label' => __('Budget Range'),
                                        'options' => [
                                            'under_25' => __('Under $25'),
                                            '25_50' => __('$25 - $50'),
                                            '50_100' => __('$50 - $100'),
                                            '100_200' => __('$100 - $200'),
                                            '200_500' => __('$200 - $500'),
                                            'over_500' => __('Over $500'),
                                            'no_budget' => __('No specific budget')
                                        ],
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $this->Form->control('brand_preference', [
                                        'type' => 'radio',
                                        'label' => __('Brand Preference'),
                                        'options' => [
                                            'no_preference' => __('No preference'),
                                            'well_known' => __('Prefer well-known brands'),
                                            'value' => __('Best value for money'),
                                            'premium' => __('Premium/high-end brands'),
                                            'eco_friendly' => __('Eco-friendly/sustainable brands')
                                        ]
                                    ]) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <?= $this->Form->control('warranty_importance', [
                                        'type' => 'radio',
                                        'label' => __('Warranty Importance'),
                                        'options' => [
                                            'not_important' => __('Not important'),
                                            'somewhat' => __('Somewhat important'),
                                            'important' => __('Important'),
                                            'very_important' => __('Very important')
                                        ]
                                    ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $this->Form->control('future_proofing', [
                                        'type' => 'radio',
                                        'label' => __('Future-proofing'),
                                        'options' => [
                                            'current_needs' => __('Just meet current needs'),
                                            'some_growth' => __('Allow for some growth'),
                                            'future_proof' => __('Highly future-proof'),
                                            'cutting_edge' => __('Latest technology preferred')
                                        ]
                                    ]) ?>
                                </div>
                            </div>

                            <?= $this->Form->control('special_requirements', [
                                'type' => 'textarea',
                                'label' => __('Special Requirements'),
                                'placeholder' => __('Any specific features, compatibility needs, or other requirements?'),
                                'class' => 'form-control',
                                'rows' => 3
                            ]) ?>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Review & Submit -->
                <div class="quiz-step" id="step-5" data-step="5" style="display: none;">
                    <div class="card step-card">
                        <div class="card-header">
                            <h4 class="step-title">
                                <i class="fas fa-check-circle text-success"></i>
                                <?= __('Review and submit') ?>
                            </h4>
                            <p class="step-description"><?= __('Review your answers and get your recommendations') ?></p>
                        </div>
                        <div class="card-body">
                            <!-- Review Summary will be populated by JavaScript -->
                            <div id="quiz-summary" class="quiz-summary">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden"><?= __('Loading...') ?></span>
                                    </div>
                                    <p class="mt-2"><?= __('Preparing your summary...') ?></p>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-success btn-lg" id="submit-quiz">
                                    <i class="fas fa-rocket"></i> <?= __('Get My Recommendations') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="quiz-navigation mt-4">
                    <div class="row">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary" id="prev-step" style="display: none;">
                                <i class="fas fa-arrow-left"></i> <?= __('Previous') ?>
                            </button>
                        </div>
                        <div class="col-6 text-end">
                            <button type="button" class="btn btn-primary" id="next-step">
                                <?= __('Next') ?> <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<style>
.quiz-comprehensive {
    padding: 2rem 0;
    min-height: 80vh;
}

.quiz-title {
    font-size: 2.25rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.quiz-description {
    font-size: 1.1rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
}

.progress-container {
    max-width: 600px;
    margin: 0 auto 2rem;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
}

.progress {
    height: 10px;
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.5s ease;
}

.step-card {
    border: 2px solid #dee2e6;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    min-height: 500px;
}

.step-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    padding: 1.5rem;
    border-radius: 13px 13px 0 0;
}

.step-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.step-title i {
    margin-right: 0.5rem;
}

.step-description {
    color: #6c757d;
    margin-bottom: 0;
    font-size: 1rem;
}

.step-card .card-body {
    padding: 2rem;
}

.quiz-step {
    opacity: 0;
    transform: translateX(30px);
    transition: all 0.3s ease;
}

.quiz-step.active {
    opacity: 1;
    transform: translateX(0);
}

.connection-options h6 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1rem;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.5rem;
}

.form-check {
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    border-radius: 8px;
    transition: background-color 0.2s;
}

.form-check:hover {
    background-color: #f8f9fa;
}

.form-check-input:checked + .form-check-label {
    font-weight: 500;
    color: #0d6efd;
}

.quiz-navigation {
    max-width: 600px;
    margin: 0 auto;
}

.quiz-navigation button {
    min-width: 120px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 25px;
}

.quiz-summary {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

#submit-quiz {
    min-width: 250px;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 25px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .quiz-title {
        font-size: 1.75rem;
    }
    
    .step-card {
        min-height: auto;
    }
    
    .step-card .card-header {
        padding: 1rem;
    }
    
    .step-card .card-body {
        padding: 1.5rem;
    }
    
    .step-title {
        font-size: 1.25rem;
    }
    
    .quiz-navigation button {
        width: 100%;
        margin-bottom: 0.5rem;
        min-width: auto;
    }
    
    #submit-quiz {
        width: 100%;
        min-width: auto;
    }
    
    .progress-header {
        font-size: 0.8rem;
    }
}

/* Loading states */
.quiz-step.loading {
    pointer-events: none;
    opacity: 0.7;
}

.quiz-step.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 2rem;
    height: 2rem;
    border: 3px solid #dee2e6;
    border-top: 3px solid #0d6efd;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize comprehensive quiz functionality
    if (typeof window.QuizModule !== 'undefined' && window.QuizModule.initializeComprehensive) {
        window.QuizModule.initializeComprehensive();
    } else {
        // Basic fallback navigation
        console.warn('Quiz module not loaded, using basic navigation');
        initializeBasicNavigation();
    }
});

function initializeBasicNavigation() {
    const steps = document.querySelectorAll('.quiz-step');
    const totalSteps = steps.length;
    let currentStep = 1;
    
    const nextBtn = document.getElementById('next-step');
    const prevBtn = document.getElementById('prev-step');
    const progressBar = document.getElementById('main-progress-bar');
    const currentStepSpan = document.getElementById('current-step');
    const totalStepsSpan = document.getElementById('total-steps');
    const completionSpan = document.getElementById('completion-percentage');
    
    // Set total steps
    totalStepsSpan.textContent = totalSteps;
    
    function updateStep() {
        // Hide all steps
        steps.forEach((step, index) => {
            step.style.display = index + 1 === currentStep ? 'block' : 'none';
            step.classList.toggle('active', index + 1 === currentStep);
        });
        
        // Update progress
        const progress = (currentStep / totalSteps) * 100;
        progressBar.style.width = progress + '%';
        currentStepSpan.textContent = currentStep;
        completionSpan.textContent = Math.round(progress);
        
        // Update navigation buttons
        prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
        nextBtn.style.display = currentStep < totalSteps ? 'inline-block' : 'none';
    }
    
    // Navigation event listeners
    nextBtn.addEventListener('click', function() {
        if (validateCurrentStep() && currentStep < totalSteps) {
            currentStep++;
            updateStep();
        }
    });
    
    prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            updateStep();
        }
    });
    
    function validateCurrentStep() {
        // Basic validation - check required fields in current step
        const currentStepEl = document.getElementById(`step-${currentStep}`);
        const requiredFields = currentStepEl.querySelectorAll('[required]');
        
        for (let field of requiredFields) {
            if (!field.value || (field.type === 'radio' && !currentStepEl.querySelector(`input[name="${field.name}"]:checked`))) {
                field.focus();
                return false;
            }
        }
        return true;
    }
    
    // Initialize first step
    updateStep();
}
</script>
