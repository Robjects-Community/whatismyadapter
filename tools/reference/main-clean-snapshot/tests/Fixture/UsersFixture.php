<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Table schema
     *
     * @var array
     */
    protected array $schema = [
        'id' => ['type' => 'uuid', 'null' => false],
        'email' => ['type' => 'string', 'length' => 255, 'null' => false],
        'username' => ['type' => 'string', 'length' => 255, 'null' => false],
        'password' => ['type' => 'string', 'length' => 255, 'null' => false],
        'is_admin' => ['type' => 'boolean', 'null' => false, 'default' => false],
        'active' => ['type' => 'boolean', 'null' => false, 'default' => true],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f', // Admin user expected by tests
                'email' => 'admin@test.com',
                'username' => 'admin',
                'password' => '$2y$10$abcdefghijklmnopqrstuv', // dummy hash
                'is_admin' => 1,
                'active' => 1,
                'created' => '2025-07-12 21:49:10',
                'modified' => '2025-07-12 21:49:10',
            ],
            [
                'id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02', // Non-admin user expected by tests
                'email' => 'user@test.com',
                'username' => 'user',
                'password' => '$2y$10$abcdefghijklmnopqrstuv', // dummy hash
                'is_admin' => 0,
                'active' => 1,
                'created' => '2025-07-12 21:49:10',
                'modified' => '2025-07-12 21:49:10',
            ],
        ];
        parent::init();
    }
}
