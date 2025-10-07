<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    public string $table = 'articles';

    public function init(): void
    {
        $this->records = [
            [
                'id' => '10000000-0000-0000-0000-000000000000',
                'user_id' => '00000000-0000-0000-0000-000000000001',
                'title' => 'First Article',
                'slug' => 'first-article',
                'body' => 'Lorem ipsum dolor sit amet',
                'status' => 'published',
                'created' => '2025-08-10 10:00:00',
                'modified' => '2025-08-10 10:00:00',
            ],
            [
                'id' => '20000000-0000-0000-0000-000000000000',
                'user_id' => '00000000-0000-0000-0000-000000000001',
                'title' => 'Second Article',
                'slug' => 'second-article',
                'body' => 'Consectetur adipiscing elit',
                'status' => 'draft',
                'created' => '2025-08-11 12:00:00',
                'modified' => '2025-08-11 12:00:00',
            ],
        ];
        parent::init();
    }
}
