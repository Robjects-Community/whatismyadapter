<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PagesFixture
 * 
 * Pages are actually articles with kind='page'.
 * This fixture provides an alias to the articles table for page-specific tests.
 */
class PagesFixture extends TestFixture
{
    public string $table = 'articles';

    public array $fields = [
        'id' => ['type' => 'uuid', 'null' => false],
        'user_id' => ['type' => 'uuid', 'null' => false],
        'title' => ['type' => 'string', 'length' => 255, 'null' => false],
        'slug' => ['type' => 'string', 'length' => 255, 'null' => false],
        'kind' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => 'article'],
        'body' => ['type' => 'text', 'null' => true],
        'excerpt' => ['type' => 'text', 'null' => true],
        'is_published' => ['type' => 'boolean', 'null' => false, 'default' => false],
        'main_menu' => ['type' => 'boolean', 'null' => false, 'default' => false],
        'footer_menu' => ['type' => 'boolean', 'null' => false, 'default' => false],
        'meta_title' => ['type' => 'string', 'length' => 255, 'null' => true],
        'meta_description' => ['type' => 'text', 'null' => true],
        'meta_keywords' => ['type' => 'text', 'null' => true],
        'featured' => ['type' => 'boolean', 'null' => false, 'default' => false],
        'view_count' => ['type' => 'integer', 'null' => false, 'default' => 0],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'unique_slug' => ['type' => 'unique', 'columns' => ['slug']],
        ],
        '_indexes' => [
            'user_id_idx' => ['type' => 'index', 'columns' => ['user_id']],
            'kind_idx' => ['type' => 'index', 'columns' => ['kind']],
            'is_published_idx' => ['type' => 'index', 'columns' => ['is_published']],
            'slug_idx' => ['type' => 'index', 'columns' => ['slug']],
        ],
    ];

    public function init(): void
    {
        $this->records = [
            [
                'id' => '11111111-page-1111-1111-111111111111',
                'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
                'title' => 'About Us',
                'slug' => 'about-us',
                'kind' => 'page',
                'body' => 'This is the about us page content.',
                'excerpt' => 'Learn more about our company.',
                'is_published' => true,
                'main_menu' => true,
                'footer_menu' => true,
                'meta_title' => 'About Us - Company Information',
                'meta_description' => 'Learn more about our company and our mission.',
                'meta_keywords' => 'about, company, information',
                'featured' => false,
                'view_count' => 100,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => '22222222-page-2222-2222-222222222222',
                'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'kind' => 'page',
                'body' => 'Get in touch with us using the form below.',
                'excerpt' => 'Contact us for inquiries.',
                'is_published' => true,
                'main_menu' => true,
                'footer_menu' => false,
                'meta_title' => 'Contact Us - Get In Touch',
                'meta_description' => 'Contact us for any questions or inquiries.',
                'meta_keywords' => 'contact, email, phone',
                'featured' => false,
                'view_count' => 50,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
            [
                'id' => '33333333-page-3333-3333-333333333333',
                'user_id' => '90d91e66-5d90-412b-aeaa-4d51fa110794',
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'kind' => 'page',
                'body' => 'Our privacy policy details how we handle your data.',
                'excerpt' => 'Read our privacy policy.',
                'is_published' => true,
                'main_menu' => false,
                'footer_menu' => true,
                'meta_title' => 'Privacy Policy',
                'meta_description' => 'Read our privacy policy and data handling practices.',
                'meta_keywords' => 'privacy, policy, data, security',
                'featured' => false,
                'view_count' => 75,
                'created' => '2025-01-01 00:00:00',
                'modified' => '2025-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
