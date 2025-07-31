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
     * Table name
     *
     * @var string
     */
    public string $table = 'product_affiliate_links';

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [];
        parent::init();
    }
}
