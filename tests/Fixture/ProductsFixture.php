<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsFixture
 */
class ProductsFixture extends TestFixture
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
                'id' => 'prod-001-usb-c-cable',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'kind' => 'adapter',
                'featured' => 0,
                'title' => 'USB-C to Lightning Cable',
                'lede' => 'High-quality USB-C to Lightning cable for fast charging',
                'slug' => 'usb-c-to-lightning-cable',
                'body' => 'Premium quality cable supporting fast charging and data transfer.',
                'manufacturer' => 'Anker',
                'model_number' => 'A8612011',
                'product_code' => 'ANK-A8612011',
                'price' => 19.99,
                'currency' => 'USD',
                'availability_status' => 'in_stock',
                'stock_quantity' => 100,
                'reliability_score' => 4.50,
                'entry_input_type' => 'developer',
                'customer_peer_verification_count' => 5,
                'verification_status' => 'approved',
                'is_published' => 1,
                'created' => '2024-01-15 10:00:00',
                'modified' => '2024-01-15 10:00:00',
                'published' => '2024-01-15 11:00:00',
                'technical_specs' => '{"power_rating": "27W", "data_transfer": "480Mbps", "length": "1m"}',
                'word_count' => 12,
                'lft' => 1,
                'rght' => 2,
                'view_count' => 150
            ],
            [
                'id' => 'prod-002-hdmi-adapter',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'kind' => 'adapter',
                'featured' => 1,
                'title' => 'USB-C to HDMI Adapter',
                'lede' => '4K HDMI output adapter for USB-C devices',
                'slug' => 'usb-c-to-hdmi-adapter',
                'body' => 'Supports 4K@60Hz video output with HDR support.',
                'manufacturer' => 'Belkin',
                'model_number' => 'AVC002btBK',
                'product_code' => 'BLK-AVC002',
                'price' => 34.99,
                'currency' => 'USD',
                'availability_status' => 'in_stock',
                'stock_quantity' => 75,
                'reliability_score' => 4.20,
                'entry_input_type' => 'user_submission',
                'customer_peer_verification_count' => 3,
                'verification_status' => 'approved',
                'is_published' => 1,
                'created' => '2024-01-16 09:30:00',
                'modified' => '2024-01-16 09:30:00',
                'published' => '2024-01-16 10:30:00',
                'technical_specs' => '{"resolution": "4K@60Hz", "hdr_support": true, "dimensions": "50x20x10mm"}',
                'word_count' => 8,
                'lft' => 3,
                'rght' => 4,
                'view_count' => 89
            ],
            [
                'id' => 'prod-003-unpublished',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'kind' => 'adapter',
                'featured' => 0,
                'title' => 'Unpublished Test Product',
                'lede' => 'Test product for workflow testing',
                'slug' => 'unpublished-test-product',
                'body' => 'This product is used for testing publication workflow.',
                'manufacturer' => 'TestBrand',
                'model_number' => 'TEST-001',
                'product_code' => 'TST-001',
                'price' => 9.99,
                'currency' => 'USD',
                'availability_status' => 'pre_order',
                'stock_quantity' => 0,
                'reliability_score' => 0.00,
                'entry_input_type' => 'developer',
                'customer_peer_verification_count' => 0,
                'verification_status' => 'pending',
                'is_published' => 0,
                'created' => '2024-01-17 12:00:00',
                'modified' => '2024-01-17 12:00:00',
                'published' => null,
                'technical_specs' => null,
                'word_count' => 10,
                'lft' => 5,
                'rght' => 6,
                'view_count' => 0
            ]
        ];
        parent::init();
    }
}
