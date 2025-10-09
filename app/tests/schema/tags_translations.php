<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for tags_translations table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('locale', 'string', [
        'length' => 5,
        'null' => false,
        'fixed' => true,  // Indicates fixed-length string (CHAR equivalent)
    ]);
    $table->addColumn('title', 'string', [
        'length' => 255,
        'null' => true,
    ]);
    $table->addColumn('description', 'text', [
        'null' => true,
    ]);
    $table->addColumn('meta_title', 'text', [
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


    return $table;
};
