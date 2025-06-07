<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateQuestionOptions extends AbstractMigration
{
    public function change(): void
    {
        // Create the question_options table
        $table = $this->table('question_options');
        $table->addColumn('question_id', 'integer', [
            'null' => false,
            'limit' => 11,
            'comment' => 'Foreign key to the questions table',
        ])
            ->addColumn('option_text', 'string', [
                'limit' => 255,
                'null' => false,
                'comment' => 'Text of the option',
            ])
            ->addColumn('is_correct', 'boolean', [
                'default' => false,
                'null' => false,
                'comment' => 'Indicates whether the option is correct or not',
            ])
            ->addForeignKey('question_id', 'questions', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();
    }
}
