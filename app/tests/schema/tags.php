<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for tags table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('title', 'string', [
        'length' => 191,
        'null' => true,
    ]);
    $table->addColumn('slug', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('image', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('dir', 'string', [
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
    $table->addColumn('size', 'integer', [
        'null' => true,
    ]);
    $table->addColumn('mime', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('name', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('meta_title', 'string', [
        'length' => 255,
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
    $table->addColumn('parent_id', 'uuid', [
        'null' => true,
    ]);
    $table->addColumn('main_menu', 'boolean', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('lft', 'integer', [
        'null' => false,
    ]);
    $table->addColumn('rght', 'integer', [
        'null' => false,
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
    $table->addColumn('created', 'datetime', [
        'null' => true,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);


    return $table;
};
