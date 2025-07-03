<?php
/**
 * Image Picker Results - Only the results portion for AJAX updates
 * This template is used when gallery_only=1 parameter is present to avoid modal flicker
 * 
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 * @var string|null $search Current search term
 */

// Just include the original image gallery template
include 'image_gallery.php';