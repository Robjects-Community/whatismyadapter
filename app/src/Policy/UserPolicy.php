<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use App\Model\Enum\Role;
use Authorization\IdentityInterface;

/**
 * User Policy
 * 
 * Defines authorization rules for user management
 */
class UserPolicy
{
    /**
     * Check if user can index users
     *
     * @param \Authorization\IdentityInterface $identity The identity.
     * @param \App\Model\Entity\User $user The user.
     * @return bool
     */
    public function canIndex(IdentityInterface $identity, User $user): bool
    {
        // Only admins can index users
        return $identity->canManageUsers();
    }

    /**
     * Check if user can view a user
     *
     * @param \Authorization\IdentityInterface $identity The identity.
     * @param \App\Model\Entity\User $user The user.
     * @return bool
     */
    public function canView(IdentityInterface $identity, User $user): bool
    {
        // Admins can view all users
        if ($identity->canManageUsers()) {
            return true;
        }
        
        // Users can view their own profile
        if ($identity->id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can add a new user
     *
     * @param \Authorization\IdentityInterface $identity The identity.
     * @param \App\Model\Entity\User $user The user.
     * @return bool
     */
    public function canAdd(IdentityInterface $identity, User $user): bool
    {
        // Only admins can add users
        return $identity->canManageUsers();
    }

    /**
     * Check if user can edit a user
     *
     * @param \Authorization\IdentityInterface $identity The identity.
     * @param \App\Model\Entity\User $user The user.
     * @return bool
     */
    public function canEdit(IdentityInterface $identity, User $user): bool
    {
        // Admins can edit all users
        if ($identity->canManageUsers()) {
            return true;
        }
        
        // Users can edit their own profile (but not role)
        if ($identity->id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can delete a user
     *
     * @param \Authorization\IdentityInterface $identity The identity.
     * @param \App\Model\Entity\User $user The user.
     * @return bool
     */
    public function canDelete(IdentityInterface $identity, User $user): bool
    {
        // Only admins can delete users
        if (!$identity->canManageUsers()) {
            return false;
        }
        
        // Prevent self-deletion
        if ($identity->id === $user->id) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can change a user's role
     *
     * @param \Authorization\IdentityInterface $identity The identity.
     * @param \App\Model\Entity\User $user The user.
     * @return bool
     */
    public function canChangeRole(IdentityInterface $identity, User $user): bool
    {
        // Only admins can change roles
        if (!$identity->canManageUsers()) {
            return false;
        }
        
        // Prevent self-demotion
        if ($identity->id === $user->id) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can change a user's active status
     *
     * @param \Authorization\IdentityInterface $identity The identity.
     * @param \App\Model\Entity\User $user The user.
     * @return bool
     */
    public function canChangeActiveStatus(IdentityInterface $identity, User $user): bool
    {
        // Only admins can change active status
        if (!$identity->canManageUsers()) {
            return false;
        }
        
        // Prevent self-deactivation
        if ($identity->id === $user->id) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can change a user's admin status (legacy)
     *
     * @param \Authorization\IdentityInterface $identity The identity.
     * @param \App\Model\Entity\User $user The user.
     * @return bool
     */
    public function canChangeAdminStatus(IdentityInterface $identity, User $user): bool
    {
        // Only admins can change admin status
        if (!$identity->canManageUsers()) {
            return false;
        }
        
        // Prevent self-demotion
        if ($identity->id === $user->id) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if user can logout
     *
     * @param \Authorization\IdentityInterface $identity The identity.
     * @param \App\Model\Entity\User $user The user.
     * @return bool
     */
    public function canLogout(IdentityInterface $identity, User $user): bool
    {
        // Everyone can logout
        return true;
    }

    /**
     * Scope the index query based on user role
     * Non-admins shouldn't see the user list
     *
     * @param \Authorization\IdentityInterface $identity The identity.
     * @param \Cake\ORM\Query $query The query.
     * @return \Cake\ORM\Query
     */
    public function scopeIndex(IdentityInterface $identity, $query)
    {
        // Admins see all users
        if ($identity->canManageUsers()) {
            return $query;
        }
        
        // Others see nothing (shouldn't get here)
        return $query->where(['1 = 0']);
    }
}
