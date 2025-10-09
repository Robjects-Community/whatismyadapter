<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BlockedIpsFixture
 */
class BlockedIpsFixture extends TestFixture
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
                'id' => '73e2efe1-7c42-43e1-a0b1-3cd969f2db51',
                'ip_address' => 'Lorem ipsum dolor sit amet',
                'reason' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'blocked_at' => 1752353317,
                'expires_at' => '2025-07-12 21:48:37',
                'created' => '2025-07-12 21:48:37',
                'modified' => '2025-07-12 21:48:37',
            ],
        ];
        parent::init();
    }
}
