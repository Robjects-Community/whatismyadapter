<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Cake\ORM\TableRegistry;
use Migrations\AbstractMigration;

class Newslugstable extends AbstractMigration
{
/**
     * Change Method.
     *
     * @return void
     */
    public function up(): void
    {
        // First, add the new 'model' column
        $this->table('slugs')
            ->addColumn('model', 'string', [
                'limit' => 20,
                'null' => true, // Temporarily allow null
                'after' => 'id',
            ])
            ->update();

        // Add the new foreign_key column
        $this->table('slugs')
            ->addColumn('foreign_key', 'uuid', [
                'null' => true, // Temporarily allow null
                'after' => 'model',
            ])
            ->update();

        // Update existing records to set model and foreign_key
        $this->execute("UPDATE slugs SET model = 'Articles', foreign_key = article_id");

        // Now make the new columns required
        $this->table('slugs')
            ->changeColumn('model', 'string', [
                'limit' => 20,
                'null' => false,
            ])
            ->changeColumn('foreign_key', 'uuid', [
                'null' => false,
            ])
            ->update();

        // Add indexes for the new structure
        $this->table('slugs')
            ->addIndex(['model', 'slug'], [
                'name' => 'idx_slugs_lookup',
            ])
            ->addIndex(['model', 'foreign_key'], [
                'name' => 'idx_slugs_foreign',
            ])
            ->update();

        // Remove the modified column as it's no longer needed
        $this->table('slugs')
            ->removeColumn('modified')
            ->update();

        // Finally, remove the old article_id column
        $this->table('slugs')
            ->removeColumn('article_id')
            ->update();

        // Migrate existing tag slugs to the slugs table
        $connection = $this->getAdapter()->getConnection();
    
        $tags = $connection->execute("
            SELECT id, slug, DATE_FORMAT(created, '%Y-%m-%d %H:%i:%s') as created 
            FROM tags 
            WHERE slug IS NOT NULL 
            AND slug != ''
        ")->fetchAll('assoc');

        foreach ($tags as $tag) {
            $this->table('slugs')
                ->insert([
                    'id' => Text::uuid(),
                    'foreign_key' => $tag['id'],
                    'model' => 'Tags',
                    'slug' => $tag['slug'],
                    'created' => $tag['created'],
                ])
                ->save();
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        // Add back the article_id column
        $this->table('slugs')
            ->addColumn('article_id', 'uuid', [
                'null' => true,
                'after' => 'id',
            ])
            ->update();

        // Restore data from foreign_key to article_id where model is 'Articles'
        $this->execute("UPDATE slugs SET article_id = foreign_key WHERE model = 'Articles'");

        // Make article_id required again
        $this->table('slugs')
            ->changeColumn('article_id', 'uuid', [
                'null' => false,
            ])
            ->update();

        // Add back the modified column
        $this->table('slugs')
            ->addColumn('modified', 'datetime', [
                'null' => true,
            ])
            ->update();

        // Remove the new polymorphic columns and indexes
        $this->table('slugs')
            ->removeIndex(['model', 'slug'])
            ->removeIndex(['model', 'foreign_key'])
            ->removeColumn('model')
            ->removeColumn('foreign_key')
            ->update();
    }
}
