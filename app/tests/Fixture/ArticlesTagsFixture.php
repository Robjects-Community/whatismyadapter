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
                'article_id' => 'c4c11fcc-0f87-49b4-a703-43367b6f5b8f',
                'tag_id' => '8ed18e31-c0a9-445b-b52d-9eec7b428705',
            ],
        ];
        parent::init();
    }
}
