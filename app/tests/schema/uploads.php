<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for uploads table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'integer', [
        'null' => false,
    ]);
    $table->addColumn('article_id', 'integer', [
        'null' => false,
    ]);
    $table->addColumn('original_name', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('basename', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('ext', 'string', [
        'length' => 10,
        'null' => false,
    ]);
    $table->addColumn('mime', 'string', [
        'length' => 100,
        'null' => false,
    ]);
    $table->addColumn('size_bytes', 'biginteger', [
        'null' => false,
    ]);
    $table->addColumn('disk', 'string', [
        'length' => 50,
        'null' => false,
        'default' => 'local',
    ]);
    $table->addColumn('directory', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('url', 'string', [
        'length' => 512,
        'null' => false,
    ]);
    $table->addColumn('checksum_sha256', 'string', [
        'length' => 64,
        'null' => false,
    ]);
    $table->addColumn('asset_type', 'string', [
        'length' => 20,
        'null' => false,
    ]);
    $table->addColumn('meta', 'json', [
        'null' => true,
    ]);
    $table->addColumn('created', 'timestamp', [
        'null' => false,
        'default' => 'CURRENT_TIMESTAMP',
    ]);
    $table->addColumn('updated', 'timestamp', [
        'null' => true,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('idx_uploads_article_id', ['type' => 'index', 'columns' => ['article_id']]);
    $table->addIndex('idx_uploads_asset_type', ['type' => 'index', 'columns' => ['asset_type']]);
    $table->addIndex('idx_uploads_checksum', ['type' => 'index', 'columns' => ['checksum_sha256']]);

    return $table;
};
