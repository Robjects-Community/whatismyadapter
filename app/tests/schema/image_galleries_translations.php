<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for image_galleries_translations table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('locale', 'string', [
        'length' => 5,
        'null' => false,
    ]);
    $table->addColumn('name', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('description', 'text', [
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
        'columns' => ['id', 'locale']
    ]);

    $table->addIndex('id', ['type' => 'index', 'columns' => ['id']]);
    $table->addIndex('locale', ['type' => 'index', 'columns' => ['locale']]);

    return $table;
};
