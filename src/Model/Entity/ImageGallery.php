<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * ImageGallery Entity
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $preview_image
 * @property bool $is_published
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $facebook_description
 * @property string|null $linkedin_description
 * @property string|null $instagram_description
 * @property string|null $twitter_description
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $created_by
 * @property string|null $modified_by
 *
 * @property \App\Model\Entity\Image[] $images
 */
class ImageGallery extends Entity
{
    use SeoEntityTrait;
    use TranslateTrait;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'slug' => true,
        'description' => true,
        'preview_image' => true,
        'is_published' => true,
        // SEO fields (managed by SeoEntityTrait)
        'meta_title' => true,
        'meta_description' => true,
        'meta_keywords' => true,
        'facebook_description' => true,
        'linkedin_description' => true,
        'instagram_description' => true,
        'twitter_description' => true,
        'created' => true,
        'modified' => true,
        'created_by' => true,
        'modified_by' => true,
        'images' => true,
    ];

    /**
     * Get the preview image URL for this gallery
     *
     * @return string|null Preview image URL or null if no preview exists
     */
    public function getPreviewImageUrl(): ?string
    {
        if (!$this->preview_image) {
            return null;
        }

        $previewPath = WWW_ROOT . 'files' . DS . 'ImageGalleries' . DS . 'preview' . DS . $this->preview_image;
        if (!file_exists($previewPath)) {
            return null;
        }

        return '/files/ImageGalleries/preview/' . $this->preview_image;
    }

    /**
     * Check if this gallery has a preview image available
     *
     * @return bool True if preview image exists and is accessible
     */
    public function hasPreviewImage(): bool
    {
        return $this->getPreviewImageUrl() !== null;
    }

    /**
     * Get the status display name
     *
     * @return string Published or Un-Published
     */
    public function getStatusDisplay(): string
    {
        return $this->is_published ? __('Published') : __('Un-Published');
    }

    /**
     * Get the status CSS class for badges
     *
     * @return string CSS class for status badge
     */
    public function getStatusClass(): string
    {
        return $this->is_published ? 'bg-success' : 'bg-warning';
    }

    /**
     * Get the number of images in this gallery
     *
     * @return int Number of images
     */
    public function getImageCount(): int
    {
        return is_array($this->images) ? count($this->images) : 0;
    }

    /**
     * Get the total file size of all images in this gallery
     *
     * @return int Total file size in bytes
     */
    public function getTotalFileSize(): int
    {
        if (!is_array($this->images) || empty($this->images)) {
            return 0;
        }

        $totalSize = 0;
        foreach ($this->images as $image) {
            if (isset($image->file_size) && is_numeric($image->file_size)) {
                $totalSize += (int)$image->file_size;
            }
        }

        return $totalSize;
    }
}
