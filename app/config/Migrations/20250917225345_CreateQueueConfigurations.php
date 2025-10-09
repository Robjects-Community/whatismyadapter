<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateQueueConfigurations extends BaseMigration
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
        $table = $this->table('queue_configurations', [
            'id' => false,
            'primary_key' => ['id'],
        ]);
        
        $table->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('name', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => false,
            'comment' => 'Display name for the queue configuration'
        ])
        ->addColumn('config_key', 'string', [
            'default' => null,
            'limit' => 50,
            'null' => false,
            'comment' => 'Configuration key used in queue.php'
        ])
        ->addColumn('queue_type', 'string', [
            'default' => 'redis',
            'limit' => 20,
            'null' => false,
            'comment' => 'Type of queue: redis or rabbitmq'
        ])
        ->addColumn('queue_name', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => false,
            'comment' => 'Queue name to listen on'
        ])
        ->addColumn('host', 'string', [
            'default' => 'localhost',
            'limit' => 255,
            'null' => false,
            'comment' => 'Queue server hostname'
        ])
        ->addColumn('port', 'integer', [
            'default' => null,
            'null' => true,
            'comment' => 'Queue server port'
        ])
        ->addColumn('username', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => true,
            'comment' => 'Username for queue authentication'
        ])
        ->addColumn('password', 'string', [
            'default' => null,
            'limit' => 255,
            'null' => true,
            'comment' => 'Password for queue authentication (encrypted)'
        ])
        ->addColumn('db_index', 'integer', [
            'default' => 0,
            'null' => true,
            'comment' => 'Redis database number'
        ])
        ->addColumn('vhost', 'string', [
            'default' => '/',
            'limit' => 100,
            'null' => true,
            'comment' => 'RabbitMQ virtual host'
        ])
        ->addColumn('exchange', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => true,
            'comment' => 'RabbitMQ exchange name'
        ])
        ->addColumn('routing_key', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => true,
            'comment' => 'RabbitMQ routing key'
        ])
        ->addColumn('ssl_enabled', 'boolean', [
            'default' => false,
            'null' => false,
            'comment' => 'Enable SSL connection'
        ])
        ->addColumn('persistent', 'boolean', [
            'default' => true,
            'null' => false,
            'comment' => 'Use persistent connection'
        ])
        ->addColumn('max_workers', 'integer', [
            'default' => 1,
            'null' => false,
            'comment' => 'Maximum number of worker processes'
        ])
        ->addColumn('priority', 'integer', [
            'default' => 5,
            'null' => false,
            'comment' => 'Queue priority (1-10, higher is more priority)'
        ])
        ->addColumn('enabled', 'boolean', [
            'default' => true,
            'null' => false,
            'comment' => 'Whether this queue configuration is enabled'
        ])
        ->addColumn('description', 'text', [
            'default' => null,
            'null' => true,
            'comment' => 'Description of what this queue handles'
        ])
        ->addColumn('config_data', 'json', [
            'default' => null,
            'null' => true,
            'comment' => 'Additional configuration options as JSON'
        ])
        ->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ])
        ->addIndex(['config_key'], ['unique' => true])
        ->addIndex(['queue_type'])
        ->addIndex(['enabled'])
        ->addIndex(['priority'])
        ->create();
    }
}
