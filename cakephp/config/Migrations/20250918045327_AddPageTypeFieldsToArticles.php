<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPageTypeFieldsToArticles extends BaseMigration
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
        $table = $this->table('articles');
        
        // Add page type field (external_url already exists)
        $table->addColumn('page_type', 'string', [
            'limit' => 20,
            'null' => false,
            'default' => 'standard',
            'comment' => 'Type of page: standard or linked',
            'after' => 'kind'
        ]);
        
        $table->addColumn('static_content', 'text', [
            'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM,
            'null' => true,
            'comment' => 'Static content for linked pages',
            'after' => 'body'
        ]);
        
        $table->addColumn('assets_json', 'json', [
            'null' => true,
            'comment' => 'JSON metadata for uploaded page assets',
            'after' => 'static_content'
        ]);
        
        $table->addColumn('asset_dir', 'string', [
            'limit' => 255,
            'null' => true,
            'comment' => 'Directory path for page assets under webroot/uploads/pages/',
            'after' => 'assets_json'
        ]);
        
        // Add indexes
        $table->addIndex(['page_type'], ['name' => 'idx_articles_page_type']);
        // external_url index might already exist, we'll check if we can add it
        
        $table->update();
    }
}
