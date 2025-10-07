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
                'id' => 'ece21d30-1fc3-4b87-8c5f-0955db51ae57',
                'user_id' => '17358ca9-e87d-490b-8173-321f9ca5090d',
                'session_id' => 'Lorem ipsum dolor sit amet',
                'quiz_type' => 'Lorem ipsum dolor ',
                'answers' => '',
                'matched_product_ids' => '',
                'confidence_scores' => '',
                'result_summary' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'analytics' => '',
                'created' => '2025-10-07 15:13:32',
                'modified' => '2025-10-07 15:13:32',
                'created_by' => '363bdd97-5a97-4877-9a15-16bd9ad48481',
                'modified_by' => 'f376ea1c-802b-43ea-95e4-edef9f97718a',
            ],
        ];
        parent::init();
    }
}
