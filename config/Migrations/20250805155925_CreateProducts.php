<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateProducts extends AbstractMigration
{
     public function up(): void
    {
        // Create simplified products table
        $table = $this->table('products', [
            'id' => false,
            'primary_key' => ['id'],
        ]);

        //
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
            ]);

            
            // TODO: Additional fields for product details pertaining to USB cables - NEED TO INCORPORATE IN SIMILAR PATTERN TO SEO FIELDS
          $table->addColumn('connector_type_a', 'string', ['limit' => 50, 'null' => true, 'after' => 'model_number'])->addColumn('connector_type_b', 'string', ['limit' => 50, 'null' => true])
          ->addColumn('supports_usb_pd', 'boolean', ['default' => false])
          ->addColumn('max_power_delivery', 'string', ['limit' => 50, 'null' => true])
          ->addColumn('usb_version', 'string', ['limit' => 20, 'null' => true])
          ->addColumn('supports_displayport', 'boolean', ['default' => false])
          ->addColumn('supports_hdmi', 'boolean', ['default' => false])
          ->addColumn('supports_alt_mode', 'boolean', ['default' => false])
          ->addColumn('supports_thunderbolt', 'boolean', ['default' => false])
          ->addColumn('supports_quick_charge', 'boolean', ['default' => false])
          ->addColumn('supports_audio', 'boolean', ['default' => false])
          ->addColumn('cable_length', 'string', ['limit' => 20, 'null' => true])
          ->addColumn('wire_gauge', 'string', ['limit' => 20, 'null' => true])
          ->addColumn('shielding_type', 'string', ['limit' => 50, 'null' => true])
          ->addColumn('is_active_cable', 'boolean', ['default' => false])
          ->addColumn('category_rating', 'string', ['limit' => 50, 'null' => true]) // e.g., Standard, Premium
          ->addColumn('shopping_link', 'string', ['limit' => 255, 'null' => true]) // Amazon/online URL
          ->addColumn('notes', 'text', ['null' => true]);

        $table->addColumn('created_by', 'uuid', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified_by', 'uuid', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('meta_title', 'string', [
                'default' => null,
                'limit' => 255, 
                'null' => true,
            ])
            ->addColumn('meta_description', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('meta_keywords', 'text', [
                'default' => null,
                'null' => true,
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

            $productsTagsTable = $this->table('products_tags', [
            'id' => false,
            'primary_key' => ['product_id', 'tag_id'],
        ]);

        $productsTagsTable->addColumn('product_id', 'uuid', [
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



        //     //// start of helper database views
        //     $this->table('v_featured_products', ['id' => false])
        //     ->addColumn('id', 'uuid', [
        //         'default' => null,
        //         'limit' => null,
        //         'null' => false,
        //     ])
        //     ->addColumn('title', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => false,
        //     ])
        //     ->addColumn('slug', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 191,
        //         'null' => false,
        //     ])
        //     ->addColumn('description', 'text', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'comment' => 'Brief product description',
        //         'default' => null,
        //         'limit' => null,
        //         'null' => true,
        //     ])
        //     ->addColumn('manufacturer', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('price', 'decimal', [
        //         'default' => null,
        //         'null' => true,
        //         'precision' => 10,
        //         'scale' => 2,
        //         'signed' => true,
        //     ])
        //     ->addColumn('currency', 'char', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => 'USD',
        //         'limit' => 3,
        //         'null' => true,
        //     ])
        //     ->addColumn('image', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'comment' => 'Primary product image',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('alt_text', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('reliability_score', 'decimal', [
        //         'default' => '0.00',
        //         'null' => true,
        //         'precision' => 3,
        //         'scale' => 2,
        //         'signed' => true,
        //     ])
        //     ->addColumn('view_count', 'integer', [
        //         'default' => '0',
        //         'limit' => null,
        //         'null' => false,
        //         'signed' => true,
        //     ])
        //     ->addColumn('creator', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->create();

        // $this->table('v_products_with_tags', ['id' => false])
        //     ->addColumn('id', 'uuid', [
        //         'default' => null,
        //         'limit' => null,
        //         'null' => false,
        //     ])
        //     ->addColumn('title', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => false,
        //     ])
        //     ->addColumn('slug', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 191,
        //         'null' => false,
        //     ])
        //     ->addColumn('description', 'text', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'comment' => 'Brief product description',
        //         'default' => null,
        //         'limit' => null,
        //         'null' => true,
        //     ])
        //     ->addColumn('manufacturer', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('model_number', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('price', 'decimal', [
        //         'default' => null,
        //         'null' => true,
        //         'precision' => 10,
        //         'scale' => 2,
        //         'signed' => true,
        //     ])
        //     ->addColumn('currency', 'char', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => 'USD',
        //         'limit' => 3,
        //         'null' => true,
        //     ])
        //     ->addColumn('image', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'comment' => 'Primary product image',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('is_published', 'boolean', [
        //         'default' => false,
        //         'limit' => null,
        //         'null' => false,
        //     ])
        //     ->addColumn('featured', 'boolean', [
        //         'default' => false,
        //         'limit' => null,
        //         'null' => false,
        //     ])
        //     ->addColumn('verification_status', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => 'pending',
        //         'limit' => 20,
        //         'null' => false,
        //     ])
        //     ->addColumn('reliability_score', 'decimal', [
        //         'default' => '0.00',
        //         'null' => true,
        //         'precision' => 3,
        //         'scale' => 2,
        //         'signed' => true,
        //     ])
        //     ->addColumn('view_count', 'integer', [
        //         'default' => '0',
        //         'limit' => null,
        //         'null' => false,
        //         'signed' => true,
        //     ])
        //     ->addColumn('created', 'datetime', [
        //         'default' => null,
        //         'limit' => null,
        //         'null' => false,
        //     ])
        //     ->addColumn('tag_names', 'text', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => null,
        //         'null' => true,
        //     ])
        //     ->addColumn('tag_slugs', 'text', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => null,
        //         'null' => true,
        //     ])
        //     ->addColumn('tag_ids', 'text', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => null,
        //         'null' => true,
        //     ])
        //     ->addColumn('tag_count', 'biginteger', [
        //         'default' => '0',
        //         'limit' => null,
        //         'null' => false,
        //         'signed' => true,
        //     ])
        //     ->create();

        // $this->table('v_published_products', ['id' => false])
        //     ->addColumn('id', 'uuid', [
        //         'default' => null,
        //         'limit' => null,
        //         'null' => false,
        //     ])
        //     ->addColumn('title', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => false,
        //     ])
        //     ->addColumn('slug', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 191,
        //         'null' => false,
        //     ])
        //     ->addColumn('description', 'text', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'comment' => 'Brief product description',
        //         'default' => null,
        //         'limit' => null,
        //         'null' => true,
        //     ])
        //     ->addColumn('manufacturer', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('model_number', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('price', 'decimal', [
        //         'default' => null,
        //         'null' => true,
        //         'precision' => 10,
        //         'scale' => 2,
        //         'signed' => true,
        //     ])
        //     ->addColumn('currency', 'char', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => 'USD',
        //         'limit' => 3,
        //         'null' => true,
        //     ])
        //     ->addColumn('image', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'comment' => 'Primary product image',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('alt_text', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('featured', 'boolean', [
        //         'default' => false,
        //         'limit' => null,
        //         'null' => false,
        //     ])
        //     ->addColumn('verification_status', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => 'pending',
        //         'limit' => 20,
        //         'null' => false,
        //     ])
        //     ->addColumn('reliability_score', 'decimal', [
        //         'default' => '0.00',
        //         'null' => true,
        //         'precision' => 3,
        //         'scale' => 2,
        //         'signed' => true,
        //     ])
        //     ->addColumn('view_count', 'integer', [
        //         'default' => '0',
        //         'limit' => null,
        //         'null' => false,
        //         'signed' => true,
        //     ])
        //     ->addColumn('created', 'datetime', [
        //         'default' => null,
        //         'limit' => null,
        //         'null' => false,
        //     ])
        //     ->addColumn('modified', 'datetime', [
        //         'default' => null,
        //         'limit' => null,
        //         'null' => false,
        //     ])
        //     ->addColumn('creator_username', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('creator_email', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => false,
        //     ])
        //     ->addColumn('creator_name', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('article_title', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 255,
        //         'null' => true,
        //     ])
        //     ->addColumn('article_slug', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 191,
        //         'null' => true,
        //     ])
        //     ->addColumn('article_lede', 'string', [
        //         'collation' => 'utf8mb4_unicode_ci',
        //         'default' => null,
        //         'limit' => 400,
        //         'null' => true,
        //     ])
        //     ->create();


    
    }


        public function down(): void
        {
            $this->table('products')->drop()->save();
            $this->table('products_tags')->drop()->save();
            

        }
        
    }

