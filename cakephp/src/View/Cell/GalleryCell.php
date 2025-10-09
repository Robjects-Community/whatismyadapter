<?php
declare(strict_types=1);

namespace App\View\Cell;

use Cake\I18n\I18n;
use Cake\Log\LogTrait;
use Cake\View\Cell;
use Exception;

/**
 * Gallery Cell
 *
 * Provides mini-controller functionality for rendering gallery placeholders.
 * Handles data fetching and preparation for gallery display components.
 *
 * Following CakePHP Cell conventions, this class:
 * - Fetches data using fetchTable() (like a controller)
 * - Sets view variables using set() (like a controller)
 * - Renders templates in templates/cell/Gallery/
 */
class GalleryCell extends Cell
{
    use LogTrait;

    /**
     * Display a gallery with proper translation support
     *
     * @param string $galleryId Gallery UUID
     * @param string $theme Gallery display theme (default: 'default')
     * @param string $title Gallery title override (optional)
     * @return void Sets view variables for template rendering
     */
    public function display(string $galleryId, string $theme = 'default', string $title = ''): void
    {
        try {
            // Get the ImageGalleries table and set current locale for translations
            $galleriesTable = $this->fetchTable('ImageGalleries');
            $currentLocale = I18n::getLocale();

            // Debug: Log the current locale being used
            $this->log(sprintf('GalleryCell: Using locale %s for gallery %s', $currentLocale, $galleryId), 'debug');

            // Explicitly set the locale on the table for TranslateBehavior
            $galleriesTable->setLocale($currentLocale);

            // Generate locale-aware cache key from request (same logic as AppController)
            $cacheKey = hash('xxh3', json_encode($this->request->getAttribute('params')));

            // Fetch gallery data using the table's dedicated method
            // For admin theme, don't require published status
            $requirePublished = $theme !== 'admin';
            $gallery = $galleriesTable->getGalleryForPlaceholder($galleryId, $requirePublished, $cacheKey);

            if (!$gallery || empty($gallery->images)) {
                // Set empty flag for template to handle gracefully
                $this->set([
                    'gallery' => null,
                    'theme' => $theme,
                    'title' => $title,
                    'isEmpty' => true,
                ]);

                return;
            }

            // Use translated gallery name, not the title from placeholder
            // This ensures translations work properly
            $displayTitle = $gallery->name;

            // Set data for template rendering
            $this->set([
                'gallery' => $gallery,
                'images' => $gallery->images,
                'theme' => $theme,
                'title' => $displayTitle,
                'description' => $gallery->description,
                'isEmpty' => false,
            ]);

            $this->log(
                sprintf('Successfully rendered gallery cell for ID: %s', $galleryId),
                'debug',
                ['group_name' => 'App\\View\\Cell\\GalleryCell'],
            );
        } catch (Exception $e) {
            // Log error and set empty state for graceful degradation
            $this->log(
                sprintf('GalleryCell error for gallery %s: %s', $galleryId, $e->getMessage()),
                'error',
                ['group_name' => 'App\\View\\Cell\\GalleryCell'],
            );

            $this->set([
                'gallery' => null,
                'theme' => $theme,
                'title' => $title,
                'isEmpty' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
