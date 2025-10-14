<?php
declare(strict_types=1);

namespace App\Job;

use App\Model\Entity\Image;
use App\Model\Entity\ImageGallery;
use Cake\Datasource\FactoryLocator;
use Cake\Log\LogTrait;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Exception;
use Imagick;
use ImagickDraw;
use ImagickPixel;
use Interop\Queue\Processor;

/**
 * GenerateGalleryPreviewJob Class
 *
 * This class generates preview montage images for image galleries using ImageMagick.
 * It creates a 2x3 grid layout showing up to 6 images from the gallery with
 * professional spacing and styling.
 */
class GenerateGalleryPreviewJob implements JobInterface
{
    use LogTrait;

    /**
     * Maximum number of attempts to process the job
     *
     * @var int|null
     */
    public static int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time
     *
     * @var bool
     */
    public static bool $shouldBeUnique = false;

    /**
     * Preview image dimensions
     */
    private const PREVIEW_WIDTH = 400;
    private const PREVIEW_HEIGHT = 300;
    private const THUMB_WIDTH = 120;
    private const THUMB_HEIGHT = 90;
    private const SPACING = 12;
    private const CORNER_RADIUS = 8;
    private const SHADOW_OFFSET = 3;
    private const SHADOW_BLUR = 6;

    /**
     * Executes the gallery preview generation task.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the gallery ID
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on error
     */
    public function execute(Message $message): ?string
    {
        if (!extension_loaded('imagick')) {
            $this->log(
                'Imagick extension is not loaded',
                'error',
                ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
            );

            return Processor::REJECT;
        }

        $galleryId = $message->getArgument('gallery_id');
        if (!$galleryId) {
            $this->log(
                'No gallery_id provided in message',
                'error',
                ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
            );

            return Processor::REJECT;
        }

        $this->log(
            sprintf('Starting gallery preview generation for gallery ID: %s', $galleryId),
            'info',
            ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
        );

        try {
            // Get gallery and images
            $galleriesTable = FactoryLocator::get('Table')->get('ImageGalleries');
            $gallery = $galleriesTable->get($galleryId, contain: [
                'Images' => function ($q) {
                    return $q->orderBy(['ImageGalleriesImages.position' => 'ASC'])
                            ->limit(6); // Only need first 6 images for preview
                },
            ]);

            if (empty($gallery->images)) {
                $this->log(
                    sprintf('Gallery %s has no images, skipping preview generation', $galleryId),
                    'info',
                    ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
                );

                // Clear any existing preview image
                $this->clearExistingPreview($gallery);

                return Processor::ACK;
            }

            // Generate the preview
            $previewPath = $this->generatePreview($gallery);

            // Update gallery with preview filename
            $previewFilename = basename($previewPath);
            $gallery->preview_image = $previewFilename;
            $galleriesTable->save($gallery);

            $this->log(
                sprintf('Successfully generated preview for gallery %s: %s', $galleryId, $previewFilename),
                'info',
                ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
            );

            return Processor::ACK;
        } catch (Exception $e) {
            $this->log(
                sprintf('Error generating preview for gallery %s: %s', $galleryId, $e->getMessage()),
                'error',
                ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
            );

            return Processor::REJECT;
        }
    }

    /**
     * Generate preview montage image
     *
     * @param \App\Model\Entity\ImageGallery $gallery
     * @return string Path to generated preview image
     * @throws \Exception
     */
    private function generatePreview(ImageGallery $gallery): string
    {
        // Ensure preview directory exists
        $previewDir = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS;
        if (!is_dir($previewDir)) {
            if (!mkdir($previewDir, 0755, true)) {
                throw new Exception("Failed to create preview directory: {$previewDir}");
            }
        }

        // Clear any existing preview
        $this->clearExistingPreview($gallery);

        $previewPath = $previewDir . $gallery->id . '.jpg';
        $images = $gallery->images;
        $imageCount = count($images);

        if ($imageCount === 1) {
            // Single image - just resize it
            $this->createSingleImagePreview($images[0], $previewPath);
        } else {
            // Multiple images - create smart grid montage
            $this->createSmartMontagePreview($images, $previewPath);
        }

        return $previewPath;
    }

    /**
     * Create preview for single image
     *
     * @param \App\Model\Entity\Image $image
     * @param string $outputPath
     * @throws \Exception
     */
    private function createSingleImagePreview(Image $image, string $outputPath): void
    {
        $imagePath = WWW_ROOT . 'files' . DS . 'Images' . DS . 'image' . DS . $image->image;

        if (!file_exists($imagePath)) {
            throw new Exception("Source image not found: {$imagePath}");
        }

        $imagick = new Imagick($imagePath);

        // Resize to fit preview dimensions while maintaining aspect ratio
        $imagick->thumbnailImage(self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT, true);

        // Create canvas with gradient background
        $canvas = new Imagick();
        $canvas->newImage(self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT, new ImagickPixel('white'));
        $canvas->setImageFormat('jpeg');

        // Add gradient background
        $this->addGradientBackground($canvas);

        // Center the image on canvas
        $imageWidth = $imagick->getImageWidth();
        $imageHeight = $imagick->getImageHeight();
        $x = (self::PREVIEW_WIDTH - $imageWidth) / 2;
        $y = (self::PREVIEW_HEIGHT - $imageHeight) / 2;

        // Apply rounded corners and shadow to the image
        $styledImage = $this->applyImageStyling($imagick);

        $canvas->compositeImage($styledImage, Imagick::COMPOSITE_OVER, intval($x), intval($y));

        $styledImage->clear();

        $canvas->writeImage($outputPath);
        $canvas->clear();
        $imagick->clear();
    }

    /**
     * Create smart montage preview for multiple images with dynamic grid layout
     *
     * @param array $images
     * @param string $outputPath
     * @throws \Exception
     */
    private function createSmartMontagePreview(array $images, string $outputPath): void
    {
        $imageCount = count($images);
        $gridLayout = $this->calculateOptimalGrid($imageCount);
        $maxImages = $gridLayout['cols'] * $gridLayout['rows'];

        // Calculate thumbnail dimensions based on grid
        $thumbWidth = intval(
            (self::PREVIEW_WIDTH - (($gridLayout['cols'] + 1) * self::SPACING)) / $gridLayout['cols'],
        );
        $thumbHeight = intval((
            self::PREVIEW_HEIGHT -
            (($gridLayout['rows'] + 1) * self::SPACING)) / $gridLayout['rows']);

        // Create final canvas with gradient background
        $canvas = new Imagick();
        $canvas->newImage(self::PREVIEW_WIDTH, self::PREVIEW_HEIGHT, new ImagickPixel('white'));
        $canvas->setImageFormat('jpeg');
        $this->addGradientBackground($canvas);

        // Process and place each image
        $processedImages = [];
        foreach (array_slice($images, 0, $maxImages) as $index => $image) {
            $imagePath = WWW_ROOT . 'files' . DS . 'Images' . DS . 'image' . DS . $image->image;

            if (!file_exists($imagePath)) {
                $this->log(
                    sprintf('Skipping missing image: %s', $imagePath),
                    'warning',
                    ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
                );
                continue;
            }

            try {
                $img = new Imagick($imagePath);
                $img->thumbnailImage($thumbWidth, $thumbHeight, true);
                $img->setImageFormat('jpeg');

                // Apply styling (rounded corners, shadow)
                $styledImage = $this->applyImageStyling($img);

                // Calculate position in grid
                $row = intval($index / $gridLayout['cols']);
                $col = $index % $gridLayout['cols'];

                $x = self::SPACING + ($col * ($thumbWidth + self::SPACING));
                $y = self::SPACING + ($row * ($thumbHeight + self::SPACING));

                // Composite onto canvas
                $canvas->compositeImage($styledImage, Imagick::COMPOSITE_OVER, $x, $y);

                $processedImages[] = $img;
                $styledImage->clear();
            } catch (Exception $e) {
                $this->log(
                    sprintf('Error processing image %s: %s', $imagePath, $e->getMessage()),
                    'warning',
                    ['group_name' => 'App\\Job\\GenerateGalleryPreviewJob'],
                );
            }
        }

        if (empty($processedImages)) {
            throw new Exception('No valid images found for montage');
        }

        $canvas->writeImage($outputPath);

        // Cleanup
        $canvas->clear();
        foreach ($processedImages as $img) {
            $img->clear();
        }
    }

    /**
     * Calculate optimal grid layout based on image count
     *
     * @param int $imageCount
     * @return array{cols: int, rows: int}
     */
    private function calculateOptimalGrid(int $imageCount): array
    {
        // Smart grid layouts based on image count
        return match (true) {
            $imageCount <= 1 => ['cols' => 1, 'rows' => 1],
            $imageCount <= 2 => ['cols' => 2, 'rows' => 1],
            $imageCount <= 3 => ['cols' => 3, 'rows' => 1],
            $imageCount <= 4 => ['cols' => 2, 'rows' => 2],
            $imageCount <= 6 => ['cols' => 3, 'rows' => 2],
            $imageCount <= 9 => ['cols' => 3, 'rows' => 3],
            default => ['cols' => 4, 'rows' => 3] // For 10+ images
        };
    }

    /**
     * Add gradient background to canvas
     *
     * @param \Imagick $canvas
     */
    private function addGradientBackground(Imagick $canvas): void
    {
        // Create subtle gradient from light gray to white
        $gradient = new Imagick();
        $gradient->newPseudoImage(
            self::PREVIEW_WIDTH,
            self::PREVIEW_HEIGHT,
            'gradient:#f8f9fa-#ffffff',
        );

        // Composite gradient onto canvas
        $canvas->compositeImage($gradient, Imagick::COMPOSITE_OVER, 0, 0);
        $gradient->clear();
    }

    /**
     * Apply styling to image (rounded corners and drop shadow)
     *
     * @param \Imagick $image
     * @return \Imagick
     */
    private function applyImageStyling(Imagick $image): Imagick
    {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        // Create mask for rounded corners
        $mask = new Imagick();
        $mask->newImage($width, $height, new ImagickPixel('transparent'));

        $draw = new ImagickDraw();
        $draw->setFillColor(new ImagickPixel('white'));
        $draw->roundRectangle(0, 0, $width - 1, $height - 1, self::CORNER_RADIUS, self::CORNER_RADIUS);
        $mask->drawImage($draw);

        // Apply mask to create rounded corners
        $image->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);

        // Create shadow
        $shadow = clone $image;
        $shadow->setImageBackgroundColor(new ImagickPixel('rgba(0,0,0,0.3)'));
        $shadow->shadowImage(60, self::SHADOW_BLUR, self::SHADOW_OFFSET, self::SHADOW_OFFSET);

        // Create final image with shadow
        $final = new Imagick();
        $final->newImage(
            $width + self::SHADOW_OFFSET + self::SHADOW_BLUR,
            $height + self::SHADOW_OFFSET + self::SHADOW_BLUR,
            new ImagickPixel('transparent'),
        );

        // Composite shadow first, then image
        $final->compositeImage($shadow, Imagick::COMPOSITE_OVER, 0, 0);
        $final->compositeImage($image, Imagick::COMPOSITE_OVER, 0, 0);

        // Cleanup
        $mask->clear();
        $shadow->clear();
        $draw->clear();

        return $final;
    }

    /**
     * Clear existing preview image if it exists
     *
     * @param \App\Model\Entity\ImageGallery $gallery
     */
    private function clearExistingPreview(ImageGallery $gallery): void
    {
        if ($gallery->preview_image) {
            $existingPath = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS . $gallery->preview_image;
            if (file_exists($existingPath)) {
                unlink($existingPath);
            }
        }

        // Also clean up any file with the gallery ID (in case preview_image field is out of sync)
        $previewDir = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS;
        $galleryPreviewPath = $previewDir . $gallery->id . '.jpg';
        if (file_exists($galleryPreviewPath)) {
            unlink($galleryPreviewPath);
        }
    }
}
