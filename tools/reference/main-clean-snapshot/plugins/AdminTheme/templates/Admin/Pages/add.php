<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $page
 * @var array $existingSlugs
 */
?>

<header class="py-3 mb-4 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <h1 class="h4 mb-0">
                <i class="bi bi-plus-circle me-2"></i>
                <?= __('Create New Page') ?>
            </h1>
        </div>
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="bi bi-arrow-left me-1"></i>' . __('Back to Pages'),
                ['action' => 'index'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>

<?= $this->Form->create($page, ['class' => 'needs-validation', 'novalidate' => true]) ?>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Content -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-file-text me-2"></i>
                    <?= __('Page Content') ?>
                </h5>
            </div>
            <div class="card-body">
                <!-- URL Import Section -->
                <div class="mb-4 p-3 bg-light rounded">
                    <h6 class="mb-3">
                        <i class="bi bi-globe me-2"></i>
                        <?= __('Import from Website') ?>
                    </h6>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" id="import-url" class="form-control" 
                                       placeholder="<?= __('https://example.com/page-to-import') ?>">
                                <button type="button" id="import-btn" class="btn btn-outline-primary">
                                    <i class="bi bi-download me-1"></i><?= __('Import') ?>
                                </button>
                            </div>
                            <div class="form-text">
                                <?= __('Enter a URL to automatically extract title, content, and meta information.') ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div id="import-status" class="small"></div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <?= $this->Form->control('title', [
                            'label' => ['text' => __('Page Title'), 'class' => 'form-label'],
                            'class' => 'form-control form-control-lg',
                            'placeholder' => __('Enter page title'),
                            'required' => true,
                            'data-slug-source' => true
                        ]) ?>
                        <div class="form-text">
                            <?= __('This will be displayed as the main heading on your page.') ?>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <?= $this->Form->control('slug', [
                            'label' => ['text' => __('Page URL Slug'), 'class' => 'form-label'],
                            'class' => 'form-control',
                            'placeholder' => __('page-url-slug'),
                            'required' => true,
                            'data-slug-target' => true,
                            'pattern' => '^[a-z0-9]+(?:-[a-z0-9]+)*$'
                        ]) ?>
                        <div class="form-text">
                            <?= __('URL-friendly version of the title. Only lowercase letters, numbers, and hyphens allowed.') ?>
                            <br>
                            <strong><?= __('Full URL:') ?></strong> <code id="full-url-preview"><?= $this->Url->build('/', ['fullBase' => true]) ?>en/pages/</code>
                        </div>
                    </div>
                </div>

                <!-- File Upload Section -->
                <div class="mb-4 p-3 bg-light rounded">
                    <h6 class="mb-3">
                        <i class="bi bi-upload me-2"></i>
                        <?= __('Upload Files for Page Content') ?>
                    </h6>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="file-upload-area border-2 border-dashed rounded p-4 text-center mb-3" 
                                 id="file-upload-area" 
                                 style="border-color: #dee2e6; background: #f8f9fa;">
                                <i class="bi bi-cloud-upload display-4 text-muted mb-2"></i>
                                <p class="text-muted mb-2"><?= __('Drag and drop your HTML, CSS, or JavaScript files here') ?></p>
                                <p class="small text-muted mb-3"><?= __('or') ?></p>
                                <input type="file" id="file-input" class="d-none" multiple accept=".html,.css,.js,.htm">
                                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('file-input').click()">
                                    <i class="bi bi-folder-plus me-1"></i><?= __('Browse Files') ?>
                                </button>
                            </div>
                            <div class="form-text mb-3">
                                <?= __('Supported formats: HTML, CSS, JavaScript. Maximum size: 5MB per file.') ?>
                            </div>
                            
                            <!-- Uploaded Files Display -->
                            <div id="uploaded-files" class="d-none">
                                <h6 class="mb-2"><?= __('Uploaded Files') ?></h6>
                                <div id="files-list" class="list-group mb-3"></div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="button" id="merge-files" class="btn btn-success btn-sm">
                                            <i class="bi bi-puzzle me-1"></i><?= __('Merge into Page Content') ?>
                                        </button>
                                        <button type="button" id="clear-files" class="btn btn-outline-danger btn-sm ms-2">
                                            <i class="bi bi-trash me-1"></i><?= __('Clear All') ?>
                                        </button>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button type="button" id="preview-files" class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-eye me-1"></i><?= __('Preview Combined') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <?= $this->Form->control('body', [
                        'type' => 'textarea',
                        'label' => ['text' => __('Page Content'), 'class' => 'form-label'],
                        'class' => 'form-control',
                        'rows' => 15,
                        'placeholder' => __('Write your page content here. You can use HTML tags or merge uploaded files.')
                    ]) ?>
                    <div class="form-text">
                        <?= __('HTML is allowed. Use the preview function to see how your content will look.') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-search me-2"></i>
                    <?= __('SEO Settings') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <?= $this->Form->control('meta_title', [
                        'label' => ['text' => __('Meta Title'), 'class' => 'form-label'],
                        'class' => 'form-control',
                        'placeholder' => __('Page Title - Site Name'),
                        'maxlength' => 60
                    ]) ?>
                    <div class="form-text">
                        <?= __('Recommended: 50-60 characters. Leave empty to use page title.') ?>
                        <span class="float-end"><span id="meta-title-count">0</span>/60</span>
                    </div>
                </div>

                <div class="mb-3">
                    <?= $this->Form->control('meta_description', [
                        'type' => 'textarea',
                        'label' => ['text' => __('Meta Description'), 'class' => 'form-label'],
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => __('Brief description of the page content'),
                        'maxlength' => 160
                    ]) ?>
                    <div class="form-text">
                        <?= __('Recommended: 150-160 characters. This appears in search results.') ?>
                        <span class="float-end"><span id="meta-description-count">0</span>/160</span>
                    </div>
                </div>

                <div class="mb-0">
                    <?= $this->Form->control('meta_keywords', [
                        'label' => ['text' => __('Meta Keywords'), 'class' => 'form-label'],
                        'class' => 'form-control',
                        'placeholder' => __('keyword1, keyword2, keyword3')
                    ]) ?>
                    <div class="form-text">
                        <?= __('Comma-separated keywords related to your page content.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Publish Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear me-2"></i>
                    <?= __('Publish Settings') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <?= $this->Form->checkbox('is_published', [
                            'class' => 'form-check-input',
                            'role' => 'switch',
                            'checked' => true
                        ]) ?>
                        <label class="form-check-label" for="is-published">
                            <?= __('Publish immediately') ?>
                        </label>
                    </div>
                    <div class="form-text">
                        <?= __('Uncheck to save as draft') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <?= $this->Form->checkbox('main_menu', [
                            'class' => 'form-check-input'
                        ]) ?>
                        <label class="form-check-label" for="main-menu">
                            <?= __('Show in header menu') ?>
                        </label>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="form-check">
                        <?= $this->Form->checkbox('footer_menu', [
                            'class' => 'form-check-input'
                        ]) ?>
                        <label class="form-check-label" for="footer-menu">
                            <?= __('Show in footer menu') ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Preview -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-eye me-2"></i>
                    <?= __('Preview') ?>
                </h5>
            </div>
            <div class="card-body">
                <div id="page-preview" class="border rounded p-3" style="min-height: 200px; background-color: #f8f9fa;">
                    <div class="text-muted text-center py-4">
                        <i class="bi bi-eye-slash display-4"></i>
                        <p class="mb-0"><?= __('Preview will appear here as you type') ?></p>
                    </div>
                </div>
                <div class="mt-2 text-end">
                    <small class="text-muted"><?= __('Live preview of your content') ?></small>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?= $this->Form->submit(__('Create Page'), [
                        'class' => 'btn btn-primary btn-lg'
                    ]) ?>
                    
                    <?= $this->Html->link(
                        __('Cancel'),
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-secondary']
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->Form->end() ?>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    // URL Import functionality
    const importBtn = document.getElementById('import-btn');
    const importUrl = document.getElementById('import-url');
    const importStatus = document.getElementById('import-status');
    const titleInput = document.querySelector('input[name="title"]');
    const bodyTextarea = document.querySelector('textarea[name="body"]');
    const metaTitleInput = document.querySelector('input[name="meta_title"]');
    const metaDescriptionTextarea = document.querySelector('textarea[name="meta_description"]');
    const metaKeywordsInput = document.querySelector('input[name="meta_keywords"]');
    
    if (importBtn && importUrl) {
        importBtn.addEventListener('click', async function() {
            const url = importUrl.value.trim();
            
            if (!url) {
                showImportStatus('Please enter a URL', 'danger');
                return;
            }
            
            if (!isValidUrl(url)) {
                showImportStatus('Please enter a valid URL', 'danger');
                return;
            }
            
            try {
                importBtn.disabled = true;
                importBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i><?= __('Importing...') ?>';
                showImportStatus('Extracting content...', 'info');
                
                const response = await fetch('<?= $this->Url->build(['controller' => 'Pages', 'action' => 'extractWebpage', 'prefix' => 'Admin']) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
                    },
                    body: JSON.stringify({ url: url })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    // Populate form fields
                    if (data.data.title && !titleInput.value) {
                        titleInput.value = data.data.title;
                        titleInput.dispatchEvent(new Event('input')); // Trigger slug generation
                    }
                    
                    if (data.data.content && !bodyTextarea.value) {
                        bodyTextarea.value = data.data.content;
                        bodyTextarea.dispatchEvent(new Event('input')); // Trigger preview update
                    }
                    
                    if (data.data.meta_title && !metaTitleInput.value) {
                        metaTitleInput.value = data.data.meta_title;
                        metaTitleInput.dispatchEvent(new Event('input')); // Trigger counter update
                    }
                    
                    if (data.data.meta_description && !metaDescriptionTextarea.value) {
                        metaDescriptionTextarea.value = data.data.meta_description;
                        metaDescriptionTextarea.dispatchEvent(new Event('input')); // Trigger counter update
                    }
                    
                    if (data.data.meta_keywords && !metaKeywordsInput.value) {
                        metaKeywordsInput.value = data.data.meta_keywords;
                    }
                    
                    showImportStatus('Content imported successfully!', 'success');
                    importUrl.value = ''; // Clear the URL input
                } else {
                    showImportStatus(data.message || 'Failed to extract content', 'danger');
                }
                
            } catch (error) {
                console.error('Import error:', error);
                showImportStatus('Error importing content: ' + error.message, 'danger');
            } finally {
                importBtn.disabled = false;
                importBtn.innerHTML = '<i class="bi bi-download me-1"></i><?= __('Import') ?>';
            }
        });
    }
    
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    function showImportStatus(message, type = 'info') {
        const statusEl = document.getElementById('import-status');
        statusEl.innerHTML = `<span class="text-${type}"><i class="bi bi-${getIconForType(type)} me-1"></i>${message}</span>`;
        
        // Auto-hide success/info messages after 5 seconds
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                statusEl.innerHTML = '';
            }, 5000);
        }
    }
    
    function getIconForType(type) {
        switch (type) {
            case 'success': return 'check-circle';
            case 'danger': return 'exclamation-triangle';
            case 'info': return 'info-circle';
            default: return 'info-circle';
        }
    }
    // Auto-generate slug from title
    const titleInput = document.querySelector('[data-slug-source]');
    const slugInput = document.querySelector('[data-slug-target]');
    const fullUrlPreview = document.getElementById('full-url-preview');
    const baseUrl = '<?= $this->Url->build('/', ['fullBase' => true]) ?>en/pages/';
    
    function generateSlug(text) {
        return text
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
    
    function updateUrlPreview() {
        const slug = slugInput.value || 'page-slug';
        fullUrlPreview.textContent = baseUrl + slug;
    }
    
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugInput.dataset.manuallyEdited) {
                const slug = generateSlug(this.value);
                slugInput.value = slug;
                updateUrlPreview();
                validateSlug();
            }
            updatePreview();
        });
        
        slugInput.addEventListener('input', function() {
            this.dataset.manuallyEdited = 'true';
            updateUrlPreview();
            validateSlug();
        });
        
        slugInput.addEventListener('blur', function() {
            this.value = generateSlug(this.value);
            updateUrlPreview();
            validateSlug();
        });
    }
    
    // Slug validation
    function validateSlug() {
        const slug = slugInput.value;
        const existingSlugs = <?= json_encode(array_column($existingSlugs, 'slug')) ?>;
        
        slugInput.setCustomValidity('');
        
        if (slug && existingSlugs.includes(slug)) {
            slugInput.setCustomValidity('<?= __('This slug is already in use. Please choose a different one.') ?>');
        }
    }
    
    // Character counters
    const metaTitle = document.querySelector('input[name="meta_title"]');
    const metaDescription = document.querySelector('textarea[name="meta_description"]');
    
    if (metaTitle) {
        const counter = document.getElementById('meta-title-count');
        metaTitle.addEventListener('input', function() {
            counter.textContent = this.value.length;
            counter.className = this.value.length > 60 ? 'text-danger' : 'text-muted';
        });
    }
    
    if (metaDescription) {
        const counter = document.getElementById('meta-description-count');
        metaDescription.addEventListener('input', function() {
            counter.textContent = this.value.length;
            counter.className = this.value.length > 160 ? 'text-danger' : 'text-muted';
        });
    }
    
    // Live preview
    function updatePreview() {
        const title = document.querySelector('input[name="title"]').value;
        const content = document.querySelector('textarea[name="body"]').value;
        const preview = document.getElementById('page-preview');
        
        if (title || content) {
            let html = '';
            if (title) {
                html += '<h1>' + escapeHtml(title) + '</h1>';
            }
            if (content) {
                html += content; // Content can contain HTML
            }
            preview.innerHTML = html || '<div class="text-muted text-center py-4"><i class="bi bi-eye-slash display-4"></i><p class="mb-0"><?= __('Preview will appear here as you type') ?></p></div>';
        } else {
            preview.innerHTML = '<div class="text-muted text-center py-4"><i class="bi bi-eye-slash display-4"></i><p class="mb-0"><?= __('Preview will appear here as you type') ?></p></div>';
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Add preview update listeners
    document.querySelector('input[name="title"]').addEventListener('input', updatePreview);
    document.querySelector('textarea[name="body"]').addEventListener('input', updatePreview);
    
    // Form validation
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // File Upload Functionality
    const fileUploadArea = document.getElementById('file-upload-area');
    const fileInput = document.getElementById('file-input');
    const uploadedFilesDiv = document.getElementById('uploaded-files');
    const filesList = document.getElementById('files-list');
    const mergeFilesBtn = document.getElementById('merge-files');
    const clearFilesBtn = document.getElementById('clear-files');
    const previewFilesBtn = document.getElementById('preview-files');
    
    let uploadedFiles = [];
    
    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight(e) {
        fileUploadArea.style.borderColor = '#007bff';
        fileUploadArea.style.backgroundColor = '#e7f3ff';
    }
    
    function unhighlight(e) {
        fileUploadArea.style.borderColor = '#dee2e6';
        fileUploadArea.style.backgroundColor = '#f8f9fa';
    }
    
    fileUploadArea.addEventListener('drop', handleDrop, false);
    fileInput.addEventListener('change', handleFileSelect, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
    
    function handleFileSelect(e) {
        const files = e.target.files;
        handleFiles(files);
    }
    
    function handleFiles(files) {
        [...files].forEach(processFile);
    }
    
    function processFile(file) {
        // Validate file type
        const allowedTypes = ['text/html', 'text/css', 'application/javascript', 'text/javascript'];
        const allowedExtensions = ['.html', '.htm', '.css', '.js'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
            showFileError(`Invalid file type: ${file.name}. Only HTML, CSS, and JavaScript files are allowed.`);
            return;
        }
        
        // Validate file size (5MB limit)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            showFileError(`File too large: ${file.name}. Maximum size is 5MB.`);
            return;
        }
        
        // Check if file already exists
        if (uploadedFiles.find(f => f.name === file.name)) {
            showFileError(`File already uploaded: ${file.name}`);
            return;
        }
        
        // Read file content
        const reader = new FileReader();
        reader.onload = function(e) {
            const content = e.target.result;
            const fileObj = {
                name: file.name,
                type: getFileType(file.name),
                content: content,
                size: file.size
            };
            
            uploadedFiles.push(fileObj);
            updateFilesDisplay();
        };
        reader.readAsText(file);
    }
    
    function getFileType(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        switch (extension) {
            case 'html':
            case 'htm':
                return 'html';
            case 'css':
                return 'css';
            case 'js':
                return 'javascript';
            default:
                return 'text';
        }
    }
    
    function showFileError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        fileUploadArea.parentNode.insertBefore(alertDiv, fileUploadArea.nextSibling);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    function updateFilesDisplay() {
        if (uploadedFiles.length === 0) {
            uploadedFilesDiv.classList.add('d-none');
            return;
        }
        
        uploadedFilesDiv.classList.remove('d-none');
        filesList.innerHTML = '';
        
        uploadedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            
            const fileInfo = document.createElement('div');
            fileInfo.innerHTML = `
                <i class="bi bi-${getFileIcon(file.type)} me-2"></i>
                <strong>${file.name}</strong>
                <small class="text-muted ms-2">(${formatFileSize(file.size)} - ${file.type})</small>
            `;
            
            const actions = document.createElement('div');
            actions.innerHTML = `
                <button type="button" class="btn btn-sm btn-outline-info me-1" onclick="previewFile(${index})">
                    <i class="bi bi-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${index})">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            
            fileItem.appendChild(fileInfo);
            fileItem.appendChild(actions);
            filesList.appendChild(fileItem);
        });
    }
    
    function getFileIcon(type) {
        switch (type) {
            case 'html': return 'filetype-html';
            case 'css': return 'filetype-css';
            case 'javascript': return 'filetype-js';
            default: return 'file-earmark-text';
        }
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Global functions for button actions
    window.previewFile = function(index) {
        const file = uploadedFiles[index];
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-${getFileIcon(file.type)} me-2"></i>${file.name}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <pre><code class="language-${file.type}" style="white-space: pre-wrap; word-break: break-all;">${escapeHtml(file.content)}</code></pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Close') ?></button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        modal.addEventListener('hidden.bs.modal', () => modal.remove());
    };
    
    window.removeFile = function(index) {
        uploadedFiles.splice(index, 1);
        updateFilesDisplay();
    };
    
    // Merge files into page content
    mergeFilesBtn.addEventListener('click', function() {
        if (uploadedFiles.length === 0) return;
        
        const bodyTextarea = document.querySelector('textarea[name="body"]');
        let mergedContent = bodyTextarea.value || '';
        
        // Sort files by type (HTML first, then CSS, then JS)
        const sortedFiles = [...uploadedFiles].sort((a, b) => {
            const order = { html: 0, css: 1, javascript: 2 };
            return (order[a.type] || 3) - (order[b.type] || 3);
        });
        
        let htmlContent = '';
        let cssContent = '';
        let jsContent = '';
        
        sortedFiles.forEach(file => {
            switch (file.type) {
                case 'html':
                    htmlContent += file.content + '\n\n';
                    break;
                case 'css':
                    cssContent += file.content + '\n\n';
                    break;
                case 'javascript':
                    jsContent += file.content + '\n\n';
                    break;
            }
        });
        
        // Build combined content
        let combinedContent = mergedContent;
        
        if (cssContent.trim()) {
            combinedContent += '\n\n<style>\n' + cssContent.trim() + '\n</style>';
        }
        
        if (htmlContent.trim()) {
            combinedContent += '\n\n' + htmlContent.trim();
        }
        
        if (jsContent.trim()) {
            combinedContent += '\n\n<script>\n' + jsContent.trim() + '\n</script>';
        }
        
        bodyTextarea.value = combinedContent;
        bodyTextarea.dispatchEvent(new Event('input')); // Trigger preview update
        
        // Show success message
        const successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success alert-dismissible fade show mt-2';
        successDiv.innerHTML = `
            Files merged successfully! Check the Page Content area and preview.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        uploadedFilesDiv.appendChild(successDiv);
        
        setTimeout(() => {
            if (successDiv.parentNode) {
                successDiv.remove();
            }
        }, 3000);
    });
    
    // Clear all files
    clearFilesBtn.addEventListener('click', function() {
        if (confirm('<?= __('Are you sure you want to clear all uploaded files?') ?>')) {
            uploadedFiles = [];
            updateFilesDisplay();
            fileInput.value = '';
        }
    });
    
    // Preview combined files
    previewFilesBtn.addEventListener('click', function() {
        if (uploadedFiles.length === 0) return;
        
        // Create a temporary combined preview
        let htmlContent = '';
        let cssContent = '';
        let jsContent = '';
        
        uploadedFiles.forEach(file => {
            switch (file.type) {
                case 'html':
                    htmlContent += file.content + '\n';
                    break;
                case 'css':
                    cssContent += file.content + '\n';
                    break;
                case 'javascript':
                    jsContent += file.content + '\n';
                    break;
            }
        });
        
        const combinedHtml = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>File Preview</title>
                ${cssContent ? `<style>${cssContent}</style>` : ''}
            </head>
            <body>
                ${htmlContent}
                ${jsContent ? `<script>${jsContent}</script>` : ''}
            </body>
            </html>
        `;
        
        // Open in new window
        const previewWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
        previewWindow.document.write(combinedHtml);
        previewWindow.document.close();
    });
    
    // Initialize
    updateUrlPreview();
    updatePreview();
});
<?php $this->Html->scriptEnd(); ?>