<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

/**
 * UsersSeed seed.
 */
class UsersSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run(): void
    {
        $data = [
            'name' => 'Umer',
            'email' => 'umer936@gmail.com',
            'password' => 'Password',
        ];

        $table = $this->table('users');
        $table->insert($data)->save();
    }
}
