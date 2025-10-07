<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for image_galleries table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('name', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('slug', 'string', [
        'length' => 255,
        'null' => false,
    ]);
    $table->addColumn('description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('preview_image', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('is_published', 'boolean', [
        'null' => false,
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

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('is_published', ['type' => 'index', 'columns' => ['is_published']]);
    $table->addIndex('created', ['type' => 'index', 'columns' => ['created']]);
    $table->addIndex('description', ['type' => 'fulltext', 'columns' => ['description']]);
    $table->addIndex('description_2', ['type' => 'fulltext', 'columns' => ['description']]);
    $table->addConstraint('slug', ['type' => 'unique', 'columns' => ['slug']]);

    return $table;
};
