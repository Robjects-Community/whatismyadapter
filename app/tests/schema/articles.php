<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for articles table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('user_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('kind', 'string', [
        'length' => 10,
        'null' => false,
        'default' => 'article',
    ]);
    $table->addColumn('featured', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('title', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('lede', 'string', [
        'length' => 400,
        'null' => true,
    ]);
    $table->addColumn('slug', 'string', [
        'length' => 191,
        'null' => false,
    ]);
    $table->addColumn('body', 'text', [
        'null' => true,
    ]);
    $table->addColumn('markdown', 'text', [
        'null' => true,
    ]);
    $table->addColumn('summary', 'text', [
        'null' => true,
    ]);
    $table->addColumn('image', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('alt_text', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('keywords', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('name', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('dir', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('size', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('mime', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('is_published', 'boolean', [
        'null' => true,
        'default' => '0',
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('created_by', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('modified_by', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('published', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('meta_title', 'string', [
        'length' => 400,
        'null' => true,
    ]);
    $table->addColumn('meta_description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('meta_keywords', 'text', [
        'null' => true,
    ]);
    $table->addColumn('facebook_description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('linkedin_description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('instagram_description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('twitter_description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('word_count', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('parent_id', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('lft', 'integer', [
        'null' => false,
    ]);
    $table->addColumn('rght', 'integer', [
        'null' => false,
    ]);
    $table->addColumn('main_menu', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('footer_menu', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('view_count', 'integer', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('deployment_type', 'string', [
        'length' => 32,
        'null' => false,
        'default' => 'new-deployment',
    ]);
    $table->addColumn('external_url', 'string', [
        'length' => 2048,
        'null' => true,
    ]);
    $table->addColumn('deployment_meta', 'json', [
        'null' => true,
    ]);
    $table->addColumn('has_uploads', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);


    return $table;
};
