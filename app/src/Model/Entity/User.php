<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property string $id
 * @property string $username
 * @property string $password
 * @property string|null $email
 * @property bool $is_admin
 * @property bool $active
 * @property string|null $image
 * @property string|null $keywords
 * @property string|null $alt_text
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Article[] $articles
 */
class User extends Entity
{
    use ImageUrlTrait;

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
        'username' => true,
        'password' => true,
        'email' => true,
        'created' => true,
        'modified' => true,
        'articles' => true,
        'image' => true,
        'is_admin' => false,
        'active' => false,
        'keywords' => true,
        'alt_text' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'password',
    ];

    /**
     * Sets the password for the user entity.
     *
     * This method hashes the password using the DefaultPasswordHasher if the provided
     * password is not empty. If the password is empty, it returns the original password value.
     * This ensures that passwords are stored securely in the database and prevents
     * overwriting existing passwords with empty values during updates.
     *
     * @param string $password The plain text password to be hashed.
     * @return string|null The hashed password if the input is not empty, or the original password value.
     */
    protected function _setPassword(string $password): ?string
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher())->hash($password);
        }

        return $this->getOriginal('password');
    }

    /**
     * Checks if the account associated with the given user ID is being disabled by the user themselves.
     *
     * This method evaluates whether the 'active' flag is set to true in the provided data array
     * and if the current object's ID matches the provided user ID. If both conditions are met, it returns true,
     * indicating that the user is attempting to disable their own account.
     *
     * @param string $userId The ID of the user whose account is being checked.
     * @param array $data An associative array containing account data, including the 'active' flag.
     * @return bool|null Returns true if the account is being disabled by the user themselves, false otherwise.
     *                   (Note: The method always returns a boolean, so the null part of the return type
     *                   is not utilized in the current implementation.)
     */
    public function lockAdminAccountError(string $userId, array $data): ?bool
    {
        //Setting is_admin to 0 for your own account
        if (isset($data['is_admin']) && !$data['is_admin'] && $this->id == $userId) {
            return true;
        }

        return false;
    }

    /**
     * Checks if an admin account is being demoted by the account owner.
     *
     * This method determines whether the 'is_admin' flag is being set to false (0)
     * for the account that matches the provided user ID. It returns true if the
     * account owner is attempting to remove their own admin privileges, otherwise
     * it returns false.
     *
     * @param string $userId The ID of the user attempting to modify the account.
     * @param array $data An associative array containing the account data, including the 'is_admin' flag.
     * @return bool|null Returns true if the admin account is being demoted by the owner, false otherwise.
     *                   (Note: The method always returns a boolean, so the null part of the return type
     *                   is not utilized in the current implementation.)
     */
    public function lockEnabledAccountError(string $userId, array $data): ?bool
    {
        //Setting active to 0 for your own account
        if (isset($data['active']) && !$data['active'] && $this->id == $userId) {
            return true;
        }

        return false;
    }
}
