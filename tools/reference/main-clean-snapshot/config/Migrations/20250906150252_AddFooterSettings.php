<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Cake\Utility\Text;

class AddFooterSettings extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        // Add footer_menu field to articles table
        $table = $this->table('articles');
        $table->addColumn('footer_menu', 'boolean', [
            'default' => false,
            'null' => false,
            'after' => 'main_menu'
        ]);
        $table->update();
        
        // Insert footer settings
        $this->table('settings')
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 4,
                'category' => 'SitePages',
                'key_name' => 'footerMenuShow',
                'value' => 'selected',
                'value_type' => 'select',
                'value_obscure' => false,
                'description' => 'Should the footer menu show all root pages or only selected pages?',
                'data' => json_encode([
                    'root' => 'Top Level Pages',
                    'selected' => 'Selected Pages'
                ]),
                'column_width' => 2
            ])
            ->save();
    }
}
