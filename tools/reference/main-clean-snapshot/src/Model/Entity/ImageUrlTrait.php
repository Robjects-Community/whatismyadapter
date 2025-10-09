<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Utility\SettingsManager;

trait ImageUrlTrait
{
    /**
     * Retrieves the URL for an image at original size by removing the 'webroot/' prefix from the directory path.
     *
     * This method constructs the image URL by concatenating the directory and image name,
     * and then removes the 'webroot/' part from the path to generate a relative URL.
     *
     * @return string The relative URL of the image.
     */
    protected function _getImageUrl(): string
    {
        $url = str_replace('webroot/', '', $this->dir . $this->image);
        // Ensure URL starts with a slash for absolute path
        return '/' . ltrim($url, '/');
    }

    /**
     * Magic method to dynamically retrieve image URLs based on image size names.
     *
     * This method checks if the requested attribute name matches the pattern for image URLs
     * (e.g., "smallImageUrl") and returns the corresponding image URL if available.
     * If the attribute does not match the pattern, it delegates to the parent::__get() method.
     *
     * @param string $attribute The name of the attribute being accessed.
     * @return mixed The URL of the image if the attribute matches the pattern, otherwise the result of parent::__get().
     */
    public function &__get(string $attribute): mixed
    {
        if (preg_match('/^(.+)ImageUrl$/', $attribute, $matches)) {
            $size = lcfirst($matches[1]);
            $imageSizes = SettingsManager::read('ImageSizes');
            if (isset($imageSizes[$size])) {
                $url = $this->getImageUrlBySize($size);

                return $url;
            }
        }

        return parent::__get($attribute);
    }

    /**
     * Retrieves the URL for an image of a specified size.
     *
     * This method constructs the URL for an image based on the provided size by
     * removing the 'webroot/' prefix and appending the directory, size, and image name.
     *
     * @param string $size The size of the image (e.g., 'thumbnail', 'medium').
     * @return string The constructed URL for the image.
     */
    public function getImageUrlBySize(string $size): string
    {
        $imageSizes = SettingsManager::read('ImageSizes');

        // Use the width value as directory name (e.g., 100, 300, 600)
        if (isset($imageSizes[$size])) {
            $url = str_replace('webroot/', '', $this->dir . $imageSizes[$size] . DS . $this->image);
        } else {
            // Fallback to original image if size not found
            $url = str_replace('webroot/', '', $this->dir . $this->image);
        }

        // Ensure URL starts with a slash for absolute path
        return '/' . ltrim($url, '/');
    }
}
