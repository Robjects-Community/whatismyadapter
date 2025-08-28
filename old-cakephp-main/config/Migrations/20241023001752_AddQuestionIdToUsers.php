<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddQuestionIdToUsers extends AbstractMigration
{
    public function change(): void
    {
        // Add a new column to the users table with a description for "user_ui_state"
        $table = $this->table('users');
        $table->addColumn('question_id', 'integer', [
            'null' => true, // Allow null if users aren't assigned a question yet
            'limit' => 11,
            'comment' => 'Tracks the user_ui_state, foreign key to the questions table',
        ])
            ->addForeignKey('question_id', 'questions', 'id', [
                'delete' => 'SET_NULL', // If a question is deleted, the user's question_id is set to NULL
                'update' => 'NO_ACTION',
            ])
            ->update(); // Apply the column addition and foreign key
    }
}
