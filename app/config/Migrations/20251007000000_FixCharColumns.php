<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class FixCharColumns extends BaseMigration
{
    /**
     * Fixes CHAR columns that were defined without proper length/fixed attributes.
     * These columns need to be properly defined as CHAR with fixed length for SQLite compatibility.
     *
     * @return void
     */
    public function up(): void
    {
        // Fix articles_translations.locale column
        $this->table('articles_translations')
            ->changeColumn('locale', 'char', [
                'limit' => 5,
                'null' => false,
            ])
            ->update();

        // Fix tags_translations.locale column
        $this->table('tags_translations')
            ->changeColumn('locale', 'char', [
                'limit' => 5,
                'null' => false,
            ])
            ->update();

        // Fix products.currency column
        $this->table('products')
            ->changeColumn('currency', 'char', [
                'limit' => 3,
                'null' => true,
                'default' => 'USD',
            ])
            ->update();

        // Fix products_purchase_links.price_currency column
        if ($this->hasTable('products_purchase_links')) {
            $this->table('products_purchase_links')
                ->changeColumn('price_currency', 'char', [
                    'limit' => 3,
                    'null' => true,
                ])
                ->update();
        }

        // Fix products_reliability_logs.checksum_sha256 column
        $this->table('products_reliability_logs')
            ->changeColumn('checksum_sha256', 'char', [
                'limit' => 64,
                'null' => false,
            ])
            ->update();
    }

    /**
     * @return void
     */
    public function down(): void
    {
        // Revert changes - change back to string without fixed attribute
        $this->table('articles_translations')
            ->changeColumn('locale', 'string', [
                'limit' => 5,
                'null' => false,
            ])
            ->update();

        $this->table('tags_translations')
            ->changeColumn('locale', 'string', [
                'limit' => 5,
                'null' => false,
            ])
            ->update();

        $this->table('products')
            ->changeColumn('currency', 'string', [
                'limit' => 3,
                'null' => true,
                'default' => 'USD',
            ])
            ->update();

        if ($this->hasTable('products_purchase_links')) {
            $this->table('products_purchase_links')
                ->changeColumn('price_currency', 'string', [
                    'limit' => 3,
                    'null' => true,
                ])
                ->update();
        }

        $this->table('products_reliability_logs')
            ->changeColumn('checksum_sha256', 'string', [
                'limit' => 64,
                'null' => false,
            ])
            ->update();
    }
}
