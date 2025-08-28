<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateQuestions extends AbstractMigration
{
    public function change(): void
    {
        // Create the questions table
        $table = $this->table('questions');
        $table->addColumn('question_order_id', 'integer', [
            'null' => false,
            'limit' => 11,
            'comment' => 'Order in which the question appears',
        ])
            ->addColumn('title', 'string', [
                'limit' => 255,
                'null' => false,
                'comment' => 'Title of the question',
            ])
            ->addColumn('description', 'text', [
                'null' => true,
                'comment' => 'Description or additional information about the question',
            ])
            ->create();
    }
}
