<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsPurchaseLinksFixture
 */
class ProductsPurchaseLinksFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'products_purchase_links';

    /**
     * Table schema
     *
     * @var array
     */
    public array $fields = [
        'id' => [
            'type' => 'string',
            'length' => 36,
            'null' => false,
        ],
        'product_id' => [
            'type' => 'string',
            'length' => 36,
            'null' => false,
        ],
        'store_url' => [
            'type' => 'string',
            'length' => 255,
            'null' => true,
        ],
        'link_type' => [
            'type' => 'string',
            'length' => 50,
            'null' => true,
        ],
        'retailer_name' => [
            'type' => 'string',
            'length' => 255,
            'null' => true,
        ],
        'listed_price' => [
            'type' => 'decimal',
            'length' => 10,
            'precision' => 2,
            'null' => true,
        ],
        'price_currency' => [
            'type' => 'string',
            'length' => 3,
            'null' => true,
        ],
        'last_price_check' => [
            'type' => 'datetime',
            'null' => true,
        ],
        'link_status' => [
            'type' => 'string',
            'length' => 50,
            'null' => true,
        ],
        'affiliate_link' => [
            'type' => 'boolean',
            'null' => true,
        ],
        'created' => [
            'type' => 'datetime',
            'null' => false,
        ],
        'modified' => [
            'type' => 'datetime',
            'null' => false,
        ],
        '_constraints' => [
            'primary' => [
                'type' => 'primary',
                'columns' => ['id'],
            ],
        ],
    ];


    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => '12345678-1234-1234-1234-123456789012',
                'product_id' => 'f4ffbf46-8708-4e10-9293-2bd8446069b6',
                'store_url' => 'https://example.com/product/sample',
                'link_type' => 'retailer',
                'retailer_name' => 'Example Store',
                'listed_price' => 99.99,
                'price_currency' => 'USD',
                'last_price_check' => '2025-10-07 15:00:00',
                'link_status' => 'active',
                'affiliate_link' => false,
                'created' => '2025-10-07 15:00:00',
                'modified' => '2025-10-07 15:00:00',
            ],
        ];
        parent::init();
    }
}
