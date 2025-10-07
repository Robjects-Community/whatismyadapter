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
                'id' => 'b8d57e1a-d14b-437d-b212-72ef6dca6e8e',
                'foreign_key' => 'ed6d3e24-24e1-4948-9690-1e08ebe4c98f',
                'model' => 'Lorem ipsum dolor sit amet',
                'user_id' => 'cc4c69ea-1b0a-4af9-8bc6-14129f33c9dd',
                'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'display' => 1,
                'is_inappropriate' => 1,
                'is_analyzed' => 1,
                'inappropriate_reason' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-10-07 15:12:53',
                'modified' => '2025-10-07 15:12:53',
                'created_by' => 'ffc40efc-4eac-4fb5-9df6-f9af31ff5405',
                'modified_by' => 'fb7b636a-6cea-4b41-aea7-2a667e33056e',
            ],
        ];
        parent::init();
    }
}
