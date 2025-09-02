<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TagsFixture
 */
class TagsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'd57e6dde-c333-4bbe-b1f1-c3bb8f00cf4c',
                'title' => 'Technology',
                'slug' => 'technology',
                'description' => 'Articles related to technology',
                'created' => '2024-09-27 07:52:51',
                'modified' => '2024-09-27 07:52:51',
                'parent_id' => null,
                'lft' => 1,
                'rght' => 20,
            ],
            [
                'id' => 'e57e6dde-c333-4bbe-b1f1-c3bb8f00cf4d',
                'title' => 'Science',
                'slug' => 'science',
                'description' => 'Scientific discoveries and research',
                'created' => '2024-09-27 07:53:51',
                'modified' => '2024-09-27 07:53:51',
                'parent_id' => null,
                'lft' => 21,
                'rght' => 22,
            ],
            [
                'id' => 'f57e6dde-c333-4bbe-b1f1-c3bb8f00cf4e',
                'title' => 'Programming',
                'slug' => 'programming',
                'description' => 'Coding and software development',
                'created' => '2024-09-27 07:54:51',
                'modified' => '2024-09-27 07:54:51',
                'parent_id' => 'd57e6dde-c333-4bbe-b1f1-c3bb8f00cf4c',
                'lft' => 2,
                'rght' => 7,
            ],
            [
                'id' => 'g57e6dde-c333-4bbe-b1f1-c3bb8f00cf4f',
                'title' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Web technologies and frameworks',
                'created' => '2024-09-27 07:55:51',
                'modified' => '2024-09-27 07:55:51',
                'parent_id' => 'f57e6dde-c333-4bbe-b1f1-c3bb8f00cf4e',
                'lft' => 3,
                'rght' => 4,
            ],
            [
                'id' => 'h57e6dde-c333-4bbe-b1f1-c3bb8f00cf4g',
                'title' => 'AI',
                'slug' => 'ai',
                'description' => 'Artificial Intelligence and Machine Learning',
                'created' => '2024-09-27 07:56:51',
                'modified' => '2024-09-27 07:56:51',
                'parent_id' => 'f57e6dde-c333-4bbe-b1f1-c3bb8f00cf4e',
                'lft' => 5,
                'rght' => 6,
            ],
            [
                'id' => 'i57e6dde-c333-4bbe-b1f1-c3bb8f00cf4h',
                'title' => 'Data Science',
                'slug' => 'data-science',
                'description' => 'Data analysis and big data',
                'created' => '2024-09-27 07:57:51',
                'modified' => '2024-09-27 07:57:51',
                'parent_id' => 'd57e6dde-c333-4bbe-b1f1-c3bb8f00cf4c',
                'lft' => 8,
                'rght' => 9,
            ],
            [
                'id' => 'j57e6dde-c333-4bbe-b1f1-c3bb8f00cf4i',
                'title' => 'Mobile Development',
                'slug' => 'mobile-development',
                'description' => 'iOS and Android app development',
                'created' => '2024-09-27 07:58:51',
                'modified' => '2024-09-27 07:58:51',
                'parent_id' => 'd57e6dde-c333-4bbe-b1f1-c3bb8f00cf4c',
                'lft' => 10,
                'rght' => 11,
            ],
            [
                'id' => 'k57e6dde-c333-4bbe-b1f1-c3bb8f00cf4j',
                'title' => 'Cloud Computing',
                'slug' => 'cloud-computing',
                'description' => 'Cloud services and infrastructure',
                'created' => '2024-09-27 07:59:51',
                'modified' => '2024-09-27 07:59:51',
                'parent_id' => 'd57e6dde-c333-4bbe-b1f1-c3bb8f00cf4c',
                'lft' => 12,
                'rght' => 13,
            ],
            [
                'id' => 'l57e6dde-c333-4bbe-b1f1-c3bb8f00cf4k',
                'title' => 'Cybersecurity',
                'slug' => 'cybersecurity',
                'description' => 'Information security and data protection',
                'created' => '2024-09-27 08:00:51',
                'modified' => '2024-09-27 08:00:51',
                'parent_id' => 'd57e6dde-c333-4bbe-b1f1-c3bb8f00cf4c',
                'lft' => 14,
                'rght' => 15,
            ],
            [
                'id' => 'm57e6dde-c333-4bbe-b1f1-c3bb8f00cf4l',
                'title' => 'DevOps',
                'slug' => 'devops',
                'description' => 'Development operations and practices',
                'created' => '2024-09-27 08:01:51',
                'modified' => '2024-09-27 08:01:51',
                'parent_id' => 'd57e6dde-c333-4bbe-b1f1-c3bb8f00cf4c',
                'lft' => 16,
                'rght' => 17,
            ],
        ];
        parent::init();
    }
}
