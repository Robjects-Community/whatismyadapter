<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QuizSubmissionsFixture
 */
class QuizSubmissionsFixture extends TestFixture
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
                'id' => 'f62be6ac-7d25-4755-a16d-7b701eb9eba8',
                'user_id' => '2670b1c2-ced5-45b1-b41b-b9d8293b8cd0',
                'session_id' => 'Lorem ipsum dolor sit amet',
                'quiz_type' => 'Lorem ipsum dolor ',
                'answers' => '',
                'matched_product_ids' => '',
                'confidence_scores' => '',
                'result_summary' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'analytics' => '',
                'created' => '2025-09-06 20:06:18',
                'modified' => '2025-09-06 20:06:18',
                'created_by' => '272dfcea-8c78-48c1-8ebf-1cff0addeb0a',
                'modified_by' => 'f57ab3ca-d0a1-4235-9b86-57fce837f2ae',
            ],
        ];
        parent::init();
    }
}
