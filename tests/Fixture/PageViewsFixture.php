<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PageViewsFixture extends TestFixture
{
    public string $table = 'page_views';

    public function init(): void
    {
        $this->records = [
            [
                'id' => '30000000-0000-0000-0000-000000000000',
                'path' => '/en/home',
                'user_agent' => 'Mozilla/5.0',
                'ip' => '203.0.113.1',
                'created' => '2025-08-11 10:05:00',
            ],
            [
                'id' => '30000000-0000-0000-0000-000000000001',
                'path' => '/en/articles/first-article',
                'user_agent' => 'curl/8.1',
                'ip' => '203.0.113.2',
                'created' => '2025-08-11 10:10:00',
            ],
        ];
        parent::init();
    }
}
