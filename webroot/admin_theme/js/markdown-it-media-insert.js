/**
 * Media insertion handlers for markdown editor - BRILLIANT unified WillowModal approach
 */
document.addEventListener('DOMContentLoaded', function() {
    // Image insertion handler
    document.getElementById('insertImageBtn')?.addEventListener('click', function() {
        WillowModal.showImageSelector(null, {
            title: 'Select Image from Library'
        });
    });

    // Video insertion handler
    document.getElementById('insertVideoBtn')?.addEventListener('click', function() {
        WillowModal.showVideoSelector(null, {
            title: 'Insert YouTube Video'
        });
    });

    // Gallery insertion handler
    document.getElementById('insertGalleryBtn')?.addEventListener('click', function() {
        WillowModal.showGallerySelector(null, {
            title: 'Insert Image Gallery'
        });
    });
});