<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class V2 extends BaseMigration
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
        $this->table('connector_types', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('slug', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('display_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('connector_family', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('is_input', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('is_output', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('power_capability', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('data_capability', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('video_capability', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('audio_capability', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('max_power_watts', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('max_data_speed_gbps', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 8,
                'scale' => 3,
                'signed' => true,
            ])
            ->addColumn('icon', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('slug')
                    ->setName('idx_connector_slug')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('connector_family')
                    ->setName('idx_connector_family')
            )
            ->addIndex(
                $this->index([
                        'power_capability',
                        'data_capability',
                        'video_capability',
                        'audio_capability',
                    ])
                    ->setName('idx_connector_capabilities')
            )
            ->create();

        $this->table('product_affiliate_links', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('product_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('affiliate_network', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('merchant_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('affiliate_url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('price', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('currency', 'char', [
                'default' => 'USD',
                'limit' => 3,
                'null' => true,
            ])
            ->addColumn('commission_rate', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 5,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('is_primary', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('click_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('conversion_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('last_checked', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('availability_status', 'string', [
                'default' => 'unknown',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('idx_affiliate_product')
            )
            ->addIndex(
                $this->index('affiliate_network')
                    ->setName('idx_affiliate_network')
            )
            ->addIndex(
                $this->index('is_active')
                    ->setName('idx_affiliate_active')
            )
            ->addIndex(
                $this->index('is_primary')
                    ->setName('idx_affiliate_primary')
            )
            ->create();

        $this->table('product_categories', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('slug', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('parent_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('lft', 'integer', [
                'default' => '1',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('rght', 'integer', [
                'default' => '2',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('icon', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('color', 'string', [
                'default' => null,
                'limit' => 7,
                'null' => true,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('sort_order', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('slug')
                    ->setName('idx_category_slug')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('parent_id')
                    ->setName('idx_category_parent')
            )
            ->addIndex(
                $this->index([
                        'lft',
                        'rght',
                    ])
                    ->setName('idx_category_tree')
            )
            ->addIndex(
                $this->index('is_active')
                    ->setName('idx_category_active')
            )
            ->create();

        $this->table('product_connectors', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('product_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('connector_type_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('connector_role', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('connector_position', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('quantity', 'integer', [
                'default' => '1',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('power_rating_watts', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('data_speed_gbps', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 8,
                'scale' => 3,
                'signed' => true,
            ])
            ->addColumn('notes', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('idx_product_connectors_product')
            )
            ->addIndex(
                $this->index('connector_type_id')
                    ->setName('idx_product_connectors_type')
            )
            ->addIndex(
                $this->index('connector_role')
                    ->setName('idx_product_connectors_role')
            )
            ->create();

        $this->table('product_reviews', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('product_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('rating', 'boolean', [
                'comment' => 'Rating from 1 to 5',
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('review_text', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('verified_purchase', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_approved', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_featured', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('helpful_votes', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('unhelpful_votes', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('moderation_notes', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('idx_review_product')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_review_user')
            )
            ->addIndex(
                $this->index('is_approved')
                    ->setName('idx_review_approved')
            )
            ->addIndex(
                $this->index('rating')
                    ->setName('idx_review_rating')
            )
            ->addIndex(
                $this->index('is_featured')
                    ->setName('idx_review_featured')
            )
            ->create();

        $this->table('product_specifications', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('product_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('spec_category', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('spec_name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('spec_value', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('spec_unit', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('spec_type', 'string', [
                'default' => 'text',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('display_order', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('is_filterable', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_searchable', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('idx_spec_product')
            )
            ->addIndex(
                $this->index('spec_category')
                    ->setName('idx_spec_category')
            )
            ->addIndex(
                $this->index('spec_name')
                    ->setName('idx_spec_name')
            )
            ->addIndex(
                $this->index('is_filterable')
                    ->setName('idx_spec_filterable')
            )
            ->addIndex(
                $this->index('is_searchable')
                    ->setName('idx_spec_searchable')
            )
            ->create();

        $this->table('product_verifications', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('product_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('verifier_user_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('verification_type', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('verification_score', 'decimal', [
                'default' => null,
                'null' => false,
                'precision' => 3,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('verification_details', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('issues_found', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('suggestions', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('verification_notes', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('is_approved', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('idx_verification_product')
            )
            ->addIndex(
                $this->index('verifier_user_id')
                    ->setName('idx_verification_user')
            )
            ->addIndex(
                $this->index('verification_type')
                    ->setName('idx_verification_type')
            )
            ->addIndex(
                $this->index('verification_score')
                    ->setName('idx_verification_score')
            )
            ->create();

        $this->table('product_views', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('product_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('ip_address', 'string', [
                'default' => null,
                'limit' => 45,
                'null' => false,
            ])
            ->addColumn('user_agent', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('referer', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('idx_product_views_product')
            )
            ->addIndex(
                $this->index('created')
                    ->setName('idx_product_views_date')
            )
            ->create();

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
                'default' => 'adapter',
                'limit' => 20,
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
                'default' => '1',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('rght', 'integer', [
                'default' => '2',
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
                'comment' => 'Number of views for the product',
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('product_code', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
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
                'signed' => true,
            ])
            ->addColumn('currency', 'char', [
                'default' => 'USD',
                'limit' => 3,
                'null' => true,
            ])
            ->addColumn('availability_status', 'string', [
                'default' => 'in_stock',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('stock_quantity', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => true,
            ])
            ->addColumn('reliability_score', 'decimal', [
                'comment' => 'Reliability score from 0.00 to 5.00',
                'default' => '0.00',
                'null' => true,
                'precision' => 3,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('entry_input_type', 'string', [
                'default' => 'developer',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('customer_peer_verification_count', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('verification_status', 'string', [
                'default' => 'pending',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('ai_analysis_score', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 3,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('verification_notes', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('verified_at', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('verified_by', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('technical_specs', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('connector_info', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('compatibility_info', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('slug')
                    ->setName('idx_product_slug')
            )
            ->addIndex(
                $this->index('is_published')
                    ->setName('idx_product_published')
            )
            ->addIndex(
                $this->index('featured')
                    ->setName('idx_product_featured')
            )
            ->addIndex(
                $this->index('kind')
                    ->setName('idx_product_kind')
            )
            ->addIndex(
                $this->index('verification_status')
                    ->setName('idx_product_verification')
            )
            ->addIndex(
                $this->index('reliability_score')
                    ->setName('idx_product_reliability')
            )
            ->addIndex(
                $this->index('manufacturer')
                    ->setName('idx_product_manufacturer')
            )
            ->addIndex(
                $this->index('availability_status')
                    ->setName('idx_product_availability')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('idx_product_user')
            )
            ->addIndex(
                $this->index('product_code')
                    ->setName('idx_product_code')
            )
            ->addIndex(
                $this->index('parent_id')
                    ->setName('idx_product_parent')
            )
            ->addIndex(
                $this->index([
                        'lft',
                        'rght',
                    ])
                    ->setName('idx_product_tree')
            )
            ->addIndex(
                $this->index([
                        'verification_status',
                        'is_published',
                    ])
                    ->setName('idx_product_verification_status')
            )
            ->addIndex(
                $this->index([
                        'reliability_score',
                        'is_published',
                    ])
                    ->setName('idx_product_reliability_published')
            )
            ->addIndex(
                $this->index([
                        'created',
                        'is_published',
                    ])
                    ->setName('idx_product_created_published')
            )
            ->addIndex(
                $this->index([
                        'kind',
                        'is_published',
                        'reliability_score',
                    ])
                    ->setName('idx_product_category_lookup')
            )
            ->create();

        $this->table('products_categories', ['id' => false, 'primary_key' => ['product_id', 'category_id']])
            ->addColumn('product_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('category_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_primary', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('idx_products_categories_product')
            )
            ->addIndex(
                $this->index('category_id')
                    ->setName('idx_products_categories_category')
            )
            ->addIndex(
                $this->index('is_primary')
                    ->setName('idx_products_categories_primary')
            )
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
            ->addIndex(
                $this->index('product_id')
                    ->setName('idx_products_tags_product')
            )
            ->addIndex(
                $this->index('tag_id')
                    ->setName('idx_products_tags_tag')
            )
            ->create();

        $this->table('products_translations', ['id' => false, 'primary_key' => ['id', 'locale']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('locale', 'char', [
                'default' => null,
                'limit' => 5,
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('lede', 'string', [
                'default' => null,
                'limit' => 400,
                'null' => true,
            ])
            ->addColumn('body', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('summary', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('meta_title', 'text', [
                'default' => null,
                'limit' => null,
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
            ->addIndex(
                $this->index('locale')
                    ->setName('idx_product_translation_locale')
            )
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

        $this->table('connector_types')->drop()->save();
        $this->table('product_affiliate_links')->drop()->save();
        $this->table('product_categories')->drop()->save();
        $this->table('product_connectors')->drop()->save();
        $this->table('product_reviews')->drop()->save();
        $this->table('product_specifications')->drop()->save();
        $this->table('product_verifications')->drop()->save();
        $this->table('product_views')->drop()->save();
        $this->table('products')->drop()->save();
        $this->table('products_categories')->drop()->save();
        $this->table('products_tags')->drop()->save();
        $this->table('products_translations')->drop()->save();
    }
}
