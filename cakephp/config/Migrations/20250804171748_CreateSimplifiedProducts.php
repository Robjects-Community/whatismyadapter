<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateSimplifiedProducts extends AbstractMigration
{
    public function change(): void
    {
        // Drop existing products table if it exists
        if ($this->hasTable('products')) {
            $this->table('products')->drop()->save();
        }
        
        // Create simplified products table
        $table = $this->table('products', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('article_id', 'uuid', [
                'default' => null,
                'null' => true,
                'comment' => 'Optional reference to detailed article'
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('slug', 'string', [
                'default' => null,
                'limit' => 191,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'null' => true,
                'comment' => 'Brief product description'
            ])
            ->addColumn('manufacturer', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('model_number', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('price', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 2,
            ])
            ->addColumn('currency', 'char', [
                'default' => 'USD',
                'limit' => 3,
                'null' => true,
            ])
            ->addColumn('image', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
                'comment' => 'Primary product image'
            ])
            ->addColumn('alt_text', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('is_published', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('featured', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('verification_status', 'string', [
                'default' => 'pending',
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('reliability_score', 'decimal', [
                'default' => '0.00',
                'null' => true,
                'precision' => 3,
                'scale' => 2,
            ])
            ->addColumn('view_count', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'null' => false,
            ])
            ->addIndex(['slug'], ['unique' => true, 'name' => 'idx_products_slug'])
            ->addIndex(['user_id'], ['name' => 'idx_products_user'])
            ->addIndex(['article_id'], ['name' => 'idx_products_article'])
            ->addIndex(['is_published'], ['name' => 'idx_products_published'])
            ->addIndex(['featured'], ['name' => 'idx_products_featured'])
            ->addIndex(['verification_status'], ['name' => 'idx_products_verification'])
            ->addIndex(['manufacturer'], ['name' => 'idx_products_manufacturer'])
            ->addIndex(['reliability_score'], ['name' => 'idx_products_reliability'])
            ->addIndex(['created'], ['name' => 'idx_products_created'])
            ->create();

        // Drop existing products_tags table if it exists
        if ($this->hasTable('products_tags')) {
            $this->table('products_tags')->drop()->save();
        }
        
        // Create products_tags junction table for unified tagging
        $tagsTable = $this->table('products_tags', [
            'id' => false,
            'primary_key' => ['product_id', 'tag_id'],
        ]);

        $tagsTable->addColumn('product_id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
            ->addColumn('tag_id', 'uuid', [
                'default' => null,
                'null' => false,
            ])
            ->addIndex(['product_id'], ['name' => 'idx_products_tags_product'])
            ->addIndex(['tag_id'], ['name' => 'idx_products_tags_tag'])
            ->create();
    }
}