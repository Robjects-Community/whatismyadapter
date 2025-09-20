/**
 * Gallery Image Management
 * Handles drag-and-drop image ordering and removal for the manage images interface
 */
(function() {
    'use strict';

    let sortableInstance = null;
    let config = {};

    /**
     * Initialize the gallery image management
     */
    function init(options) {
        config = Object.assign({
            galleryId: null,
            csrfToken: null,
            updateOrderUrl: '',
            removeImageUrl: '',
            confirmMessage: 'Are you sure you want to remove this image from the gallery?'
        }, options);

        initializeSortable();
        bindRemoveEvents();
    }

    /**
     * Initialize Sortable.js for drag and drop
     */
    function initializeSortable() {
        const sortableElement = document.getElementById('sortable-images');
        if (!sortableElement || typeof Sortable === 'undefined') {
            return;
        }

        sortableInstance = Sortable.create(sortableElement, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function(evt) {
                updateImageOrder();
            }
        });
    }

    /**
     * Bind remove image button events
     */
    function bindRemoveEvents() {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-image')) {
                e.preventDefault();
                const imageId = e.target.getAttribute('data-image-id');
                if (imageId) {
                    removeImage(imageId, e.target);
                }
            }
        });
    }

    /**
     * Update image order after drag and drop
     */
    function updateImageOrder() {
        if (!config.updateOrderUrl) {
            console.warn('Update order URL not configured');
            return;
        }

        const sortableElement = document.getElementById('sortable-images');
        if (!sortableElement) return;

        const imageIds = Array.from(sortableElement.children).map(item => 
            item.getAttribute('data-image-id')
        );

        const formData = new FormData();
        formData.append('gallery_id', config.galleryId);
        imageIds.forEach(imageId => {
            formData.append('image_ids[]', imageId);
        });

        fetch(config.updateOrderUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': config.csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Image order updated successfully', 'success');
            } else {
                showNotification('Failed to update order: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error updating order:', error);
            showNotification('Error updating image order', 'error');
        });
    }

    /**
     * Remove an image from the gallery
     */
    function removeImage(imageId, buttonElement) {
        if (!confirm(config.confirmMessage)) {
            return;
        }

        if (!config.removeImageUrl) {
            console.warn('Remove image URL not configured');
            return;
        }

        const url = config.removeImageUrl.replace(':imageId', imageId);

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': config.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the image item from DOM
                const imageItem = buttonElement.closest('.gallery-image-item');
                if (imageItem) {
                    imageItem.remove();
                }
                showNotification('Image removed from gallery', 'success');
            } else {
                showNotification('Failed to remove image: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error removing image:', error);
            showNotification('Failed to remove image', 'error');
        });
    }

    /**
     * Show a notification to the user
     */
    function showNotification(message, type = 'info') {
        // Use existing notification system if available, otherwise create simple alert
        if (window.showToast && typeof window.showToast === 'function') {
            window.showToast(message, type);
            return;
        }

        // Fallback: create a simple notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Auto-initialize if window.GalleryManageConfig is available
    document.addEventListener('DOMContentLoaded', function() {
        if (window.GalleryManageConfig) {
            init(window.GalleryManageConfig);
        }
    });

    // Export for manual initialization
    window.GalleryManage = {
        init: init
    };
})();