<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Image;
use App\Model\Table\ImageGalleriesImagesTable;
use App\Model\Table\ImagesTable;
use App\Utility\ArchiveExtractor;
use Cake\Log\LogTrait;
use Exception;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;

/**
 * ImageProcessingService Class
 *
 * This service handles the upload and processing of image files, including:
 * - Single image uploads
 * - Archive extraction and batch processing
 * - Gallery association for uploaded images
 * - Integration with QueueableImageBehavior for automatic processing
 *
 * The service consolidates duplicate image upload logic from ImageGalleriesController
 * and ImagesController, providing a reusable, testable solution.
 */
class ImageProcessingService
{
    use LogTrait;

    /**
     * @var \App\Model\Table\ImagesTable
     */
    private ImagesTable $imagesTable;

    /**
     * @var \App\Model\Table\ImageGalleriesImagesTable
     */
    private ImageGalleriesImagesTable $galleriesImagesTable;

    /**
     * @var \App\Utility\ArchiveExtractor
     */
    private ArchiveExtractor $archiveExtractor;

    /**
     * ImageProcessingService constructor.
     *
     * @param \App\Model\Table\ImagesTable $imagesTable The images table instance
     * @param \App\Model\Table\ImageGalleriesImagesTable $galleriesImagesTable The galleries images junction table
     * @param \App\Utility\ArchiveExtractor $archiveExtractor The archive extractor utility
     */
    public function __construct(
        ImagesTable $imagesTable,
        ImageGalleriesImagesTable $galleriesImagesTable,
        ArchiveExtractor $archiveExtractor,
    ) {
        $this->imagesTable = $imagesTable;
        $this->galleriesImagesTable = $galleriesImagesTable;
        $this->archiveExtractor = $archiveExtractor;
    }

    /**
     * Process an array of uploaded files, handling both individual images and archives
     *
     * @param array $uploadedFiles Array of uploaded files
     * @param string|null $galleryId Optional gallery ID to associate images with
     * @return array Result array with success/error counts and details
     */
    public function processUploadedFiles(array $uploadedFiles, ?string $galleryId = null): array
    {
        $results = [
            'success' => true,
            'total_processed' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'created_images' => [],
            'errors' => [],
            'message' => '',
        ];

        foreach ($uploadedFiles as $uploadedFile) {
            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                $results['errors'][] = [
                    'file' => $uploadedFile->getClientFilename(),
                    'error' => 'Upload error: ' . $this->getUploadErrorMessage($uploadedFile->getError()),
                ];
                $results['error_count']++;
                continue;
            }

            try {
                if ($this->isArchiveFile($uploadedFile)) {
                    $archiveResult = $this->processArchive($uploadedFile, $galleryId);
                    $this->mergeResults($results, $archiveResult);
                } else {
                    $imageResult = $this->processSingleImage($uploadedFile, $galleryId);
                    $this->mergeResults($results, $imageResult);
                }
            } catch (Exception $e) {
                $this->log(
                    sprintf(
                        'Error processing uploaded file %s: %s',
                        $uploadedFile->getClientFilename(),
                        $e->getMessage(),
                    ),
                    'error',
                    ['group_name' => 'ImageUploadService'],
                );

                $results['errors'][] = [
                    'file' => $uploadedFile->getClientFilename(),
                    'error' => $e->getMessage(),
                ];
                $results['error_count']++;
            }
        }

        $results['total_processed'] = $results['success_count'] + $results['error_count'];
        $results['success'] = $results['success_count'] > 0;
        $results['message'] = $this->generateResultMessage($results);

        return $results;
    }

    /**
     * Create and save an image entity from an uploaded file
     *
     * @param \Psr\Http\Message\UploadedFileInterface $uploadedFile The uploaded file
     * @param string|null $galleryId Optional gallery ID to associate the image with
     * @return \App\Model\Entity\Image|null The created image entity or null on failure
     */
    public function createImageFromFile(UploadedFileInterface $uploadedFile, ?string $galleryId = null): ?Image
    {
        $filename = $uploadedFile->getClientFilename();

        // Create new image entity
        $imageEntity = $this->imagesTable->newEntity([
            'name' => pathinfo($filename, PATHINFO_FILENAME),
            'image' => $uploadedFile,
        ], ['validate' => 'create']);

        $this->log(sprintf('Attempting to save image: %s', $filename), 'info', ['group_name' => 'ImageUploadService']);

        if ($this->imagesTable->save($imageEntity)) {
            $this->log(
                sprintf('Successfully created image "%s" (ID: %s)', $imageEntity->name, $imageEntity->id),
                'info',
                ['group_name' => 'ImageUploadService'],
            );

            // Add to gallery if gallery ID provided
            if ($galleryId && !$this->addImageToGallery($imageEntity, $galleryId)) {
                $this->log(
                    sprintf('Failed to add image %s to gallery %s', $imageEntity->id, $galleryId),
                    'warning',
                    ['group_name' => 'ImageUploadService'],
                );
            }

            return $imageEntity;
        }

        $this->log(
            sprintf('Failed to save image "%s". Errors: %s', $filename, json_encode($imageEntity->getErrors())),
            'error',
            ['group_name' => 'ImageUploadService'],
        );
        $this->log(
            sprintf('Image entity validation errors: %s', json_encode($imageEntity->getErrors())),
            'error',
            ['group_name' => 'ImageUploadService'],
        );

        return null;
    }

    /**
     * Check if an uploaded file is an archive
     *
     * @param \Psr\Http\Message\UploadedFileInterface $uploadedFile
     * @return bool
     */
    private function isArchiveFile(UploadedFileInterface $uploadedFile): bool
    {
        $filename = $uploadedFile->getClientFilename();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, $this->archiveExtractor->getSupportedArchiveTypes()) ||
               ($extension === 'gz' && strpos($filename, '.tar.') !== false);
    }

    /**
     * Process an archive file and extract images
     *
     * @param \Psr\Http\Message\UploadedFileInterface $uploadedFile
     * @param string|null $galleryId
     * @return array
     */
    private function processArchive(UploadedFileInterface $uploadedFile, ?string $galleryId): array
    {
        $results = [
            'success_count' => 0,
            'error_count' => 0,
            'created_images' => [],
            'errors' => [],
        ];

        // Create archives directory if it doesn't exist
        $archivesDir = TMP . 'archives' . DS;
        if (!is_dir($archivesDir)) {
            mkdir($archivesDir, 0755, true);
        }

        // Save uploaded file to temporary location
        $tempPath = $archivesDir . uniqid() . '_' . $uploadedFile->getClientFilename();
        $uploadedFile->moveTo($tempPath);

        $tempDir = null;
        try {
            // Extract archive
            $extractedFiles = $this->archiveExtractor->extract($tempPath);
            $tempDir = dirname($extractedFiles[0] ?? ''); // Get the temp directory from the first file

            $this->log(
                sprintf(
                    'Extracted %d files from archive "%s"',
                    count($extractedFiles),
                    $uploadedFile->getClientFilename(),
                ),
                'info',
                ['group_name' => 'ImageUploadService'],
            );

            foreach ($extractedFiles as $extractedFile) {
                try {
                    $filename = basename($extractedFile);

                    // Create proper UploadedFile object for each extracted file
                    $fileUploadedFile = new UploadedFile(
                        $extractedFile, // stream/file path
                        filesize($extractedFile), // size
                        UPLOAD_ERR_OK, // error
                        $filename, // client filename
                        mime_content_type($extractedFile), // client media type
                    );

                    $imageEntity = $this->createImageFromFile($fileUploadedFile, $galleryId);

                    if ($imageEntity) {
                        $results['created_images'][] = [
                            'id' => $imageEntity->id,
                            'name' => $imageEntity->name,
                        ];
                        $results['success_count']++;
                    } else {
                        $results['errors'][] = [
                            'file' => $filename,
                            'error' => 'Failed to create image entity',
                        ];
                        $results['error_count']++;
                    }
                } catch (Exception $e) {
                    $filename = basename($extractedFile);
                    $this->log(
                        sprintf('Error processing extracted file "%s": %s', $filename, $e->getMessage()),
                        'error',
                        ['group_name' => 'ImageUploadService'],
                    );

                    $results['errors'][] = [
                        'file' => $filename,
                        'error' => $e->getMessage(),
                    ];
                    $results['error_count']++;
                }
            }
        } finally {
            // Clean up temporary files
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            if ($tempDir && is_dir($tempDir)) {
                $this->archiveExtractor->cleanup($tempDir);
            }
        }

        return $results;
    }

    /**
     * Process a single image file
     *
     * @param \Psr\Http\Message\UploadedFileInterface $uploadedFile
     * @param string|null $galleryId
     * @return array
     */
    private function processSingleImage(UploadedFileInterface $uploadedFile, ?string $galleryId): array
    {
        $imageEntity = $this->createImageFromFile($uploadedFile, $galleryId);

        if ($imageEntity) {
            return [
                'success_count' => 1,
                'error_count' => 0,
                'created_images' => [[
                    'id' => $imageEntity->id,
                    'name' => $imageEntity->name,
                ]],
                'errors' => [],
            ];
        }

        return [
            'success_count' => 0,
            'error_count' => 1,
            'created_images' => [],
            'errors' => [[
                'file' => $uploadedFile->getClientFilename(),
                'error' => 'Failed to create image entity',
            ]],
        ];
    }

    /**
     * Add an image to a gallery
     *
     * @param \App\Model\Entity\Image $image
     * @param string $galleryId
     * @return bool
     */
    private function addImageToGallery(Image $image, string $galleryId): bool
    {
        // Check if image is already in gallery
        $exists = $this->galleriesImagesTable->exists([
            'image_gallery_id' => $galleryId,
            'image_id' => $image->id,
        ]);

        if ($exists) {
            return true; // Already in gallery
        }

        $position = $this->galleriesImagesTable->getNextPosition($galleryId);
        $galleryImage = $this->galleriesImagesTable->newEntity([
            'image_gallery_id' => $galleryId,
            'image_id' => $image->id,
            'position' => $position,
        ]);

        return (bool)$this->galleriesImagesTable->save($galleryImage);
    }

    /**
     * Merge results from individual processing into main results
     *
     * @param array $mainResults
     * @param array $subResults
     * @return void
     */
    private function mergeResults(array &$mainResults, array $subResults): void
    {
        $mainResults['success_count'] += $subResults['success_count'];
        $mainResults['error_count'] += $subResults['error_count'];
        $mainResults['created_images'] = array_merge($mainResults['created_images'], $subResults['created_images']);
        $mainResults['errors'] = array_merge($mainResults['errors'], $subResults['errors']);
    }

    /**
     * Generate a user-friendly result message
     *
     * @param array $results
     * @return string
     */
    private function generateResultMessage(array $results): string
    {
        if ($results['success_count'] === 0) {
            return __('No images were processed successfully.');
        }

        if ($results['error_count'] === 0) {
            return __('Successfully processed {0} image(s).', $results['success_count']);
        }

        return __(
            'Successfully processed {0} of {1} image(s). {2} had errors.',
            $results['success_count'],
            $results['total_processed'],
            $results['error_count'],
        );
    }

    /**
     * Get human-readable upload error message
     *
     * @param int $errorCode
     * @return string
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File size exceeds limit',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
            default => 'Unknown upload error',
        };
    }
}
