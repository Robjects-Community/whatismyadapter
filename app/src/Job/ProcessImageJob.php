<?php
declare(strict_types=1);

namespace App\Job;

use App\Utility\SettingsManager;
use Cake\Queue\Job\Message;
use Exception;
use Imagick;
use Interop\Queue\Processor;

/**
 * ProcessImageJob Class
 *
 * This class is responsible for processing image resizing as a background job.
 * It receives image paths and resize specifications, processes the images using Imagick,
 * and logs the process.
 */
class ProcessImageJob extends AbstractJob
{
    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    protected static function getJobType(): string
    {
        return 'image processing';
    }

    /**
     * Executes the image processing task.
     *
     * This method processes an image based on the provided message arguments. It retrieves the folder path,
     * file name, and image ID from the message and processes the image for each specified size.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the arguments for image processing.
     * @return string|null Returns Processor::ACK on successful processing, or Processor::REJECT on error.
     */
    public function execute(Message $message): ?string
    {
        if (!extension_loaded('imagick')) {
            $this->log(
                'Imagick extension is not loaded',
                'error',
                ['group_name' => static::class],
            );

            return Processor::REJECT;
        }

        if (!$this->validateArguments($message, ['folder_path', 'file', 'id'])) {
            return Processor::REJECT;
        }

        $folderPath = $message->getArgument('folder_path');
        $file = $message->getArgument('file');
        $id = $message->getArgument('id');

        $imageSizes = SettingsManager::read('ImageSizes', []);

        return $this->executeWithErrorHandling($id, function () use ($folderPath, $file, $imageSizes) {
            foreach ($imageSizes as $width) {
                $this->createImage($folderPath, $file, intval($width));
            }

            return true;
        }, "Path: {$folderPath}{$file}");
    }

    /**
     * Creates a resized or copied version of an image based on target width.
     *
     * This method ensures that a directory for the processed image exists, creating it if necessary.
     * It checks if the original image exists and whether a processed version already exists.
     * If the original image is smaller than or equal to the target width, it copies the original
     * without resizing to preserve quality. Otherwise, it resizes the image to the specified width
     * while maintaining the aspect ratio using the Imagick library.
     *
     * The method includes extensive error checking and logging throughout the process:
     * - Validates and creates necessary directories
     * - Checks for existence of source and destination files
     * - Compares original and target dimensions
     * - Handles image processing with proper resource cleanup
     *
     * @param string $folder The directory where the original image is stored
     * @param string $file The name of the image file to be processed
     * @param int $width The target width for the processed image
     * @throws \Exception If the directory for the processed image cannot be created
     * @return void
     * @uses \Imagick For image processing operations
     * @logs
     * - Error if the directory cannot be created
     * - Error if the original image is not found
     * - Info if the processed image already exists (skips processing)
     * - Info when original image is copied without resizing (smaller than target)
     * - Info upon successful resizing of larger images
     * - Error if any exception occurs during processing
     * @note All logs are grouped under 'App\Job\ProcessImageJob'
     */
    private function createImage(string $folder, string $file, int $width): void
    {
        // Make sure folder for size exists
        // Ensure the folder path ends with a directory separator
        $folder = rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Create the full path including the width
        $sizeFolder = $folder . $width . DIRECTORY_SEPARATOR;

        // Check if the directory exists, if not, create it
        if (!is_dir($sizeFolder)) {
            if (!mkdir($sizeFolder, 0755, true)) {
                throw new Exception("Failed to create directory: $sizeFolder");
            }
        }

        if (!file_exists($folder . $file)) {
            throw new Exception("Original image not found for resizing: {$folder}{$file}");
        }

        if (file_exists($sizeFolder . $file)) {
            $this->log(
                sprintf('Skipped resizing, image already exists. Path: %s', $sizeFolder . $file),
                'info',
                ['group_name' => static::class],
            );

            return;
        }

        $imagick = new Imagick($folder . $file);
        $originalWidth = $imagick->getImageWidth();

        // Check if the original image is smaller than the target width
        if ($originalWidth <= $width) {
            // Just copy the original file without resizing
            copy($folder . $file, $sizeFolder . $file);
            $imagick->clear();

            $this->log(
                sprintf(
                    'Original smaller than target width, copied. Original: %s, Saved: %s (Original width: %dpx)',
                    $folder . $file,
                    $sizeFolder . $file,
                    $originalWidth,
                ),
                'info',
                ['group_name' => static::class],
            );
        } else {
            // Resize the image since it's larger than the target width
            $imagick->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);
            $imagick->writeImage($sizeFolder . $file);
            $imagick->clear();

            $this->log(
                sprintf(
                    'Successfully resized image. Original: %s, Resized: %s, Width: %dpx (Orig. width: %dpx)',
                    $folder . $file,
                    $sizeFolder . $file,
                    $width,
                    $originalWidth,
                ),
                'info',
                ['group_name' => static::class],
            );
        }
    }
}
