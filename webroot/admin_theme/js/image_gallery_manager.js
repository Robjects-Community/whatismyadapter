/**
 * Image Gallery Manager
 * Handles drag-and-drop image ordering, adding/removing images, and caption editing
 */
window.ImageGalleryManager = (function() {
    'use strict';

    let config = {};
    let sortable = null;
    let selectedImages = new Set();
    let currentPage = 1;
    let isLoading = false;

    /**
     * Initialize the gallery manager
     */
    function init(options) {
        config = Object.assign({
            galleryId: null,
            csrfToken: null,
            urls: {
                addImages: '',
                removeImage: '',
                updateOrder: '',
                loadImages: ''
            }
        }, options);

        if (!config.galleryId) {
            console.error('Gallery ID is required');
            return;
        }

        initializeSortable();
        bindEvents();
        loadAvailableImages();
    }

    /**
     * Initialize sortable drag-and-drop functionality
     */
    function initializeSortable() {
        const gallery = document.getElementById('gallery-images-grid');
        if (!gallery) return;

        sortable = Sortable.create(gallery, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            chosenClass: 'sortable-chosen',
            onEnd: function(evt) {
                updateImageOrder();
            }
        });
    }

    /**
     * Bind event handlers
     */
    function bindEvents() {
        // Remove image buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-image')) {
                e.preventDefault();
                const btn = e.target.closest('.btn-remove-image');
                const imageId = btn.dataset.imageId;
                removeImageFromGallery(imageId);
            }
        });

        // Add selected images button
        const addSelectedBtn = document.getElementById('add-selected-images');
        if (addSelectedBtn) {
            addSelectedBtn.addEventListener('click', addSelectedImages);
        }

        // Image search
        const searchInput = document.getElementById('image-search');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const searchTerm = this.value.trim();
                    // Only search if 3+ characters or empty (to show all results)
                    if (searchTerm.length > 0 && searchTerm.length < 3) {
                        return;
                    }
                    currentPage = 1;
                    loadAvailableImages(searchTerm);
                }, 300);
            });
        }

        // Load more images
        const loadMoreBtn = document.getElementById('load-more-images');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                currentPage++;
                loadAvailableImages(document.getElementById('image-search').value, true);
            });
        }

        // Image selection in modal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.image-item')) {
                const item = e.target.closest('.image-item');
                toggleImageSelection(item);
            }
        });

        // Edit caption modal
        const editCaptionModal = document.getElementById('editCaptionModal');
        if (editCaptionModal) {
            editCaptionModal.addEventListener('show.bs.modal', function(e) {
                const button = e.relatedTarget;
                const imageId = button.dataset.imageId;
                const caption = button.dataset.caption || '';
                
                const textarea = editCaptionModal.querySelector('#image-caption');
                textarea.value = caption;
                
                const saveBtn = editCaptionModal.querySelector('#save-caption');
                saveBtn.onclick = () => saveCaptionForImage(imageId, textarea.value);
            });
        }

        // Reset modal state when closed
        const addImagesModal = document.getElementById('addImagesModal');
        if (addImagesModal) {
            addImagesModal.addEventListener('hidden.bs.modal', function() {
                selectedImages.clear();
                updateSelectedCount();
                currentPage = 1;
            });
        }
    }

    /**
     * Load available images for selection
     */
    function loadAvailableImages(search = '', append = false) {
        if (isLoading) return;
        isLoading = true;

        const params = new URLSearchParams({
            page: currentPage,
            limit: 12
        });

        if (search) {
            params.append('search', search);
        }

        fetch(`${config.urls.loadImages}?${params}`)
            .then(response => response.text())
            .then(html => {
                const container = document.getElementById('available-images');
                if (!container) return;

                if (append) {
                    container.insertAdjacentHTML('beforeend', html);
                } else {
                    container.innerHTML = html;
                }

                // Update load more button visibility
                const loadMoreBtn = document.getElementById('load-more-images');
                const hasMore = container.children.length % 12 === 0 && container.children.length > 0;
                loadMoreBtn.style.display = hasMore ? 'block' : 'none';

                isLoading = false;
            })
            .catch(error => {
                console.error('Error loading images:', error);
                showNotification('Error loading images', 'error');
                isLoading = false;
            });
    }

    /**
     * Toggle image selection
     */
    function toggleImageSelection(item) {
        const imageId = item.dataset.imageId;
        
        if (selectedImages.has(imageId)) {
            selectedImages.delete(imageId);
            item.classList.remove('selected');
        } else {
            selectedImages.add(imageId);
            item.classList.add('selected');
        }
        
        updateSelectedCount();
    }

    /**
     * Update selected images count
     */
    function updateSelectedCount() {
        const countElement = document.getElementById('selected-count');
        const addBtn = document.getElementById('add-selected-images');
        
        if (countElement) {
            countElement.textContent = selectedImages.size;
        }
        
        if (addBtn) {
            addBtn.disabled = selectedImages.size === 0;
        }
    }

    /**
     * Add selected images to gallery
     */
    function addSelectedImages() {
        if (selectedImages.size === 0) return;

        const imageIds = Array.from(selectedImages);
        const formData = new FormData();
        
        imageIds.forEach(id => {
            formData.append('image_ids[]', id);
        });
        formData.append('_csrfToken', config.csrfToken);

        fetch(config.urls.addImages, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Close modal and reload page to show new images
                const modal = bootstrap.Modal.getInstance(document.getElementById('addImagesModal'));
                modal.hide();
                setTimeout(() => window.location.reload(), 500);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error adding images:', error);
            showNotification('Error adding images to gallery', 'error');
        });
    }

    /**
     * Remove image from gallery
     */
    function removeImageFromGallery(imageId) {
        if (!confirm('Are you sure you want to remove this image from the gallery?')) {
            return;
        }

        fetch(`${config.urls.removeImage}/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': config.csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the image from DOM
                const imageCard = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageCard) {
                    imageCard.remove();
                    updatePositionBadges();
                }
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error removing image:', error);
            showNotification('Error removing image from gallery', 'error');
        });
    }

    /**
     * Update image order after drag and drop
     */
    function updateImageOrder() {
        const cards = document.querySelectorAll('#gallery-images-grid [data-image-id]');
        const imageIds = Array.from(cards).map(card => card.dataset.imageId);

        const formData = new FormData();
        formData.append('gallery_id', config.galleryId);
        imageIds.forEach(id => {
            formData.append('image_ids[]', id);
        });
        formData.append('_csrfToken', config.csrfToken);

        fetch(config.urls.updateOrder, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePositionBadges();
                showNotification('Image order updated', 'success');
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error updating order:', error);
            showNotification('Error updating image order', 'error');
        });
    }

    /**
     * Update position badges after reordering
     */
    function updatePositionBadges() {
        const cards = document.querySelectorAll('#gallery-images-grid [data-image-id]');
        cards.forEach((card, index) => {
            const badge = card.querySelector('.badge');
            if (badge) {
                badge.textContent = index + 1;
            }
            card.dataset.position = index;
        });
    }

    /**
     * Save caption for image
     */
    function saveCaptionForImage(imageId, caption) {
        // This would need a new endpoint in the controller
        // For now, just close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('editCaptionModal'));
        modal.hide();
        showNotification('Caption saving functionality to be implemented', 'info');
    }

    /**
     * Show notification to user
     */
    function showNotification(message, type = 'info') {
        // Create a simple toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }

    // Public API
    return {
        init: init
    };
})();