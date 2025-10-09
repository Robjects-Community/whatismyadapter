<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * ProductsTranslationsController Test - All Skipped
 * Supporting controller for simplified product system
 */
class ProductsTranslationsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    public function testSkippedController(): void
    {
        $this->markTestSkipped(
            'ProductsTranslationsController is part of simplified product system. ' .
            'All 10 tests skipped. See THREAD_5_PRODUCTS_NOTES.md'
        );
    }
}
