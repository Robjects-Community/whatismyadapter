<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 * @var string $viewType
 * @var string|null $galleryId
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Image'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
            <?php if ($galleryId): ?>
                <?= $this->Html->link(__('Back to Gallery'), ['controller' => 'ImageGalleries', 'action' => 'manageImages', $galleryId], ['class' => 'side-nav-item']) ?>
            <?php endif; ?>
        </div>
    </aside>
    <div class="column column-80">
        <header class="py-3 mb-3 border-bottom">
            <div class="container-fluid d-flex align-items-center images">
                <div class="d-flex align-items-center me-auto">
                    <div class="btn-group me-3">
                        <?= $this->Html->link('
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"></path>
                            </svg>
                            <span class="visually-hidden">' . __('List View') . '</span>
                        ', ['action' => 'picker', '?' => ['view' => 'list', 'gallery_id' => $galleryId]], [
                            'class' => 'btn btn-outline-secondary',
                            'escape' => false,
                        ]) ?>
                        <?= $this->Html->link('
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-grid-3x2" viewBox="0 0 16 16">
                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h13A1.5 1.5 0 0 1 16 3.5v8a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5zM1.5 3a.5.5 0 0 0-.5.5V7h4V3zM5 8H1v3.5a.5.5 0 0 0 .5.5H5zm1 0v4h4V8zm4-1V3H6v4zm1 1v4h3.5a.5.5 0 0 0 .5-.5V8zm0-1h4V3.5a.5.5 0 0 0-.5-.5H11z"></path>
                            </svg>
                            <span class="visually-hidden">' . __('Grid View') . '</span>
                        ', ['action' => 'picker', '?' => ['view' => 'grid', 'gallery_id' => $galleryId]], [
                            'class' => 'btn btn-secondary',
                            'escape' => false,
                        ]) ?>
                    </div>
                    <div class="d-flex-grow-1 me-3" role="search">
                        <input id="imageSearch" type="search" class="form-control" placeholder="<?= __('Search...') ?>" aria-label="Search" value="<?= $this->request->getQuery('search') ?>">
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <?= $this->Form->button(__('Add Selected Images'), [
                        'class' => 'btn btn-primary',
                        'id' => 'submit-button',
                        'disabled' => true
                    ]) ?>
                    <span id="selected-count" class="text-muted ms-2"><?= __('No images selected') ?></span>
                </div>
            </div>
        </header>
        
        <?= $this->Form->create(null, [
            'url' => ['controller' => 'ImageGalleries', 'action' => 'addImages', $galleryId],
            'id' => 'add-images-form'
        ]) ?>
            
            <div id="ajax-target">
                <div class="album py-5 bg-body-tertiary">
                    <div class="container">
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                            <?php foreach ($images as $image): ?>
                                    <div class="col">
                                        <div class="card shadow-sm position-relative">
                                            <div class="form-check position-absolute top-0 start-0 m-2" style="z-index: 10;">
                                                <?= $this->Form->checkbox('image_ids[]', [
                                    'value' => $image->id,
                                    'class' => 'image-checkbox form-check-input',
                                    'id' => 'img-' . $image->id,
                                    'hiddenField' => false
                                ]) ?>
                                                <label class="form-check-label visually-hidden" for="img-<?= h($image->id) ?>">
                                                    <?= __('Select {0}', h($image->name)) ?>
                                                </label>
                                            </div>
                                            
                                            <?= $this->Html->image(
                                                SettingsManager::read('ImageSizes.large') . '/' . $image->image, [
                                                    'pathPrefix' => 'files/Images/image/',
                                                    'alt' => $image->alt_text,
                                                    'class' => 'card-img-top'
                                            ]) ?>
                                            
                                            <div class="card-body">
                                                <p class="card-text"><?= h($image->name) ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted"><?= h($image->created->format('M j, Y')) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?= $this->element('pagination', ['recordCount' => count($images)]) ?>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>

<style>
.card.selected {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.3) !important;
}
</style>

<?php $this->Html->scriptStart(['block' => true]); ?>
console.log('Grid view script loading...');
document.addEventListener('DOMContentLoaded', function() {
    console.log('Grid view: DOM Content Loaded');
    
    const searchInput = document.getElementById('imageSearch');
    const resultsContainer = document.querySelector('#ajax-target');
    const submitButton = document.getElementById('submit-button');
    const selectedCount = document.getElementById('selected-count');
    
    console.log('Grid view elements found:', {
        searchInput: !!searchInput,
        resultsContainer: !!resultsContainer,
        submitButton: !!submitButton,
        selectedCount: !!selectedCount
    });
    
    let debounceTimer;

    // Update UI based on checkbox selections
    function updateUI() {
        const checked = document.querySelectorAll('.image-checkbox:checked');
        const count = checked.length;
        
        submitButton.disabled = count === 0;
        
        if (count === 0) {
            selectedCount.textContent = '<?= __('No images selected') ?>';
        } else if (count === 1) {
            selectedCount.textContent = '<?= __('1 image selected') ?>';
        } else {
            selectedCount.textContent = count + ' <?= __('images selected') ?>';
        }
        
        // Update visual selection
        document.querySelectorAll('.card').forEach(card => {
            const checkbox = card.querySelector('.image-checkbox');
            if (checkbox && checkbox.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }
    
    // Add listeners to checkboxes
    function updateCheckboxListeners() {
        document.querySelectorAll('.image-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateUI);
        });
        
        // Also add click listeners to cards for better UX
        document.querySelectorAll('.card').forEach(card => {
            const checkbox = card.querySelector('.image-checkbox');
            card.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox' && checkbox) {
                    checkbox.checked = !checkbox.checked;
                    updateUI();
                }
            });
        });
    }
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();
            
            // Only search if 3+ characters or empty (to show all results)
            if (searchTerm.length > 0 && searchTerm.length < 3) {
                return;
            }
            
            let url = '<?= $this->Url->build(['action' => 'picker']) ?>';
            const params = new URLSearchParams();
            params.append('gallery_id', '<?= h($galleryId) ?>');
            params.append('view', 'grid');
            
            if (searchTerm.length > 0) {
                params.append('search', searchTerm);
            }
            
            url += '?' + params.toString();
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                resultsContainer.innerHTML = html;
                updateCheckboxListeners();
                updateUI();
            })
            .catch(error => console.error('Search error:', error));
        }, 300);
    });
    
    // Submit button click handler
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            console.log('Grid view: Submit button clicked!');
            
            const form = document.getElementById('add-images-form');
            const checked = document.querySelectorAll('.image-checkbox:checked');
            
            if (checked.length === 0) {
                alert('Please select at least one image.');
                e.preventDefault();
                return false;
            }
            
            if (form && !this.disabled) {
                console.log('Grid view: Submitting form...');
                form.submit();
            } else {
                console.log('Grid view: Form not found or button disabled');
                e.preventDefault();
            }
        });
    }
    
    // Initialize
    updateCheckboxListeners();
    updateUI();
});
<?php $this->Html->scriptEnd(); ?>