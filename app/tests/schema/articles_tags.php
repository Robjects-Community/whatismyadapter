<?php
declare(strict_types=1);

use Cake\Database\Schema\TableSchema;

/**
 * Schema for articles_tags table
 * Auto-generated from database
 */
return function (TableSchema $table) {
    $table->addColumn('article_id', 'uuid', [
        'null' => false,
    ]);
    $table->addColumn('tag_id', 'uuid', [
        'null' => false,
    ]);

    $table->addConstraint('primary', [
        'type' => 'primary',
        'columns' => ['article_id', 'tag_id']
    ]);


    return $table;
};
