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
     * Table name
     *
     * @var string
     */
    public string $table = 'products';

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
                'kind' => 'cable',
                'featured' => 0,
                'title' => 'USB-C to Lightning Cable',
                'lede' => 'High-quality USB-C to Lightning cable for fast charging',
                'slug' => 'usb-c-to-lightning-cable',
                'body' => 'Premium quality cable supporting fast charging and data transfer.',
                'markdown' => null,
                'summary' => 'Fast charging USB-C to Lightning cable',
                'image' => null,
                'alt_text' => null,
                'keywords' => 'USB-C, Lightning, cable, charging',
                'name' => null,
                'dir' => null,
                'size' => null,
                'mime' => null,
                'is_published' => 1,
                'created' => '2024-01-15 10:00:00',
                'modified' => '2024-01-15 10:00:00',
                'published' => '2024-01-15 11:00:00',
                'meta_title' => 'USB-C to Lightning Cable - Fast Charging',
                'meta_description' => 'High-quality USB-C to Lightning cable for fast charging your iPhone and iPad.',
                'meta_keywords' => 'USB-C Lightning cable fast charging iPhone iPad',
                'facebook_description' => 'Get the best USB-C to Lightning cable for fast charging.',
                'linkedin_description' => 'Professional-grade USB-C to Lightning cable.',
                'instagram_description' => '⚡ Fast charging USB-C to Lightning cable!',
                'twitter_description' => 'Fast charging USB-C to Lightning cable.',
                'word_count' => 12,
                'parent_id' => null,
                'lft' => 1,
                'rght' => 4,
                'main_menu' => 0,
                'view_count' => 150,
                'product_code' => 'ANK-A8612011',
                'manufacturer' => 'Anker',
                'model_number' => 'A8612011',
                'price' => 19.99,
                'currency' => 'USD',
                'availability_status' => 'in_stock',
                'stock_quantity' => 100,
                'reliability_score' => 4.50,
                'entry_input_type' => 'developer',
                'customer_peer_verification_count' => 5,
                'verification_status' => 'approved',
                'ai_analysis_score' => 4.2,
                'verification_notes' => null,
                'verified_at' => '2024-01-15 11:30:00',
                'verified_by' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'technical_specs' => '{"power_rating": "27W", "data_transfer": "480Mbps", "length": "1m"}',
                'connector_info' => '{"input": "USB-C", "output": "Lightning"}',
                'compatibility_info' => '{"devices": ["iPhone", "iPad"]}'
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
                'markdown' => null,
                'summary' => '4K HDMI adapter for USB-C devices',
                'image' => null,
                'alt_text' => null,
                'keywords' => 'USB-C, HDMI, adapter, 4K',
                'name' => null,
                'dir' => null,
                'size' => null,
                'mime' => null,
                'is_published' => 1,
                'created' => '2024-01-16 09:30:00',
                'modified' => '2024-01-16 09:30:00',
                'published' => '2024-01-16 10:30:00',
                'meta_title' => 'USB-C to HDMI Adapter 4K 60Hz HDR',
                'meta_description' => '4K USB-C to HDMI adapter supporting 60Hz and HDR.',
                'meta_keywords' => 'USB-C HDMI adapter 4K 60Hz HDR video display',
                'facebook_description' => 'Transform your USB-C device with our 4K HDMI adapter.',
                'linkedin_description' => 'Professional USB-C to HDMI adapter.',
                'instagram_description' => '🖥️ 4K HDMI magic for your USB-C devices!',
                'twitter_description' => '4K@60Hz USB-C to HDMI adapter with HDR support.',
                'word_count' => 8,
                'parent_id' => 'prod-001-usb-c-cable',
                'lft' => 2,
                'rght' => 3,
                'main_menu' => 0,
                'view_count' => 89,
                'product_code' => 'BLK-AVC002',
                'manufacturer' => 'Belkin',
                'model_number' => 'AVC002btBK',
                'price' => 34.99,
                'currency' => 'USD',
                'availability_status' => 'in_stock',
                'stock_quantity' => 75,
                'reliability_score' => 4.20,
                'entry_input_type' => 'user_submission',
                'customer_peer_verification_count' => 3,
                'verification_status' => 'approved',
                'ai_analysis_score' => 3.8,
                'verification_notes' => null,
                'verified_at' => '2024-01-16 11:00:00',
                'verified_by' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'technical_specs' => '{"resolution": "4K@60Hz", "hdr_support": true, "dimensions": "50x20x10mm"}',
                'connector_info' => '{"input": "USB-C", "output": "HDMI"}',
                'compatibility_info' => '{"devices": ["MacBook", "iPad Pro", "Chromebook"]}'
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
                'markdown' => null,
                'summary' => 'Test product for development purposes',
                'image' => null,
                'alt_text' => null,
                'keywords' => 'test, development',
                'name' => null,
                'dir' => null,
                'size' => null,
                'mime' => null,
                'is_published' => 0,
                'created' => '2024-01-17 12:00:00',
                'modified' => '2024-01-17 12:00:00',
                'published' => null,
                'meta_title' => 'Test Product - Development Use Only',
                'meta_description' => 'Test product used for development and QA testing.',
                'meta_keywords' => 'test product development QA workflow',
                'facebook_description' => 'Development test product.',
                'linkedin_description' => 'Test product for development environments.',
                'instagram_description' => 'Test product for development purposes.',
                'twitter_description' => 'Test product for development workflows.',
                'word_count' => 10,
                'parent_id' => null,
                'lft' => 5,
                'rght' => 6,
                'main_menu' => 0,
                'view_count' => 0,
                'product_code' => 'TST-001',
                'manufacturer' => 'TestBrand',
                'model_number' => 'TEST-001',
                'price' => 9.99,
                'currency' => 'USD',
                'availability_status' => 'pre_order',
                'stock_quantity' => 0,
                'reliability_score' => 0.00,
                'entry_input_type' => 'developer',
                'customer_peer_verification_count' => 0,
                'verification_status' => 'pending',
                'ai_analysis_score' => null,
                'verification_notes' => null,
                'verified_at' => null,
                'verified_by' => null,
                'technical_specs' => null,
                'connector_info' => null,
                'compatibility_info' => null
            ]
        ];
        parent::init();
    }
}
