<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CommentsFixture
 */
class CommentsFixture extends TestFixture
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
                'id' => '550e8400-e29b-41d4-a716-446655440000',
                'foreign_key' => '263a5364-a1bc-401c-9e44-49c23d066a0f', // Article One
                'model' => 'Articles',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f', // Admin user
                'content' => 'Test comment content',
                'display' => 1,
                'is_inappropriate' => 0,
                'is_analyzed' => 0,
                'inappropriate_reason' => null,
                'created' => '2025-07-12 21:48:38',
                'modified' => '2025-07-12 21:48:38',
            ],
            [
                'id' => '5ue8ro00-e29b-41d4-a716-446655447465',
                'foreign_key' => 'b2c0a111-b222-4ccc-8ddd-eeeeffff0002', // Article Six
                'model' => 'Articles',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02', // Non-admin user
                'content' => 'Do not disable this comment it has to appear on article six.',
                'display' => 1,
                'is_inappropriate' => 0,
                'is_analyzed' => 0,
                'inappropriate_reason' => null,
                'created' => '2025-07-12 21:48:38',
                'modified' => '2025-07-12 21:48:38',
            ],
        ];
        parent::init();
    }
}
