<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ImageGalleriesTranslationsFixture
 */
class ImageGalleriesTranslationsFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'image_galleries_translations';

    /**
     * Schema
     *
     * @var array
     */
    protected array $schema = [
        'id' => ['type' => 'uuid', 'null' => false],
        'locale' => ['type' => 'string', 'length' => 5, 'null' => false],
        'name' => ['type' => 'string', 'length' => 255, 'null' => true],
        'description' => ['type' => 'text', 'null' => true],
        'meta_title' => ['type' => 'string', 'length' => 255, 'null' => true],
        'meta_description' => ['type' => 'text', 'null' => true],
        'meta_keywords' => ['type' => 'text', 'null' => true],
        'facebook_description' => ['type' => 'text', 'null' => true],
        'linkedin_description' => ['type' => 'text', 'null' => true],
        'instagram_description' => ['type' => 'text', 'null' => true],
        'twitter_description' => ['type' => 'text', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id','locale']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => '32cf930e-1456-4cf9-ab9e-a7db7250b1ea', // gallery id
                'locale' => 'en_GB',
                'name' => 'Example Gallery (EN)',
                'description' => null,
                'meta_title' => null,
                'meta_description' => null,
                'meta_keywords' => null,
                'facebook_description' => null,
                'linkedin_description' => null,
                'instagram_description' => null,
                'twitter_description' => null,
            ],
        ];
        parent::init();
    }
}
