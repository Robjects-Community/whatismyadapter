<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * QueueConfiguration Entity
 *
 * @property string $id
 * @property string $name
 * @property string $config_key
 * @property string $queue_type
 * @property string $queue_name
 * @property string $host
 * @property int|null $port
 * @property string|null $username
 * @property string|null $password
 * @property int|null $db_index
 * @property string|null $vhost
 * @property string|null $exchange
 * @property string|null $routing_key
 * @property bool $ssl_enabled
 * @property bool $persistent
 * @property int $max_workers
 * @property int $priority
 * @property bool $enabled
 * @property string|null $description
 * @property array|null $config_data
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class QueueConfiguration extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'config_key' => true,
        'queue_type' => true,
        'queue_name' => true,
        'host' => true,
        'port' => true,
        'username' => true,
        'password' => true,
        'db_index' => true,
        'vhost' => true,
        'exchange' => true,
        'routing_key' => true,
        'ssl_enabled' => true,
        'persistent' => true,
        'max_workers' => true,
        'priority' => true,
        'enabled' => true,
        'description' => true,
        'config_data' => true,
        'created' => true,
        'modified' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'password',
    ];
}
