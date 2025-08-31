<?php
/**
 * Image Picker - For selecting images to insert into content
 * Modern layout with separated search form and results for smooth AJAX updates
 * 
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 * @var string|null $search Current search term
 */

use App\Utility\SettingsManager;
?>

<div id="image-gallery" class="willow-image-picker">
    <!-- Static Search Form (Never gets replaced via AJAX) -->
    <div class="willow-search-form p-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           id="imageSearch" 
                           placeholder="<?= __('Search images by name, alt text, keywords...') ?>"
                           value="<?= h($this->request->getQuery('search', '')) ?>"
                           autocomplete="off">
                    <?php if ($this->request->getQuery('search')): ?>
                        <button class="btn btn-outline-secondary" type="button" id="clearImageSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group" aria-label="View toggle">
                    <input type="radio" class="btn-check" name="imageViewType" id="imageGridView" value="grid" checked>
                    <label class="btn btn-outline-primary" for="imageGridView" title="<?= __('Grid View') ?>">
                        <i class="fas fa-th"></i>
                    </label>
                    <input type="radio" class="btn-check" name="imageViewType" id="imageListView" value="list">
                    <label class="btn btn-outline-primary" for="imageListView" title="<?= __('List View') ?>">
                        <i class="fas fa-list"></i>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Search Stats -->
        <div class="row mt-2">
            <div class="col">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    <?= __('Select an image and choose the size to insert into your content') ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Dynamic Results Container (Gets replaced via AJAX) -->
    <div id="image-results" class="willow-results-container">
        <?php include 'image_gallery.php'; ?>
    </div>
</div>

<script>
// Enhanced image picker interactions
document.addEventListener('DOMContentLoaded', function() {
    // Clear search functionality  
    const clearImageSearchBtn = document.getElementById('clearImageSearchBtn');
    if (clearImageSearchBtn) {
        clearImageSearchBtn.addEventListener('click', function() {
            const searchInput = document.getElementById('imageSearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                searchInput.focus();
            }
        });
    }
    
    // Clear search button in search form
    const clearImageSearch = document.getElementById('clearImageSearch');
    if (clearImageSearch) {
        clearImageSearch.addEventListener('click', function() {
            const searchInput = document.getElementById('imageSearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                searchInput.focus();
            }
        });
    }
});
</script>