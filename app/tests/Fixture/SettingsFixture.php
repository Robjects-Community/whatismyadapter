<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Cake\Utility\Text;

/**
 * SettingsFixture
 */
class SettingsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            // Existing ImageSizes settings
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'massive',
                'value' => '800',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'extra-large',
                'value' => '500',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'large',
                'value' => '400',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'medium',
                'value' => '300',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'small',
                'value' => '200',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'tiny',
                'value' => '100',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'teeny',
                'value' => '50',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'micro',
                'value' => '10',
                'value_type' => 'numeric',
            ],

            // Email settings
            [
                'id' => Text::uuid(),
                'category' => 'Email',
                'key_name' => 'reply_email',
                'value' => 'noreply@example.com',
                'value_type' => 'text',
            ],

            // SEO settings
            [
                'id' => Text::uuid(),
                'category' => 'SEO',
                'key_name' => 'siteStrapline',
                'value' => 'Welcome to Willow CMS',
                'value_type' => 'text',
            ],

            // AI settings
            [
                'id' => Text::uuid(),
                'category' => 'AI',
                'key_name' => 'anthropicApiKey',
                'value' => 'your-api-key-here',
                'value_type' => 'text',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'AI',
                'key_name' => 'enabled',
                'value' => '0',
                'value_type' => 'bool',
            ],

            // Comments settings
            [
                'id' => Text::uuid(),
                'category' => 'Comments',
                'key_name' => 'articlesEnabled',
                'value' => '1',
                'value_type' => 'bool',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Comments',
                'key_name' => 'pagesEnabled',
                'value' => '1',
                'value_type' => 'bool',
            ],

            // Users settings
            [
                'id' => Text::uuid(),
                'category' => 'Users',
                'key_name' => 'registrationEnabled',
                'value' => '1',
                'value_type' => 'bool',
            ],

            // Security settings
            [
                'id' => Text::uuid(),
                'category' => 'Security',
                'key_name' => 'blockOnNoIp',
                'value' => '1',
                'value_type' => 'bool',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Security',
                'key_name' => 'trustProxy',
                'value' => '0',
                'value_type' => 'bool',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Security',
                'key_name' => 'trustedProxies',
                'value' => '',
                'value_type' => 'text',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Security',
                'key_name' => 'enableRateLimiting',
                'value' => '1',
                'value_type' => 'bool',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Security',
                'key_name' => 'suspiciousRequestThreshold',
                'value' => '3',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Security',
                'key_name' => 'suspiciousWindowHours',
                'value' => '24',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Security',
                'key_name' => 'suspiciousBlockHours',
                'value' => '24',
                'value_type' => 'numeric',
            ],

            // Rate limit settings - Global
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'numberOfRequests',
                'value' => '500',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'numberOfSeconds',
                'value' => '60',
                'value_type' => 'numeric',
            ],

            // Rate limit settings - Login
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'loginNumberOfRequests',
                'value' => '5',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'loginNumberOfSeconds',
                'value' => '60',
                'value_type' => 'numeric',
            ],

            // Rate limit settings - Admin
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'adminNumberOfRequests',
                'value' => '10',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'adminNumberOfSeconds',
                'value' => '60',
                'value_type' => 'numeric',
            ],

            // Rate limit settings - Password Reset
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'passwordResetNumberOfRequests',
                'value' => '3',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'passwordResetNumberOfSeconds',
                'value' => '300',
                'value_type' => 'numeric',
            ],

            // Rate limit settings - Register
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'registerNumberOfRequests',
                'value' => '5',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'registerNumberOfSeconds',
                'value' => '300',
                'value_type' => 'numeric',
            ],

            // Rate limit settings - API
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'apiNumberOfRequests',
                'value' => '1000',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'RateLimit',
                'key_name' => 'apiNumberOfSeconds',
                'value' => '3600',
                'value_type' => 'numeric',
            ],
        ];
        parent::init();
    }
}
