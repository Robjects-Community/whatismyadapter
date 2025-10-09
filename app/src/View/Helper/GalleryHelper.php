<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;
use Exception;

/**
 * Gallery Helper
 *
 * Handles processing of gallery placeholders in article content using the Cell pattern.
 * This helper follows CakePHP best practices by delegating data fetching to a Cell
 * and focusing purely on placeholder processing.
 */
class GalleryHelper extends Helper
{
    /**
     * Process gallery placeholders in content
     *
     * Replaces [gallery:id:theme:title] placeholders with rendered gallery cells.
     * This method follows CakePHP conventions by delegating rendering to a Cell
     * instead of fetching data directly in the view layer.
     *
     * @param string $content The content containing gallery placeholders
     * @return string The processed content with rendered galleries
     */
    public function processGalleryPlaceholders(string $content): string
    {
        return preg_replace_callback(
            '/\[gallery:([a-f0-9-]+):([^:]*):([^\]]*)\]/',
            [$this, 'renderGalleryPlaceholder'],
            $content,
        );
    }

    /**
     * Render a single gallery placeholder using Cell pattern
     *
     * @param array $matches Regex matches [full_match, gallery_id, theme, title]
     * @return string HTML for the gallery or empty string if error
     */
    private function renderGalleryPlaceholder(array $matches): string
    {
        $galleryId = $matches[1];
        $theme = $matches[2] ?: 'default';
        $title = $matches[3] ?: '';

        try {
            // Use Cell pattern for proper MVC separation
            // Cell handles data fetching, caching, and error handling
            return (string)$this->getView()->cell('Gallery::display', [$galleryId, $theme, $title]);
        } catch (Exception $e) {
            // Log error and return empty string for graceful degradation
            error_log("GalleryHelper: Error rendering gallery cell {$galleryId}: " . $e->getMessage());

            return '';
        }
    }
}
