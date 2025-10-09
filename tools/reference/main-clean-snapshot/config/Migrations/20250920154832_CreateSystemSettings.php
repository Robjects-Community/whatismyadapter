<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateSystemSettings extends BaseMigration
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
        $table = $this->table('system_settings');
        
        // Add columns
        $table->addColumn('namespace', 'string', [
            'limit' => 100,
            'null' => false,
            'comment' => 'Settings namespace (e.g., quiz, forms)'
        ]);
        
        $table->addColumn('setting_key', 'string', [
            'limit' => 255,
            'null' => false,
            'comment' => 'Setting key within namespace'
        ]);
        
        $table->addColumn('value_json', 'json', [
            'null' => false,
            'comment' => 'Setting value stored as JSON'
        ]);
        
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
            'comment' => 'Record creation timestamp'
        ]);
        
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
            'comment' => 'Record modification timestamp'
        ]);
        
        // Add indexes
        $table->addIndex(['namespace'], ['name' => 'idx_namespace']);
        $table->addIndex(['namespace', 'setting_key'], [
            'unique' => true,
            'name' => 'uniq_namespace_key'
        ]);
        
        $table->create();
        
        // Add seed data with default quiz settings from config
        $this->execute("
            INSERT INTO system_settings (namespace, setting_key, value_json, created, modified) VALUES
            ('quiz', 'enabled', 'true', NOW(), NOW()),
            ('quiz', 'max_results', '10', NOW(), NOW()),
            ('quiz', 'confidence_threshold', '0.3', NOW(), NOW()),
            ('quiz', 'akinator', '{\"enabled\": true, \"max_questions\": 20}', NOW(), NOW()),
            ('quiz', 'comprehensive', '{\"enabled\": true, \"steps\": [\"basic_info\", \"technical_specs\", \"use_case\"]}', NOW(), NOW()),
            ('quiz', 'result', '{\"display\": {\"show_confidence\": true, \"show_specs\": true, \"layout\": \"list\"}}', NOW(), NOW()),
            ('quiz', 'ai', '{\"method\": \"hybrid\", \"semantic\": {\"provider\": \"openai\", \"model\": \"text-embedding-3-small\"}, \"temperature\": 0.7}', NOW(), NOW())
        ");
    }
}
