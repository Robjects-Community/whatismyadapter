<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for image_galleries_images table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('image_gallery_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('image_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('position', 'integer', [
        'null' => false,
        'default' => '0',
    ]);
    $table->addColumn('caption', 'text', [
        'null' => true,
    ]);
    $table->addColumn('created', 'datetime', [
        'null' => true,
    ]);
    $table->addColumn('modified', 'datetime', [
        'null' => true,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['id']
    ]);

    $table->addIndex('image_gallery_id', ['type' => 'index', 'columns' => ['image_gallery_id']]);
    $table->addIndex('image_id', ['type' => 'index', 'columns' => ['image_id']]);
    $table->addIndex('position', ['type' => 'index', 'columns' => ['position']]);

    return $table;
};
