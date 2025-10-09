<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

/**
 * Add Homepage Feeds Settings Migration
 * 
 * Adds configuration settings for managing which feeds appear on the homepage
 * Backwards compatible with existing migrations and settings structure
 */
class AddHomepageFeedsSettings extends AbstractMigration
{
    /**
     * Change Method.
     * 
     * Adds homepage feed configuration settings to the existing settings table
     * 
     * @return void
     */
    public function change(): void
    {
        $this->table('settings')
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'HomepageFeeds',
                'key_name' => 'featuredArticlesEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Display featured articles section on the homepage. Featured articles are highlighted at the top of the page with larger layouts.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 2,
                'category' => 'HomepageFeeds',
                'key_name' => 'latestArticlesEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Display latest articles section on the homepage. Shows the most recent published articles in a grid layout.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 3,
                'category' => 'HomepageFeeds',
                'key_name' => 'latestProductsEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Display latest products section on the homepage. Shows the most recently added products with images and descriptions.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 4,
                'category' => 'HomepageFeeds',
                'key_name' => 'popularTagsEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Display popular tags widget in the sidebar. Shows tag cloud with the most used tags across articles.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 5,
                'category' => 'HomepageFeeds',
                'key_name' => 'socialLinksEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Display social links and connect section in the sidebar with links to author pages and social profiles.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 6,
                'category' => 'HomepageFeeds',
                'key_name' => 'developmentInfoEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Display development stack and server information widget in the sidebar showing technology stack details.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 7,
                'category' => 'HomepageFeeds',
                'key_name' => 'searchWidgetEnabled',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Display search widget in the sidebar. Currently disabled as search functionality is not yet implemented.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 8,
                'category' => 'HomepageFeeds',
                'key_name' => 'imageGalleriesEnabled',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Display image galleries section on the homepage. Shows recent image galleries if galleries feature is enabled.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 9,
                'category' => 'HomepageFeeds',
                'key_name' => 'userRegistrationWidgetEnabled',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Display user registration widget in the sidebar when user registration is enabled.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 10,
                'category' => 'HomepageFeeds',
                'key_name' => 'featuredArticlesLimit',
                'value' => '3',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Number of featured articles to display on the homepage (1-10).',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 11,
                'category' => 'HomepageFeeds',
                'key_name' => 'latestArticlesLimit',
                'value' => '6',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Number of latest articles to display on the homepage (1-20).',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 12,
                'category' => 'HomepageFeeds',
                'key_name' => 'latestProductsLimit',
                'value' => '4',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Number of latest products to display on the homepage (1-12).',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 13,
                'category' => 'HomepageFeeds',
                'key_name' => 'popularTagsLimit',
                'value' => '15',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Number of popular tags to display in the tags widget (1-50).',
                'data' => null,
                'column_width' => 2,
            ])
            ->save();
    }
}