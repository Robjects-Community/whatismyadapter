<?php
/**
 * Gallery Cell Display Template
 * 
 * This template renders a gallery from cell data.
 * It receives the following variables from the GalleryCell:
 * 
 * @var \App\Model\Entity\ImageGallery|null $gallery The gallery entity
 * @var \App\Model\Entity\Image[] $images Array of gallery images
 * @var string $theme Gallery display theme
 * @var string $title Gallery title (with override support)
 * @var bool $isEmpty Whether the gallery is empty or unavailable
 * @var string|null $error Error message if rendering failed
 */

// Handle empty/error states gracefully
if ($isEmpty || !$gallery) {
    // Return empty string for silent degradation on frontend
    return;
}

// Load PhotoSwipe library from CDN (required dependency)
echo $this->Html->css('https://cdn.jsdelivr.net/npm/photoswipe@5.4.2/dist/photoswipe.css');
echo $this->Html->script('https://cdn.jsdelivr.net/npm/photoswipe@5.4.2/dist/photoswipe.umd.min.js');

// Load our gallery script
echo $this->Html->script('photoswipe-gallery');

// Load theme-specific CSS
if ($theme === 'admin') {
    echo $this->Html->css('AdminTheme.photo-gallery');
} else {
    echo $this->Html->css('DefaultTheme.photo-gallery');
}

// Render the gallery using the existing shared element
echo $this->element('shared_photo_gallery', [
    'images' => $images,
    'title' => $title,
    'description' => $description,
    'theme' => $theme,
    'showActions' => false, // Actions handled separately when needed
    'galleryId' => null, // Links handled separately when needed
]);
?>