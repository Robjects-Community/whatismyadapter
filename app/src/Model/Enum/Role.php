<?php
declare(strict_types=1);

namespace App\Model\Enum;

/**
 * User Role Enum
 * 
 * Defines the available user roles in the application
 * 
 * @since PHP 8.1
 */
enum Role: string
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case AUTHOR = 'author';
    case USER = 'user';

    /**
     * Get the label for the role for display in forms
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => __('Administrator'),
            self::EDITOR => __('Editor'),
            self::AUTHOR => __('Author'),
            self::USER => __('User'),
        };
    }

    /**
     * Get the description of the role's permissions
     *
     * @return string
     */
    public function description(): string
    {
        return match($this) {
            self::ADMIN => __('Full system access, can manage users and all content'),
            self::EDITOR => __('Can create, edit, and delete all articles and products'),
            self::AUTHOR => __('Can create articles and products, but can only edit/delete their own'),
            self::USER => __('Standard registered user, can only access frontend'),
        };
    }

    /**
     * Check if this role has admin access
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if this role can access the admin panel
     *
     * @return bool
     */
    public function canAccessAdmin(): bool
    {
        return in_array($this, [self::ADMIN, self::EDITOR, self::AUTHOR]);
    }

    /**
     * Check if this role can edit any content (not just their own)
     *
     * @return bool
     */
    public function canEditAnyContent(): bool
    {
        return in_array($this, [self::ADMIN, self::EDITOR]);
    }

    /**
     * Check if this role can manage users
     *
     * @return bool
     */
    public function canManageUsers(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Get all available roles for forms
     *
     * @return array<string, string>
     */
    public static function getFormOptions(): array
    {
        $options = [];
        foreach (self::cases() as $role) {
            $options[$role->value] = $role->label();
        }
        return $options;
    }

    /**
     * Get roles that can access the admin panel
     *
     * @return array<self>
     */
    public static function getAdminRoles(): array
    {
        return [self::ADMIN, self::EDITOR, self::AUTHOR];
    }

    /**
     * Create a Role from a string value
     *
     * @param string|null $value
     * @return self|null
     */
    public static function tryFromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }
        
        return self::tryFrom($value);
    }
}
