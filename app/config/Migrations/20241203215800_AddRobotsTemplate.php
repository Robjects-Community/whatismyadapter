<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

class AddRobotsTemplate extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $robotsTemplate = <<<EOT
User-agent: *
Allow: /{LANG}/
Allow: /{LANG}/articles/*
Allow: /{LANG}/pages/*
Allow: /{LANG}/sitemap.xml

Disallow: /admin/
Disallow: /{LANG}/users/login
Disallow: /{LANG}/users/register
Disallow: /{LANG}/users/forgot-password
Disallow: /{LANG}/users/reset-password/*
Disallow: /{LANG}/users/confirm-email/*
Disallow: /{LANG}/users/edit/*
Disallow: /{LANG}/cookie-consents/edit

# Prevent indexing of non-existent listing pages
Disallow: /{LANG}/articles$
Disallow: /{LANG}/pages$

Sitemap: /{LANG}/sitemap.xml
EOT;

        $this->table('settings')
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 4,
                'category' => 'SEO',
                'key_name' => 'robots',
                'value' => $robotsTemplate,
                'value_type' => 'textarea',
                'value_obscure' => false,
                'description' => 'The template for robots.txt file. Use {LANG} as a placeholder for the language code. This template will be used to generate the robots.txt file content.',
                'data' => null,
                'column_width' => 4,
            ])
            ->save();
    }
}
