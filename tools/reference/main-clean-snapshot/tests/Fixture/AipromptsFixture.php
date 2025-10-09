<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AipromptsFixture
 */
class AipromptsFixture extends TestFixture
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
                'id' => '12f92bb6-668e-41a2-b07b-6e3aa059d64a',
                'task_type' => 'Lorem ipsum dolor sit amet',
                'system_prompt' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'model' => 'Lorem ipsum dolor sit amet',
                'max_tokens' => 1,
                'temperature' => 1,
                'created' => '2025-07-12 21:48:31',
                'modified' => '2025-07-12 21:48:31',
            ],
        ];
        parent::init();
    }
}
