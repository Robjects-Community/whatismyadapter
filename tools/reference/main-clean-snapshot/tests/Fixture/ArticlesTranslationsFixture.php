<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArticlesTranslationsFixture
 */
class ArticlesTranslationsFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'articles_translations';

    /**
     * Schema
     *
     * @var array
     */
    protected array $schema = [
        'id' => ['type' => 'uuid', 'null' => false],
        'locale' => ['type' => 'string', 'length' => 5, 'null' => false],
        'title' => ['type' => 'string', 'length' => 255, 'null' => true],
        'lede' => ['type' => 'string', 'length' => 400, 'null' => true],
        'body' => ['type' => 'text', 'null' => true],
        'summary' => ['type' => 'text', 'null' => true],
        'meta_title' => ['type' => 'text', 'null' => true],
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
            // Minimal example translation row; tests may not depend on actual values
            [
                'id' => '263a5364-a1bc-401c-9e44-49c23d066a0f', // should match an article id if needed
                'locale' => 'en_GB',
                'title' => 'Article One (EN)',
                'lede' => null,
                'body' => null,
                'summary' => null,
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
