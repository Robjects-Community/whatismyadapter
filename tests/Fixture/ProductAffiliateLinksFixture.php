<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductAffiliateLinksFixture
 */
class ProductAffiliateLinksFixture extends TestFixture
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
                'id' => 'affiliate-001',
                'product_id' => 'prod-001-usb-c-cable',
                'affiliate_network' => 'Amazon Associates',
                'merchant_name' => 'Amazon',
                'affiliate_url' => 'https://amazon.com/dp/B08XYZ123?tag=testsite-20',
                'price' => 19.99,
                'currency' => 'USD',
                'commission_rate' => 4.00,
                'is_primary' => 1,
                'is_active' => 1,
                'click_count' => 127,
                'conversion_count' => 8,
                'last_checked' => '2024-01-30 08:00:00',
                'availability_status' => 'available',
                'created' => '2024-01-15 10:00:00',
                'modified' => '2024-01-30 08:00:00'
            ],
            [
                'id' => 'affiliate-002',
                'product_id' => 'prod-001-usb-c-cable',
                'affiliate_network' => 'eBay Partner Network',
                'merchant_name' => 'eBay',
                'affiliate_url' => 'https://ebay.com/itm/123456789?mkcid=1&mkrid=711-53200-19255-0&campid=5338273189',
                'price' => 17.50,
                'currency' => 'USD',
                'commission_rate' => 6.00,
                'is_primary' => 0,
                'is_active' => 1,
                'click_count' => 45,
                'conversion_count' => 3,
                'last_checked' => '2024-01-29 12:30:00',
                'availability_status' => 'available',
                'created' => '2024-01-16 14:20:00',
                'modified' => '2024-01-29 12:30:00'
            ],
            [
                'id' => 'affiliate-003',
                'product_id' => 'prod-002-hdmi-adapter',
                'affiliate_network' => 'Amazon Associates',
                'merchant_name' => 'Amazon',
                'affiliate_url' => 'https://amazon.com/dp/B09ABC456?tag=testsite-20',
                'price' => 34.99,
                'currency' => 'USD',
                'commission_rate' => 4.00,
                'is_primary' => 1,
                'is_active' => 1,
                'click_count' => 89,
                'conversion_count' => 12,
                'last_checked' => '2024-01-30 08:00:00',
                'availability_status' => 'available',
                'created' => '2024-01-16 09:30:00',
                'modified' => '2024-01-30 08:00:00'
            ],
            [
                'id' => 'affiliate-004',
                'product_id' => 'prod-002-hdmi-adapter',
                'affiliate_network' => 'Best Buy Affiliate',
                'merchant_name' => 'Best Buy',
                'affiliate_url' => 'https://bestbuy.com/site/product/6445678.p?skuId=6445678&ref=app_android',
                'price' => 39.99,
                'currency' => 'USD',
                'commission_rate' => 3.00,
                'is_primary' => 0,
                'is_active' => 1,
                'click_count' => 23,
                'conversion_count' => 2,
                'last_checked' => '2024-01-28 16:45:00',
                'availability_status' => 'available',
                'created' => '2024-01-18 11:15:00',
                'modified' => '2024-01-28 16:45:00'
            ],
            [
                'id' => 'affiliate-005',
                'product_id' => 'prod-001-usb-c-cable',
                'affiliate_network' => 'ShareASale',
                'merchant_name' => 'TechGear Direct',
                'affiliate_url' => 'https://techgeardirect.com/usb-c-lightning-cable?sscid=a1k5_xyz123',
                'price' => 22.95,
                'currency' => 'USD',
                'commission_rate' => 8.00,
                'is_primary' => 0,
                'is_active' => 0,
                'click_count' => 12,
                'conversion_count' => 1,
                'last_checked' => '2024-01-25 14:20:00',
                'availability_status' => 'unavailable',
                'created' => '2024-01-20 09:45:00',
                'modified' => '2024-01-25 14:20:00'
            ],
            [
                'id' => 'affiliate-006',
                'product_id' => 'prod-003-unpublished',
                'affiliate_network' => 'Amazon Associates',
                'merchant_name' => 'Amazon',
                'affiliate_url' => 'https://amazon.com/dp/B07DEF789?tag=testsite-20',
                'price' => 9.99,
                'currency' => 'USD',
                'commission_rate' => 4.00,
                'is_primary' => 1,
                'is_active' => 1,
                'click_count' => 0,
                'conversion_count' => 0,
                'last_checked' => '2024-01-17 12:00:00',
                'availability_status' => 'unknown',
                'created' => '2024-01-17 12:00:00',
                'modified' => '2024-01-17 12:00:00'
            ]
        ];
        parent::init();
    }
}
