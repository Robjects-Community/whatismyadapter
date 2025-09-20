# Create the CakePHP 5.x integration guide with all necessary files

# 1. Database Migration for CakePHP
migration_file = """<?php
declare(strict_types=1);

use Migrations\\AbstractMigration;

class CreateAdapterQuizTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     */
    public function change(): void
    {
        // Products table
        $table = $this->table('products');
        $table->addColumn('title', 'string', ['limit' => 255])
              ->addColumn('manufacturer', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('port_type', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('form_factor', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('device_gender', 'string', ['limit' => 20, 'null' => true])
              ->addColumn('device_cat', 'string', ['limit' => 100, 'null' => true])
              ->addColumn('device_compatibility', 'text', ['null' => true])
              ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
              ->addColumn('rating', 'decimal', ['precision' => 3, 'scale' => 2, 'null' => true])
              ->addColumn('certified', 'boolean', ['default' => false])
              ->addColumn('status', 'string', ['limit' => 20, 'default' => 'pending'])
              ->addColumn('rel_score', 'decimal', ['precision' => 5, 'scale' => 4, 'null' => true])
              ->addColumn('views', 'integer', ['default' => 0])
              ->addColumn('image_url', 'string', ['limit' => 500, 'null' => true])
              ->addColumn('features', 'json', ['null' => true])
              ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['manufacturer'])
              ->addIndex(['port_type'])
              ->addIndex(['device_cat'])
              ->addIndex(['price'])
              ->addIndex(['rating'])
              ->addIndex(['status'])
              ->create();

        // Quiz Submissions table
        $table = $this->table('quiz_submissions');
        $table->addColumn('user_id', 'integer', ['null' => true])
              ->addColumn('session_id', 'string', ['limit' => 50, 'null' => true])
              ->addColumn('quiz_type', 'string', ['limit' => 20, 'default' => 'comprehensive'])
              ->addColumn('quiz_data', 'json')
              ->addColumn('recommendations', 'json', ['null' => true])
              ->addColumn('selected_product_id', 'integer', ['null' => true])
              ->addColumn('confidence_score', 'decimal', ['precision' => 5, 'scale' => 4, 'null' => true])
              ->addColumn('ip_address', 'string', ['limit' => 45, 'null' => true])
              ->addColumn('user_agent', 'text', ['null' => true])
              ->addColumn('completed', 'boolean', ['default' => false])
              ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['user_id'])
              ->addIndex(['session_id'])
              ->addIndex(['created'])
              ->addIndex(['selected_product_id'])
              ->create();

        // Quiz Questions table (for dynamic questions)
        $table = $this->table('quiz_questions');
        $table->addColumn('question_id', 'string', ['limit' => 50])
              ->addColumn('quiz_type', 'string', ['limit' => 20])
              ->addColumn('question_text', 'text')
              ->addColumn('question_type', 'string', ['limit' => 50])
              ->addColumn('options', 'json', ['null' => true])
              ->addColumn('weight', 'integer', ['default' => 1])
              ->addColumn('filters', 'json', ['null' => true])
              ->addColumn('dependencies', 'json', ['null' => true])
              ->addColumn('active', 'boolean', ['default' => true])
              ->addColumn('created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('modified', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
              ->addIndex(['quiz_type'])
              ->addIndex(['question_id'])
              ->addIndex(['active'])
              ->create();
    }
}
?>"""

# Save migration file
with open('CreateAdapterQuizTables.php', 'w') as f:
    f.write(migration_file)

print("✅ CakePHP Migration created!")
print("📄 File: CreateAdapterQuizTables.php")
print("🗄️ Creates 3 tables: products, quiz_submissions, quiz_questions")