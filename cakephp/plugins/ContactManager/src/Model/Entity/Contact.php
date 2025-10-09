<?php
declare(strict_types=1);

// plugins/ContactManager/src/Model/Table/ContactsTable.php
// plugins/ContactManager/src/Model/Entity/Contact.php:
namespace ContactManager\Model\Entity;

use Cake\ORM\Entity;

/**
 * Contact Entity
 *
 * This class represents a contact entity in the ContactManager plugin.
 * It defines the properties of a contact and specifies which fields can be mass assigned.
 *
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $contact_num
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $country
 */

class Contact extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected array $_accessible = [
        // Set '*' to true to allow all unspecified fields to be mass assigned.
        'id' => true,
        'first_name' => true,
        'last_name' => true,
        'email' => true,
        'contact_num' => true,
        'address' => true,
        'city' => true,
        'state' => true,
        'country' => true,
    ];
}
