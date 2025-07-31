<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductViewsFixture
 */
class ProductViewsFixture extends TestFixture
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
                'id' => 'view-001',
                'product_id' => 'prod-001-usb-c-cable',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'referer' => 'https://google.com/search?q=usb-c+lightning+cable',
                'created' => '2024-01-15 11:30:00'
            ],
            [
                'id' => 'view-002',
                'product_id' => 'prod-001-usb-c-cable',
                'ip_address' => '10.0.0.15',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
                'referer' => 'https://example.com/products',
                'created' => '2024-01-15 14:22:00'
            ],
            [
                'id' => 'view-003',
                'product_id' => 'prod-001-usb-c-cable',
                'ip_address' => '203.0.113.45',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
                'referer' => 'https://facebook.com',
                'created' => '2024-01-16 09:15:00'
            ],
            [
                'id' => 'view-004',
                'product_id' => 'prod-002-hdmi-adapter',
                'ip_address' => '192.168.1.200',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
                'referer' => 'https://google.com/search?q=usb-c+hdmi+adapter',
                'created' => '2024-01-16 10:45:00'
            ],
            [
                'id' => 'view-005',
                'product_id' => 'prod-002-hdmi-adapter',
                'ip_address' => '172.16.0.50',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'referer' => 'https://reddit.com/r/UsbCHardware',
                'created' => '2024-01-16 15:30:00'
            ],
            [
                'id' => 'view-006',
                'product_id' => 'prod-001-usb-c-cable',
                'ip_address' => '198.51.100.25',
                'user_agent' => 'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0',
                'referer' => 'https://twitter.com',
                'created' => '2024-01-17 12:10:00'
            ],
            [
                'id' => 'view-007',
                'product_id' => 'prod-002-hdmi-adapter',
                'ip_address' => '203.0.113.100',
                'user_agent' => 'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
                'referer' => 'https://youtube.com',
                'created' => '2024-01-18 08:20:00'
            ],
            [
                'id' => 'view-008',
                'product_id' => 'prod-001-usb-c-cable',
                'ip_address' => '192.168.1.150',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59',
                'referer' => null,
                'created' => '2024-01-18 16:45:00'
            ],
            [
                'id' => 'view-009',
                'product_id' => 'prod-003-unpublished',
                'ip_address' => '10.0.0.5',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'referer' => 'https://example.com/admin/products',
                'created' => '2024-01-17 12:30:00'
            ],
            [
                'id' => 'view-010',
                'product_id' => 'prod-001-usb-c-cable',
                'ip_address' => '203.0.113.200',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36',
                'referer' => 'https://bing.com/search?q=best+usb-c+cables',
                'created' => '2024-01-19 11:15:00'
            ]
        ];
        parent::init();
    }
}
