<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArticlesTagsFixture
 */
class ArticlesTagsFixture extends TestFixture
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
                'article_id' => 'aaa29809-ed48-428c-9e24-5f8d55aa0c2d',
                'tag_id' => 'a4597c2b-5dbf-4b1f-be67-73061b00e5e6',
            ],
        ];
        parent::init();
    }
}
