<?php
declare(strict_types=1);

namespace App\Model\Entity;

use ArrayAccess;
use App\Model\Enum\Role;
use Authentication\IdentityInterface as AuthenticationIdentityInterface;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authorization\AuthorizationServiceInterface;
use Authorization\IdentityInterface as AuthorizationIdentityInterface;
use Authorization\Policy\Result;
use Authorization\Policy\ResultInterface;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property string $id
 * @property string $username
 * @property string $password
 * @property string|null $email
 * @property bool $is_admin
 * @property string $role
 * @property bool $active
 * @property string|null $image
 * @property string|null $keywords
 * @property string|null $alt_text
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Article[] $articles
 */
class User extends Entity implements AuthenticationIdentityInterface, AuthorizationIdentityInterface
{
    use ImageUrlTrait;
    
    /**
     * Authorization service instance
     *
     * @var \Authorization\AuthorizationServiceInterface|null
     */
    protected ?AuthorizationServiceInterface $_authorization = null;

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
        'role' => false, // Prevent mass assignment by default
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
     * @param  string $password The plain text password to be hashed.
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
     * @param  string $userId The ID of the user whose account is being checked.
     * @param  array  $data   An associative array containing account data, including the 'active' flag.
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
     * @param  string $userId The ID of the user attempting to modify the account.
     * @param  array  $data   An associative array containing the account data, including the 'is_admin' flag.
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

    /**
     * Get the Role enum instance for this user
     *
     * @return \App\Model\Enum\Role|null
     */
    public function getRoleEnum(): ?Role
    {
        if (!isset($this->role)) {
            // Backward compatibility: if role is not set but is_admin is true, return admin
            if ($this->is_admin) {
                return Role::ADMIN;
            }
            return Role::USER;
        }
        
        return Role::tryFromString($this->role);
    }

    /**
     * Check if the user is an administrator
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        // Backward compatibility: check both role and is_admin
        return $this->role === Role::ADMIN->value || $this->is_admin === true;
    }

    /**
     * Check if the user is an editor
     *
     * @return bool
     */
    public function isEditor(): bool
    {
        return $this->role === Role::EDITOR->value;
    }

    /**
     * Check if the user is an author
     *
     * @return bool
     */
    public function isAuthor(): bool
    {
        return $this->role === Role::AUTHOR->value;
    }

    /**
     * Check if the user is a regular logged-in user
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role === Role::USER->value || (!$this->role && !$this->is_admin);
    }

    /**
     * Check if the user can access the admin panel
     *
     * @return bool
     */
    public function canAccessAdmin(): bool
    {
        $roleEnum = $this->getRoleEnum();
        return $roleEnum ? $roleEnum->canAccessAdmin() : false;
    }

    /**
     * Check if the user can edit any content (not just their own)
     *
     * @return bool
     */
    public function canEditAnyContent(): bool
    {
        $roleEnum = $this->getRoleEnum();
        return $roleEnum ? $roleEnum->canEditAnyContent() : false;
    }

    /**
     * Check if the user can manage other users
     *
     * @return bool
     */
    public function canManageUsers(): bool
    {
        $roleEnum = $this->getRoleEnum();
        return $roleEnum ? $roleEnum->canManageUsers() : false;
    }
    
    /**
     * Sets the authorization service for this identity.
     *
     * @param  \Authorization\AuthorizationServiceInterface $service The authorization service
     * @return $this
     */
    public function setAuthorization(AuthorizationServiceInterface $service)
    {
        $this->_authorization = $service;
        return $this;
    }
    
    /**
     * Check whether the current identity can perform an action.
     *
     * @param  string $action   The action to check authorization for.
     * @param  mixed  $resource The resource to check authorization for.
     * @return bool
     */
    public function can(string $action, mixed $resource): bool
    {
        if ($this->_authorization === null) {
            return false;
        }
        return $this->_authorization->can($this, $action, $resource);
    }
    
    /**
     * Check whether the current identity can perform an action and get a result.
     *
     * @param  string $action   The action to check authorization for.
     * @param  mixed  $resource The resource to check authorization for.
     * @return \Authorization\Policy\ResultInterface
     */
    public function canResult(string $action, mixed $resource): ResultInterface
    {
        if ($this->_authorization === null) {
            // Return a failed result when no authorization service is set
            return new Result(false, 'Authorization service not set.');
        }
        return $this->_authorization->canResult($this, $action, $resource);
    }
    
    /**
     * Apply authorization scope conditions/restrictions.
     *
     * @param  string $action       The action to check authorization for.
     * @param  mixed  $resource     The resource to check authorization for.
     * @param  mixed  $optionalArgs Multiple additional arguments which are passed to the scope
     * @return mixed The modified resource
     * @throws \Authorization\Exception\ForbiddenException
     */
    public function applyScope(string $action, mixed $resource, mixed ...$optionalArgs): mixed
    {
        if ($this->_authorization === null) {
            throw new \Authorization\Exception\ForbiddenException(null, 'Authorization service not set.');
        }
        return $this->_authorization->applyScope($this, $action, $resource, ...$optionalArgs);
    }
    
    /**
     * Get the original data for this identity.
     *
     * @return \ArrayAccess|array
     */
    public function getOriginalData(): \ArrayAccess|array
    {
        return $this;
    }
    
    /**
     * Get the primary key/id field for this identity.
     *
     * @return array|string|int|null
     */
    public function getIdentifier(): array|string|int|null
    {
        return $this->id;
    }
}
