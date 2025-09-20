<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddRoleToUsers extends BaseMigration
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
        $table = $this->table('users');
        $table->addColumn('role', 'string', [
            'limit' => 32,
            'default' => 'user',
            'null' => false,
            'after' => 'is_admin',
            'comment' => 'User role: admin, editor, author, user'
        ]);
        $table->addIndex(['role']);
        $table->update();
    }
}
