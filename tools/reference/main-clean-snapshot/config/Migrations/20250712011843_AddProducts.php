<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddProducts extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {
        // Check if products table already exists (defensive programming)
        if ($this->hasTable('products')) {
            // Table already exists - likely from CreateSimplifiedProducts migration
            // Skip creation to avoid conflicts
            return;
        }
        
        $this->table('products', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('kind', 'string', [
                'default' => 'article',
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('featured', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('lede', 'string', [
                'default' => null,
                'limit' => 400,
                'null' => true,
            ])
            ->addColumn('slug', 'string', [
                'default' => null,
                'limit' => 191,
                'null' => false,
            ])
            ->addColumn('body', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('markdown', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('summary', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('image', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('alt_text', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('keywords', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('dir', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('size', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => true,
            ])
            ->addColumn('mime', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('is_published', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('published', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('meta_title', 'string', [
                'default' => null,
                'limit' => 400,
                'null' => true,
            ])
            ->addColumn('meta_description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('meta_keywords', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('facebook_description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('linkedin_description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('instagram_description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('twitter_description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('word_count', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => true,
            ])
            ->addColumn('parent_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('lft', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('rght', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('main_menu', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('view_count', 'integer', [
                'comment' => 'Number of views for the article',
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->create();

        $this->table('products_tags', ['id' => false, 'primary_key' => ['product_id', 'tag_id']])
            ->addColumn('product_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('tag_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->create();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down(): void
    {
        // DO NOT drop products table in down() method to preserve production schema
        // The current products table was created by CreateSimplifiedProducts migration
        // and contains important data that should not be lost
        
        // Defensive check: only attempt to drop if this migration actually created the tables
        // Since we now skip creation in up(), we should not drop anything here
        
        // Leaving this method empty to preserve database integrity
    }
}
