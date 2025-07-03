<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ImageGalleriesImage Entity
 *
 * @property string $id
 * @property string $image_gallery_id
 * @property string $image_id
 * @property int $position
 * @property string|null $caption
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\ImageGallery $image_gallery
 * @property \App\Model\Entity\Image $image
 */
class ImageGalleriesImage extends Entity
{
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
        'image_gallery_id' => true,
        'image_id' => true,
        'position' => true,
        'caption' => true,
        'created' => true,
        'modified' => true,
        'image_gallery' => true,
        'image' => true,
    ];
}
