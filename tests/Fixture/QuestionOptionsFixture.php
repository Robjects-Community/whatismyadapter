<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QuestionOptionsFixture
 */
class QuestionOptionsFixture extends TestFixture
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
                'id' => 1,
                'question_id' => 1,
                'option_text' => 'Lorem ipsum dolor sit amet',
                'is_correct' => 1,
            ],
        ];
        parent::init();
    }
}
