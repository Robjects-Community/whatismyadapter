<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>

<div class="page-form-integration-widget">
    <div class="card border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
                <i class="fas fa-plus-square"></i>
                Add Product Form to This Page
            </h5>
        </div>
        <div class="card-body">
            <p class="card-text">
                Embed a beautiful product submission form on this page to allow users to contribute products directly.
            </p>
            
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-sparkles text-primary"></i> Beautiful Form Features:</h6>
                    <ul class="list-unstyled small">
                        <li><i class="fas fa-check text-success"></i> AI-powered scoring & recommendations</li>
                        <li><i class="fas fa-check text-success"></i> Drag & drop image uploads</li>
                        <li><i class="fas fa-check text-success"></i> Progressive form validation</li>
                        <li><i class="fas fa-check text-success"></i> Mobile-responsive design</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-cogs text-info"></i> Form Options:</h6>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="enableAiScoring" checked>
                        <label class="form-check-label small" for="enableAiScoring">
                            Enable AI Product Scoring
                        </label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="requireApproval" checked>
                        <label class="form-check-label small" for="requireApproval">
                            Require Admin Approval
                        </label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="showProgressSteps">
                        <label class="form-check-label small" for="showProgressSteps">
                            Show Progress Indicator
                        </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <h6><i class="fas fa-code text-warning"></i> Embed Code:</h6>
                    <div class="bg-light p-3 rounded border">
                        <code class="text-dark" id="embedCode">
                            &lt;?= $this->element('ProductForm/beautiful', [<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;'pageId' => <?= $article->id ?>,<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;'aiScoring' => true,<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;'requireApproval' => true,<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;'showProgress' => false<br>
                            ]); ?&gt;
                        </code>
                        <button class="btn btn-outline-secondary btn-sm float-end mt-2" onclick="copyEmbedCode()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <small class="text-muted">
                        Add this code to your page content where you want the form to appear.
                    </small>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <div class="d-grid">
                        <?= $this->Html->link(
                            '<i class="fas fa-eye"></i> Preview Beautiful Form',
                            ['controller' => 'Products', 'action' => 'addBeautiful', '?' => ['preview' => 1]],
                            ['class' => 'btn btn-info', 'escape' => false, 'target' => '_blank']
                        ) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-grid">
                        <button type="button" class="btn btn-primary" onclick="addFormToPage()">
                            <i class="fas fa-plus"></i> Add Form to Page
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-form-integration-widget {
    margin: 2rem 0;
}

.page-form-integration-widget .card {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border: 2px solid #007bff;
}

.page-form-integration-widget .form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

.page-form-integration-widget code {
    font-size: 0.85rem;
    line-height: 1.4;
    display: block;
    white-space: pre-wrap;
}

.page-form-integration-widget .bg-light {
    position: relative;
}
</style>

<script>
function updateEmbedCode() {
    const aiScoring = document.getElementById('enableAiScoring').checked;
    const requireApproval = document.getElementById('requireApproval').checked;
    const showProgress = document.getElementById('showProgressSteps').checked;
    
    const embedCode = `&lt;?= $this->element('ProductForm/beautiful', [<br>
&nbsp;&nbsp;&nbsp;&nbsp;'pageId' => <?= $article->id ?>,<br>
&nbsp;&nbsp;&nbsp;&nbsp;'aiScoring' => ${aiScoring ? 'true' : 'false'},<br>
&nbsp;&nbsp;&nbsp;&nbsp;'requireApproval' => ${requireApproval ? 'true' : 'false'},<br>
&nbsp;&nbsp;&nbsp;&nbsp;'showProgress' => ${showProgress ? 'true' : 'false'}<br>
]); ?&gt;`;
    
    document.getElementById('embedCode').innerHTML = embedCode;
}

function copyEmbedCode() {
    const embedCode = document.getElementById('embedCode').textContent;
    const cleanCode = embedCode.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&nbsp;/g, ' ');
    
    navigator.clipboard.writeText(cleanCode).then(() => {
        // Show success feedback
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(() => {
        alert('Could not copy code. Please copy manually.');
    });
}

function addFormToPage() {
    const aiScoring = document.getElementById('enableAiScoring').checked;
    const requireApproval = document.getElementById('requireApproval').checked;
    const showProgress = document.getElementById('showProgressSteps').checked;
    
    const formElement = `<?= $this->element('ProductForm/beautiful', [
    'pageId' => ${<?= $article->id ?>},
    'aiScoring' => ${aiScoring},
    'requireApproval' => ${requireApproval},
    'showProgress' => ${showProgress}
]); ?>`;
    
    // Check if we're editing the page content
    const bodyTextarea = document.querySelector('textarea[name="body"], textarea[name="markdown"]');
    const editorInstance = window.CKEDITOR?.instances?.body || window.CKEDITOR?.instances?.markdown;
    
    if (editorInstance) {
        // CKEditor is being used
        const currentContent = editorInstance.getData();
        const newContent = currentContent + '\n\n' + formElement;
        editorInstance.setData(newContent);
    } else if (bodyTextarea) {
        // Regular textarea
        const currentContent = bodyTextarea.value;
        const newContent = currentContent + '\n\n' + formElement;
        bodyTextarea.value = newContent;
    } else {
        // Fallback - show in alert
        alert('Please copy the embed code and paste it into your page content manually:\n\n' + formElement);
        return;
    }
    
    // Show success message
    const successAlert = document.createElement('div');
    successAlert.className = 'alert alert-success alert-dismissible fade show mt-3';
    successAlert.innerHTML = `
        <strong>Success!</strong> Product form has been added to your page content.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.page-form-integration-widget').appendChild(successAlert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (successAlert.parentNode) {
            successAlert.remove();
        }
    }, 5000);
}

// Update embed code when checkboxes change
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.page-form-integration-widget input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateEmbedCode);
    });
    
    // Initial update
    updateEmbedCode();
});
</script>
