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
                'id' => 'a4a63718-76be-490e-9f24-5c2671bb846b',
                'ip_address' => 'Lorem ipsum dolor sit amet',
                'reason' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'blocked_at' => 1759867968,
                'expires_at' => '2025-10-07 15:12:48',
                'created' => '2025-10-07 15:12:48',
                'modified' => '2025-10-07 15:12:48',
            ],
        ];
        parent::init();
    }
}
