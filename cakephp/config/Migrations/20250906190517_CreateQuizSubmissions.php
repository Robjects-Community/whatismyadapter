<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateQuizSubmissions extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('quiz_submissions', [
            'id' => false,
            'primary_key' => ['id'],
        ]);
        
        $table
            ->addColumn('id', 'char', ['limit' => 36])
            ->addColumn('user_id', 'char', ['limit' => 36, 'null' => true])
            ->addColumn('session_id', 'string', ['limit' => 64])
            ->addColumn('quiz_type', 'string', [
                'limit' => 20,
                'default' => 'comprehensive'
            ])
            ->addColumn('answers', 'json')
            ->addColumn('matched_product_ids', 'json', ['null' => true])
            ->addColumn('confidence_scores', 'json', ['null' => true])
            ->addColumn('result_summary', 'text', ['null' => true])
            ->addColumn('analytics', 'json', ['null' => true])
            ->addColumn('created', 'datetime')
            ->addColumn('modified', 'datetime')
            ->addColumn('created_by', 'char', ['limit' => 36, 'null' => true])
            ->addColumn('modified_by', 'char', ['limit' => 36, 'null' => true])
            ->addIndex(['session_id'])
            ->addIndex(['quiz_type'])
            ->addIndex(['created'])
            ->addIndex(['user_id'])
            ->create();
    }
}
