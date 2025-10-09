<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 * @var \Cake\Collection\CollectionInterface|string[] $tags
 * @var array $formSettings
 * @var array $formFields Grouped form fields from dynamic configuration
 * @var array $fieldGroups Field group configurations
 */
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header Section -->
            <div class="text-center mb-4">
                <h1 class="display-6 text-primary">
                    <i class="fas fa-plus-circle me-2"></i>
                    Submit a New Product
                </h1>
                <p class="lead text-muted">
                    Share your product with our community and help others find the right adapters and connectivity solutions.
                </p>
            </div>

            <!-- AI Assistant Notice -->
            <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-magic me-2"></i>
                    AI-Powered Form Assistant
                </h6>
                <p class="mb-2">Our intelligent form assistant will help you fill out product details as you type. Look for the <span class="badge bg-success"><i class="fas fa-magic"></i> AI</span> indicators next to supported fields.</p>
                <small class="text-muted">
                    <i class="fas fa-lightbulb me-1"></i>
                    Start by entering basic information, and our AI will suggest details for other fields automatically.
                </small>
            </div>

            <!-- Information Notice -->
            <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    Before You Submit
                </h6>
                <p class="mb-2">Your product submission will be reviewed by our team before being published. This helps ensure quality and accuracy for all users.</p>
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    Review typically takes 1-2 business days.
                </small>
            </div>

            <!-- Main Form Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <?= $this->Form->create($product, [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation',
                        'novalidate' => true,
                        'id' => 'dynamic-product-form'
                    ]) ?>
                    
                    <?php foreach ($fieldGroups as $groupKey => $group): ?>
                        <?php if (!empty($formFields[$groupKey])): ?>
                            <!-- <?= h($group['title']) ?> Section -->
                            <fieldset class="mb-4">
                                <legend class="h5 text-primary border-bottom pb-2 mb-4">
                                    <i class="<?= h($group['icon']) ?> me-2"></i>
                                    <?= h($group['title']) ?>
                                </legend>
                                <p class="text-muted small mb-3"><?= h($group['description']) ?></p>
                                
                                <?= $this->element('dynamic_form_fields', [
                                    'fields' => $formFields[$groupKey],
                                    'product' => $product,
                                    'tags' => $tags ?? []
                                ]) ?>
                            </fieldset>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Reliability Notice -->
                    <div class="alert alert-primary border-0 mb-4" role="alert">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-shield-alt fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="alert-heading mb-2">Reliability Scoring</h6>
                                <p class="mb-1">Your submission will automatically receive a reliability score based on the completeness and quality of the information provided.</p>
                                <small class="text-muted">More complete information results in higher reliability scores and better visibility in search results.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Section -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-between align-items-center">
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-lock me-1"></i>
                                Your submission will be reviewed before publication.
                            </small>
                        </div>
                        <div class="d-grid gap-2 d-md-flex">
                            <?= $this->Html->link(
                                '<i class="fas fa-arrow-left me-2"></i>Cancel',
                                ['action' => 'index'],
                                [
                                    'class' => 'btn btn-outline-secondary',
                                    'escape' => false
                                ]
                            ) ?>
                            <?= $this->Form->button(
                                '<i class="fas fa-paper-plane me-2"></i>Submit for Review',
                                [
                                    'type' => 'submit',
                                    'class' => 'btn btn-primary btn-lg px-4',
                                    'escape' => false,
                                    'id' => 'submit-button'
                                ]
                            ) ?>
                        </div>
                    </div>
                    
                    <?= $this->Form->end() ?>
                </div>
            </div>
            
            <!-- Help Section -->
            <div class="mt-4">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center py-3">
                        <small class="text-muted">
                            <i class="fas fa-question-circle me-1"></i>
                            Need help? Contact us at 
                            <a href="mailto:support@adaptercms.com" class="text-decoration-none">support@adaptercms.com</a>
                            or check our 
                            <a href="#" class="text-decoration-none">submission guidelines</a>.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Suggestion Modal -->
<div class="modal fade" id="aiSuggestionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-magic text-success me-2"></i>
                    AI Suggestions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="ai-suggestions-content">
                    <!-- AI suggestions will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Ignore
                </button>
                <button type="button" class="btn btn-success" id="apply-suggestion-btn">
                    <i class="fas fa-check me-1"></i>
                    Apply Suggestion
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus, .form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

.card {
    transition: all 0.2s ease-in-out;
}

.alert {
    border-radius: 0.5rem;
}

legend {
    font-weight: 600;
}

.form-text {
    font-size: 0.875rem;
}

#submit-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.ai-field-indicator {
    position: relative;
}

.ai-field-indicator::after {
    content: "AI";
    position: absolute;
    top: -8px;
    right: -8px;
    background: #198754;
    color: white;
    font-size: 0.6rem;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: bold;
}

.ai-suggestion-btn {
    position: absolute;
    top: 50%;
    right: 8px;
    transform: translateY(-50%);
    z-index: 10;
    padding: 4px 8px;
    font-size: 0.7rem;
    border-radius: 12px;
}

.field-with-ai {
    position: relative;
}

.ai-loading {
    border-right: 3px solid #198754;
    animation: ai-pulse 1.5s infinite;
}

@keyframes ai-pulse {
    0%, 100% { border-right-color: #198754; }
    50% { border-right-color: #20c997; }
}
</style>

<script>
// Get CSRF token for API requests
const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('dynamic-product-form');
    const submitButton = document.getElementById('submit-button');
    const aiModal = new bootstrap.Modal(document.getElementById('aiSuggestionModal'));
    let currentSuggestionField = null;
    let currentSuggestionValue = null;
    let aiRequestTimeout = null;

    // Form submission handler
    if (form && submitButton) {
        form.addEventListener('submit', function(e) {
            // Show loading state
            const originalContent = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitButton.disabled = true;
            
            // Re-enable after 5 seconds as failsafe
            setTimeout(function() {
                submitButton.innerHTML = originalContent;
                submitButton.disabled = false;
            }, 5000);
        });
    }

    // AI suggestion handlers for enabled fields
    const aiEnabledFields = document.querySelectorAll('[data-ai-enabled="true"]');
    
    aiEnabledFields.forEach(field => {
        field.addEventListener('input', function() {
            clearTimeout(aiRequestTimeout);
            const fieldName = this.name;
            
            // Add visual indicator that AI is processing
            this.classList.add('ai-loading');
            
            // Debounce AI requests
            aiRequestTimeout = setTimeout(() => {
                requestAiSuggestions(fieldName, this);
            }, 1500); // Wait 1.5 seconds after user stops typing
        });
        
        field.addEventListener('blur', function() {
            this.classList.remove('ai-loading');
        });
    });

    // Auto-generate alt text from title when image is selected
    const titleField = document.getElementById('title');
    const altTextField = document.getElementById('alt-text');
    const imageField = document.getElementById('image-uploads');
    
    if (titleField && altTextField && imageField) {
        imageField.addEventListener('change', function() {
            if (this.files.length > 0 && titleField.value && !altTextField.value) {
                altTextField.value = titleField.value + ' product image';
            }
        });
    }

    function requestAiSuggestions(fieldName, fieldElement) {
        const formData = new FormData(form);
        const existingData = {};
        
        // Convert FormData to object for AI context
        for (let [key, value] of formData.entries()) {
            if (value && value !== '') {
                existingData[key] = value;
            }
        }
        
        fetch('/api/form-ai-suggestions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                field_name: fieldName,
                existing_data: existingData
            })
        })
        .then(response => response.json())
        .then(data => {
            fieldElement.classList.remove('ai-loading');
            
            if (data.suggestions && data.suggestions.length > 0 && data.confidence_level > 60) {
                showAiSuggestions(fieldName, fieldElement, data);
            }
        })
        .catch(error => {
            console.error('AI suggestion error:', error);
            fieldElement.classList.remove('ai-loading');
        });
    }

    function showAiSuggestions(fieldName, fieldElement, suggestionData) {
        currentSuggestionField = fieldElement;
        currentSuggestionValue = suggestionData.suggestions[0]; // Use first suggestion
        
        const modalContent = document.querySelector('.ai-suggestions-content');
        
        let html = `
            <div class="mb-3">
                <h6>Suggestion for "${fieldElement.labels[0]?.textContent || fieldName}"</h6>
                <p class="text-muted small">${suggestionData.reasoning}</p>
            </div>
            
            <div class="suggestion-preview bg-light p-3 rounded mb-3">
                <strong>Suggested value:</strong><br>
                <span class="text-primary">${currentSuggestionValue}</span>
            </div>
            
            <div class="confidence-indicator mb-3">
                <small class="text-muted">Confidence: ${suggestionData.confidence_level}%</small>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: ${suggestionData.confidence_level}%"></div>
                </div>
            </div>
        `;
        
        // Show alternative suggestions if available
        if (suggestionData.suggestions.length > 1) {
            html += `<div class="alternative-suggestions">`;
            html += `<small class="text-muted">Alternative suggestions:</small><ul class="list-unstyled mt-2">`;
            suggestionData.suggestions.slice(1, 3).forEach(alt => {
                html += `<li><button type="button" class="btn btn-sm btn-outline-secondary me-2 mb-1 alt-suggestion" data-value="${alt}">${alt}</button></li>`;
            });
            html += `</ul></div>`;
        }
        
        modalContent.innerHTML = html;
        
        // Add event listeners for alternative suggestions
        document.querySelectorAll('.alt-suggestion').forEach(btn => {
            btn.addEventListener('click', function() {
                currentSuggestionValue = this.dataset.value;
                document.querySelector('.suggestion-preview span').textContent = currentSuggestionValue;
            });
        });
        
        aiModal.show();
    }

    // Apply suggestion button handler
    document.getElementById('apply-suggestion-btn').addEventListener('click', function() {
        if (currentSuggestionField && currentSuggestionValue) {
            currentSuggestionField.value = currentSuggestionValue;
            currentSuggestionField.dispatchEvent(new Event('input', { bubbles: true }));
            
            // Visual feedback
            currentSuggestionField.classList.add('border-success');
            setTimeout(() => {
                currentSuggestionField.classList.remove('border-success');
            }, 2000);
        }
        
        aiModal.hide();
        currentSuggestionField = null;
        currentSuggestionValue = null;
    });

    // Clear current suggestion when modal is hidden
    document.getElementById('aiSuggestionModal').addEventListener('hidden.bs.modal', function() {
        currentSuggestionField = null;
        currentSuggestionValue = null;
    });
});
</script>
