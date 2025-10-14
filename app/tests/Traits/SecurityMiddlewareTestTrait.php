<?php
declare(strict_types=1);

namespace App\Test\Traits;

use Cake\Core\Configure;

trait SecurityMiddlewareTestTrait
{
    /**
     * Enable security middleware for this test
     *
     * @return void
     */
    protected function enableSecurityMiddleware(): void
    {
        Configure::write('TestSecurity.enabled', true);
    }

    /**
     * Disable security middleware for this test
     *
     * @return void
     */
    protected function disableSecurityMiddleware(): void
    {
        Configure::write('TestSecurity.enabled', false);
    }
}
