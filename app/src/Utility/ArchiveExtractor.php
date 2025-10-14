<?php
declare(strict_types=1);

namespace App\Utility;

use Exception;
use PharData;
use RuntimeException;
use ZipArchive;

/**
 * ArchiveExtractor Utility
 *
 * Handles extraction of various archive formats (ZIP, TAR, GZIP) and
 * filters extracted files to return only valid image files.
 *
 * Security features:
 * - Path traversal prevention
 * - File size limits
 * - Maximum extraction count limits
 * - Supported file type validation
 */
class ArchiveExtractor
{
    /**
     * Supported image file extensions
     */
    private array $supportedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Supported archive file extensions
     */
    private array $supportedArchiveTypes = ['zip', 'tar', 'gz', 'tar.gz', 'tgz'];

    /**
     * Maximum number of files to extract (security limit)
     */
    private int $maxFileCount = 100;

    /**
     * Maximum file size for individual files (10MB)
     */
    private int $maxFileSize = 10485760;

    /**
     * Maximum total extraction size (100MB)
     */
    private int $maxTotalSize = 104857600;

    /**
     * Extract archive and return list of image files
     *
     * @param string $archivePath Path to the archive file
     * @return array List of extracted image file paths
     * @throws \RuntimeException
     */
    public function extract(string $archivePath): array
    {
        if (!file_exists($archivePath)) {
            throw new RuntimeException("Archive file not found: {$archivePath}");
        }

        $archiveType = $this->getArchiveType($archivePath);
        if (!$archiveType) {
            throw new RuntimeException("Unsupported archive type: {$archivePath}");
        }

        $tempDir = $this->getTempDirectory();
        $extractedFiles = [];

        try {
            switch ($archiveType) {
                case 'zip':
                    $extractedFiles = $this->extractZip($archivePath, $tempDir);
                    break;
                case 'tar':
                case 'tar.gz':
                case 'tgz':
                    $extractedFiles = $this->extractTar($archivePath, $tempDir);
                    break;
                default:
                    throw new RuntimeException("Unsupported archive type: {$archiveType}");
            }

            $imageFiles = $this->filterImageFiles($extractedFiles);

            if (empty($imageFiles)) {
                $this->cleanup($tempDir);
                throw new RuntimeException('No valid image files found in archive');
            }

            return $imageFiles;
        } catch (RuntimeException $e) {
            $this->cleanup($tempDir);
            throw $e;
        }
    }

    /**
     * Detect archive type based on file extension and mime type
     *
     * @param string $path
     * @return string|null Archive type or null if unsupported
     */
    private function getArchiveType(string $path): ?string
    {
        $pathInfo = pathinfo(strtolower($path));
        $extension = $pathInfo['extension'] ?? '';

        // Handle double extensions like .tar.gz
        if ($extension === 'gz' && isset($pathInfo['filename'])) {
            $innerInfo = pathinfo($pathInfo['filename']);
            if (($innerInfo['extension'] ?? '') === 'tar') {
                return 'tar.gz';
            }

            return 'gz';
        }

        if ($extension === 'tgz') {
            return 'tgz';
        }

        if (in_array($extension, $this->supportedArchiveTypes)) {
            return $extension;
        }

        return null;
    }

    /**
     * Extract ZIP files using ZipArchive
     *
     * @param string $path
     * @param string $targetDir
     * @return array List of extracted files
     * @throws \RuntimeException
     */
    private function extractZip(string $path, string $targetDir): array
    {
        $zip = new ZipArchive();
        $result = $zip->open($path);

        if ($result !== true) {
            throw new RuntimeException("Failed to open ZIP file: {$path} (Error: {$result})");
        }

        if ($zip->numFiles > $this->maxFileCount) {
            $zip->close();
            throw new RuntimeException(
                "Archive contains too many files ({$zip->numFiles}). Maximum allowed: {$this->maxFileCount}",
            );
        }

        $extractedFiles = [];
        $totalSize = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);

            if ($stat === false) {
                continue;
            }

            // Security: Check for path traversal
            $filename = $stat['name'];
            if ($this->isPathTraversal($filename)) {
                continue;
            }

            // Skip directories
            if (substr($filename, -1) === '/') {
                continue;
            }

            // Check file size limits
            if ($stat['size'] > $this->maxFileSize) {
                continue;
            }

            $totalSize += $stat['size'];
            if ($totalSize > $this->maxTotalSize) {
                $zip->close();
                throw new RuntimeException('Archive total size exceeds limit');
            }

            $extractPath = $targetDir . DIRECTORY_SEPARATOR . basename($filename);

            if ($zip->extractTo($targetDir, $filename)) {
                $extractedFiles[] = $extractPath;
            }
        }

        $zip->close();

        return $extractedFiles;
    }

    /**
     * Extract TAR files using PharData
     *
     * @param string $path
     * @param string $targetDir
     * @return array List of extracted files
     * @throws \RuntimeException
     */
    private function extractTar(string $path, string $targetDir): array
    {
        try {
            $phar = new PharData($path);

            // Count files first
            $fileCount = iterator_count($phar);
            if ($fileCount > $this->maxFileCount) {
                throw new RuntimeException(
                    "Archive contains too many files ({$fileCount}). Maximum allowed: {$this->maxFileCount}",
                );
            }

            $extractedFiles = [];
            $totalSize = 0;

            foreach ($phar as $file) {
                $filename = $file->getFilename();

                // Security: Check for path traversal
                if ($this->isPathTraversal($filename)) {
                    continue;
                }

                // Skip directories
                if ($file->isDir()) {
                    continue;
                }

                // Check file size limits
                $fileSize = $file->getSize();
                if ($fileSize > $this->maxFileSize) {
                    continue;
                }

                $totalSize += $fileSize;
                if ($totalSize > $this->maxTotalSize) {
                    throw new RuntimeException('Archive total size exceeds limit');
                }

                $extractPath = $targetDir . DIRECTORY_SEPARATOR . basename($filename);

                if (copy($file->getPathname(), $extractPath)) {
                    $extractedFiles[] = $extractPath;
                }
            }

            return $extractedFiles;
        } catch (Exception $e) {
            throw new RuntimeException('Failed to extract TAR file: ' . $e->getMessage());
        }
    }

    /**
     * Check for path traversal attempts
     *
     * @param string $filename
     * @return bool True if path traversal detected
     */
    private function isPathTraversal(string $filename): bool
    {
        // Check for .. in path
        if (strpos($filename, '..') !== false) {
            return true;
        }

        // Check for absolute paths
        if (substr($filename, 0, 1) === '/' || substr($filename, 1, 1) === ':') {
            return true;
        }

        return false;
    }

    /**
     * Filter and validate image files
     *
     * @param array $files
     * @return array Only valid image files
     */
    private function filterImageFiles(array $files): array
    {
        $imageFiles = [];

        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }

            $pathInfo = pathinfo(strtolower($file));
            $extension = $pathInfo['extension'] ?? '';

            if (in_array($extension, $this->supportedImageTypes)) {
                // Additional validation: check if it's actually an image
                if ($this->isValidImageFile($file)) {
                    $imageFiles[] = $file;
                }
            }
        }

        return $imageFiles;
    }

    /**
     * Validate that a file is actually an image
     *
     * @param string $filePath
     * @return bool
     */
    private function isValidImageFile(string $filePath): bool
    {
        $imageInfo = getimagesize($filePath);

        return $imageInfo !== false;
    }

    /**
     * Get temporary directory for extraction
     *
     * @return string Path to temp directory
     * @throws \RuntimeException
     */
    private function getTempDirectory(): string
    {
        $tempBase = sys_get_temp_dir();
        $tempDir = $tempBase . DIRECTORY_SEPARATOR . 'willow_extract_' . uniqid();

        if (!mkdir($tempDir, 0755, true)) {
            throw new RuntimeException("Failed to create temporary directory: {$tempDir}");
        }

        return $tempDir;
    }

    /**
     * Cleanup temporary files and directories
     *
     * @param string $tempDir
     * @return void
     */
    public function cleanup(string $tempDir): void
    {
        if (empty($tempDir) || !is_dir($tempDir)) {
            return;
        }

        // Security: Only delete directories we created
        if (strpos($tempDir, 'willow_extract_') === false) {
            return;
        }

        $this->deleteDirectory($tempDir);
    }

    /**
     * Recursively delete a directory and all its contents
     *
     * @param string $dir Directory to delete
     * @return void
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    /**
     * Get supported image types
     *
     * @return array
     */
    public function getSupportedImageTypes(): array
    {
        return $this->supportedImageTypes;
    }

    /**
     * Get supported archive types
     *
     * @return array
     */
    public function getSupportedArchiveTypes(): array
    {
        return $this->supportedArchiveTypes;
    }

    /**
     * Set security limits
     *
     * @param int $maxFileCount
     * @param int $maxFileSize
     * @param int $maxTotalSize
     * @return void
     */
    public function setLimits(int $maxFileCount, int $maxFileSize, int $maxTotalSize): void
    {
        $this->maxFileCount = $maxFileCount;
        $this->maxFileSize = $maxFileSize;
        $this->maxTotalSize = $maxTotalSize;
    }
}
