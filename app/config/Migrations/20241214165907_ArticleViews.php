<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Phinx\Db\Table\Column;

class ArticleViews extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        // Get the articles table
        $table = $this->table('articles');

        // Add the view_count column
        $table->addColumn('view_count', 'integer', [
            'default' => 0,
            'null' => false,
            'comment' => 'Number of views for the article'
        ]);

        // Update the table with the new column
        $table->update();
    }
}
