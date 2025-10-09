<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class InitialContactManager extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     *
     * @return void
     */
    public function up(): void
    {
        $this->table('contacts', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('first_name', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('last_name', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('contact_num', 'string', [
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('address', 'text', [
                'null' => false,
            ])
            ->addColumn('city', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('state', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('country', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addTimestamps('created', 'modified')
            ->create();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     *
     * @return void
     */
    public function down(): void
    {
        $this->table('contacts')->drop()->save();
    }
}
