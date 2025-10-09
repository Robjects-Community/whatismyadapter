<?php
declare(strict_types=1);

namespace App\Utility;

/**
 * File Utility
 *
 * Provides common file-related utility functions.
 */
class FileUtility
{
    /**
     * Format file size in bytes to human readable format
     *
     * @param int $bytes File size in bytes
     * @param int $precision Number of decimal places
     * @return string Formatted file size (e.g., "1.2 MB", "345 KB")
     */
    public static function formatFileSize(int $bytes, int $precision = 1): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $unitsCount = count($units);
        $unitIndex = 0;
        $size = (float)$bytes;

        while ($size >= 1024 && $unitIndex < $unitsCount - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        // Don't show decimals for bytes
        if ($unitIndex === 0) {
            $precision = 0;
        }

        return round($size, $precision) . ' ' . $units[$unitIndex];
    }
}
