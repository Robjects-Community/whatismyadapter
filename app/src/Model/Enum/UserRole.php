<?php
declare(strict_types=1);

namespace App\Model\Enum;

/**
 * UserRole Enum Class
 * 
 * Defines all available user roles in the system with their associated
 * permission levels and helper methods for role management.
 * 
 * This follows CakePHP 5.x patterns for constants and provides
 * type-safe role handling throughout the application.
 */
class UserRole
{
    // Core system roles
    public const ADMINISTRATOR = 'administrator';
    public const EDITOR = 'editor';
    public const AUTHOR = 'author';
    public const CONTRIBUTOR = 'contributor';
    public const SUBSCRIBER = 'subscriber';
    
    // E-commerce roles
    public const SHOP_MANAGER = 'shop_manager';
    public const CUSTOMER = 'customer';
    
    // Special roles
    public const LOGGED_IN_USER = 'logged_in_user';
    public const GUEST = 'guest';
    
    /**
     * Role hierarchy - higher values have more permissions
     */
    private const ROLE_HIERARCHY = [
        self::GUEST => 0,
        self::LOGGED_IN_USER => 10,
        self::SUBSCRIBER => 20,
        self::CUSTOMER => 25,
        self::CONTRIBUTOR => 30,
        self::AUTHOR => 40,
        self::SHOP_MANAGER => 50,
        self::EDITOR => 60,
        self::ADMINISTRATOR => 100,
    ];
    
    /**
     * Human-readable role names for display
     */
    private const ROLE_NAMES = [
        self::ADMINISTRATOR => 'Administrator',
        self::EDITOR => 'Editor',
        self::AUTHOR => 'Author',
        self::CONTRIBUTOR => 'Contributor',
        self::SUBSCRIBER => 'Subscriber',
        self::SHOP_MANAGER => 'Shop Manager',
        self::CUSTOMER => 'Customer',
        self::LOGGED_IN_USER => 'Logged In User',
        self::GUEST => 'Guest',
    ];
    
    /**
     * Role descriptions for admin UI
     */
    private const ROLE_DESCRIPTIONS = [
        self::ADMINISTRATOR => 'Full system access, can manage all content and settings',
        self::EDITOR => 'Can publish and manage posts including those of other users',
        self::AUTHOR => 'Can publish and manage their own posts',
        self::CONTRIBUTOR => 'Can write and manage their own posts but cannot publish',
        self::SUBSCRIBER => 'Can only manage their profile',
        self::SHOP_MANAGER => 'Can manage products, orders, and shop settings',
        self::CUSTOMER => 'Can view and purchase products, manage orders',
        self::LOGGED_IN_USER => 'Default role for newly registered users',
        self::GUEST => 'Non-authenticated visitor',
    ];
    
    /**
     * Content type permissions by role
     */
    private const CONTENT_PERMISSIONS = [
        'articles' => [
            self::ADMINISTRATOR => ['create', 'read', 'update', 'delete', 'publish', 'unpublish'],
            self::EDITOR => ['create', 'read', 'update', 'delete', 'publish', 'unpublish'],
            self::AUTHOR => ['create', 'read', 'update_own', 'delete_own', 'publish_own'],
            self::CONTRIBUTOR => ['create', 'read', 'update_own', 'delete_own'],
            self::SUBSCRIBER => ['read'],
            self::LOGGED_IN_USER => ['read'],
            self::GUEST => ['read'],
        ],
        'products' => [
            self::ADMINISTRATOR => ['create', 'read', 'update', 'delete', 'publish'],
            self::SHOP_MANAGER => ['create', 'read', 'update', 'delete', 'publish'],
            self::EDITOR => ['read', 'update'],
            self::CUSTOMER => ['read', 'purchase'],
            self::LOGGED_IN_USER => ['read'],
            self::GUEST => ['read'],
        ],
        'comments' => [
            self::ADMINISTRATOR => ['create', 'read', 'update', 'delete', 'moderate'],
            self::EDITOR => ['create', 'read', 'update', 'delete', 'moderate'],
            self::AUTHOR => ['create', 'read', 'update_own', 'delete_own'],
            self::LOGGED_IN_USER => ['create', 'read', 'update_own', 'delete_own'],
            self::GUEST => ['read'],
        ],
        'media' => [
            self::ADMINISTRATOR => ['create', 'read', 'update', 'delete'],
            self::EDITOR => ['create', 'read', 'update', 'delete'],
            self::AUTHOR => ['create', 'read', 'update_own', 'delete_own'],
            self::CONTRIBUTOR => ['create', 'read'],
            self::SHOP_MANAGER => ['create', 'read', 'update', 'delete'],
        ],
    ];
    
    /**
     * Get all available roles
     * 
     * @return array<string>
     */
    public static function getAllRoles(): array
    {
        return array_keys(self::ROLE_NAMES);
    }
    
    /**
     * Get roles for dropdown/select fields
     * 
     * @return array<string, string>
     */
    public static function getRoleOptions(): array
    {
        return self::ROLE_NAMES;
    }
    
    /**
     * Get role display name
     * 
     * @param string $role
     * @return string
     */
    public static function getRoleName(string $role): string
    {
        return self::ROLE_NAMES[$role] ?? ucfirst(str_replace('_', ' ', $role));
    }
    
    /**
     * Get role description
     * 
     * @param string $role
     * @return string
     */
    public static function getRoleDescription(string $role): string
    {
        return self::ROLE_DESCRIPTIONS[$role] ?? '';
    }
    
    /**
     * Check if a role exists
     * 
     * @param string $role
     * @return bool
     */
    public static function isValidRole(string $role): bool
    {
        return isset(self::ROLE_NAMES[$role]);
    }
    
    /**
     * Check if role has permission for specific action on content type
     * 
     * @param string $role User's role
     * @param string $contentType Type of content (articles, products, etc.)
     * @param string $permission Permission to check (create, read, update, delete, etc.)
     * @param bool $isOwner Whether the user owns the content
     * @return bool
     */
    public static function hasPermission(string $role, string $contentType, string $permission, bool $isOwner = false): bool
    {
        if (!self::isValidRole($role)) {
            return false;
        }
        
        // Administrators always have full access
        if ($role === self::ADMINISTRATOR) {
            return true;
        }
        
        // Check if content type exists in permissions
        if (!isset(self::CONTENT_PERMISSIONS[$contentType])) {
            return false;
        }
        
        // Check if role has permissions for this content type
        if (!isset(self::CONTENT_PERMISSIONS[$contentType][$role])) {
            return false;
        }
        
        $rolePermissions = self::CONTENT_PERMISSIONS[$contentType][$role];
        
        // Check for exact permission
        if (in_array($permission, $rolePermissions, true)) {
            return true;
        }
        
        // Check for ownership-based permissions
        if ($isOwner) {
            $ownPermission = $permission . '_own';
            if (in_array($ownPermission, $rolePermissions, true)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if role can perform action regardless of ownership
     * 
     * @param string $role
     * @param string $contentType
     * @param string $permission
     * @return bool
     */
    public static function canPerformAction(string $role, string $contentType, string $permission): bool
    {
        return self::hasPermission($role, $contentType, $permission, false);
    }
    
    /**
     * Check if role can only perform action on owned content
     * 
     * @param string $role
     * @param string $contentType
     * @param string $permission
     * @return bool
     */
    public static function requiresOwnership(string $role, string $contentType, string $permission): bool
    {
        if (!self::isValidRole($role)) {
            return true;
        }
        
        // Check if has general permission
        if (self::canPerformAction($role, $contentType, $permission)) {
            return false;
        }
        
        // Check if has ownership-based permission
        return self::hasPermission($role, $contentType, $permission, true);
    }
    
    /**
     * Compare role hierarchy levels
     * 
     * @param string $role1
     * @param string $role2
     * @return int -1 if role1 < role2, 0 if equal, 1 if role1 > role2
     */
    public static function compareRoles(string $role1, string $role2): int
    {
        $level1 = self::ROLE_HIERARCHY[$role1] ?? 0;
        $level2 = self::ROLE_HIERARCHY[$role2] ?? 0;
        
        return $level1 <=> $level2;
    }
    
    /**
     * Check if role has higher privileges than another
     * 
     * @param string $role
     * @param string $compareToRole
     * @return bool
     */
    public static function hasHigherPrivileges(string $role, string $compareToRole): bool
    {
        return self::compareRoles($role, $compareToRole) > 0;
    }
    
    /**
     * Get all permissions for a role on a content type
     * 
     * @param string $role
     * @param string $contentType
     * @return array<string>
     */
    public static function getRolePermissions(string $role, string $contentType): array
    {
        if (!self::isValidRole($role)) {
            return [];
        }
        
        return self::CONTENT_PERMISSIONS[$contentType][$role] ?? [];
    }
    
    /**
     * Get content types a role can access
     * 
     * @param string $role
     * @return array<string>
     */
    public static function getAccessibleContentTypes(string $role): array
    {
        if (!self::isValidRole($role)) {
            return [];
        }
        
        $contentTypes = [];
        foreach (self::CONTENT_PERMISSIONS as $contentType => $permissions) {
            if (isset($permissions[$role]) && !empty($permissions[$role])) {
                $contentTypes[] = $contentType;
            }
        }
        
        return $contentTypes;
    }
    
    /**
     * Get default role for new users
     * 
     * @return string
     */
    public static function getDefaultRole(): string
    {
        return self::LOGGED_IN_USER;
    }
    
    /**
     * Get administrative roles
     * 
     * @return array<string>
     */
    public static function getAdminRoles(): array
    {
        return [
            self::ADMINISTRATOR,
            self::EDITOR,
            self::SHOP_MANAGER,
        ];
    }
    
    /**
     * Check if role has admin access
     * 
     * @param string $role
     * @return bool
     */
    public static function isAdminRole(string $role): bool
    {
        return in_array($role, self::getAdminRoles(), true);
    }
    
    /**
     * Get content creation roles
     * 
     * @return array<string>
     */
    public static function getContentCreatorRoles(): array
    {
        return [
            self::ADMINISTRATOR,
            self::EDITOR,
            self::AUTHOR,
            self::CONTRIBUTOR,
        ];
    }
    
    /**
     * Check if role can create content
     * 
     * @param string $role
     * @return bool
     */
    public static function canCreateContent(string $role): bool
    {
        return in_array($role, self::getContentCreatorRoles(), true);
    }
}
