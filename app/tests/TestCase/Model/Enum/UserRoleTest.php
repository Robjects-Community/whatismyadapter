<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Enum;

use App\Model\Enum\UserRole;
use Cake\TestSuite\TestCase;

/**
 * UserRole Test Case
 * 
 * Tests the UserRole enum class functionality including
 * permissions, role hierarchy, and helper methods.
 */
class UserRoleTest extends TestCase
{
    /**
     * Test getting all roles
     */
    public function testGetAllRoles(): void
    {
        $roles = UserRole::getAllRoles();
        
        $this->assertIsArray($roles);
        $this->assertContains(UserRole::ADMINISTRATOR, $roles);
        $this->assertContains(UserRole::EDITOR, $roles);
        $this->assertContains(UserRole::AUTHOR, $roles);
        $this->assertContains(UserRole::LOGGED_IN_USER, $roles);
        $this->assertCount(9, $roles);
    }
    
    /**
     * Test role validation
     */
    public function testIsValidRole(): void
    {
        $this->assertTrue(UserRole::isValidRole(UserRole::ADMINISTRATOR));
        $this->assertTrue(UserRole::isValidRole(UserRole::EDITOR));
        $this->assertTrue(UserRole::isValidRole(UserRole::AUTHOR));
        $this->assertFalse(UserRole::isValidRole('invalid_role'));
        $this->assertFalse(UserRole::isValidRole(''));
    }
    
    /**
     * Test permission checking for articles
     */
    public function testArticlePermissions(): void
    {
        // Administrator has all permissions
        $this->assertTrue(UserRole::hasPermission(UserRole::ADMINISTRATOR, 'articles', 'create'));
        $this->assertTrue(UserRole::hasPermission(UserRole::ADMINISTRATOR, 'articles', 'delete'));
        $this->assertTrue(UserRole::hasPermission(UserRole::ADMINISTRATOR, 'articles', 'publish'));
        
        // Editor has most permissions
        $this->assertTrue(UserRole::hasPermission(UserRole::EDITOR, 'articles', 'create'));
        $this->assertTrue(UserRole::hasPermission(UserRole::EDITOR, 'articles', 'delete'));
        $this->assertTrue(UserRole::hasPermission(UserRole::EDITOR, 'articles', 'publish'));
        
        // Author can only manage own content
        $this->assertTrue(UserRole::hasPermission(UserRole::AUTHOR, 'articles', 'create'));
        $this->assertFalse(UserRole::hasPermission(UserRole::AUTHOR, 'articles', 'delete'));
        $this->assertTrue(UserRole::hasPermission(UserRole::AUTHOR, 'articles', 'delete', true)); // as owner
        $this->assertFalse(UserRole::hasPermission(UserRole::AUTHOR, 'articles', 'publish'));
        $this->assertTrue(UserRole::hasPermission(UserRole::AUTHOR, 'articles', 'publish', true)); // as owner
        
        // Contributor cannot publish
        $this->assertTrue(UserRole::hasPermission(UserRole::CONTRIBUTOR, 'articles', 'create'));
        $this->assertFalse(UserRole::hasPermission(UserRole::CONTRIBUTOR, 'articles', 'publish'));
        $this->assertFalse(UserRole::hasPermission(UserRole::CONTRIBUTOR, 'articles', 'publish', true));
        
        // Logged in user can only read
        $this->assertTrue(UserRole::hasPermission(UserRole::LOGGED_IN_USER, 'articles', 'read'));
        $this->assertFalse(UserRole::hasPermission(UserRole::LOGGED_IN_USER, 'articles', 'create'));
        $this->assertFalse(UserRole::hasPermission(UserRole::LOGGED_IN_USER, 'articles', 'update'));
    }
    
    /**
     * Test product permissions
     */
    public function testProductPermissions(): void
    {
        // Shop manager has full product access
        $this->assertTrue(UserRole::hasPermission(UserRole::SHOP_MANAGER, 'products', 'create'));
        $this->assertTrue(UserRole::hasPermission(UserRole::SHOP_MANAGER, 'products', 'delete'));
        $this->assertTrue(UserRole::hasPermission(UserRole::SHOP_MANAGER, 'products', 'publish'));
        
        // Editor can read and update products
        $this->assertTrue(UserRole::hasPermission(UserRole::EDITOR, 'products', 'read'));
        $this->assertTrue(UserRole::hasPermission(UserRole::EDITOR, 'products', 'update'));
        $this->assertFalse(UserRole::hasPermission(UserRole::EDITOR, 'products', 'create'));
        
        // Customer can purchase
        $this->assertTrue(UserRole::hasPermission(UserRole::CUSTOMER, 'products', 'read'));
        $this->assertTrue(UserRole::hasPermission(UserRole::CUSTOMER, 'products', 'purchase'));
        $this->assertFalse(UserRole::hasPermission(UserRole::CUSTOMER, 'products', 'create'));
    }
    
    /**
     * Test ownership requirements
     */
    public function testRequiresOwnership(): void
    {
        // Administrator never requires ownership
        $this->assertFalse(UserRole::requiresOwnership(UserRole::ADMINISTRATOR, 'articles', 'delete'));
        
        // Editor doesn't require ownership for articles
        $this->assertFalse(UserRole::requiresOwnership(UserRole::EDITOR, 'articles', 'delete'));
        
        // Author requires ownership for delete
        $this->assertTrue(UserRole::requiresOwnership(UserRole::AUTHOR, 'articles', 'delete'));
        $this->assertTrue(UserRole::requiresOwnership(UserRole::AUTHOR, 'articles', 'update'));
        
        // Invalid role always requires ownership
        $this->assertTrue(UserRole::requiresOwnership('invalid_role', 'articles', 'delete'));
    }
    
    /**
     * Test role hierarchy comparison
     */
    public function testRoleHierarchy(): void
    {
        // Administrator is higher than all
        $this->assertTrue(UserRole::hasHigherPrivileges(UserRole::ADMINISTRATOR, UserRole::EDITOR));
        $this->assertTrue(UserRole::hasHigherPrivileges(UserRole::ADMINISTRATOR, UserRole::AUTHOR));
        $this->assertTrue(UserRole::hasHigherPrivileges(UserRole::ADMINISTRATOR, UserRole::GUEST));
        
        // Editor is higher than Author
        $this->assertTrue(UserRole::hasHigherPrivileges(UserRole::EDITOR, UserRole::AUTHOR));
        $this->assertTrue(UserRole::hasHigherPrivileges(UserRole::EDITOR, UserRole::CONTRIBUTOR));
        
        // Author is higher than Contributor
        $this->assertTrue(UserRole::hasHigherPrivileges(UserRole::AUTHOR, UserRole::CONTRIBUTOR));
        
        // Logged in user is higher than Guest
        $this->assertTrue(UserRole::hasHigherPrivileges(UserRole::LOGGED_IN_USER, UserRole::GUEST));
        
        // Same role comparison
        $this->assertEquals(0, UserRole::compareRoles(UserRole::EDITOR, UserRole::EDITOR));
        $this->assertFalse(UserRole::hasHigherPrivileges(UserRole::EDITOR, UserRole::EDITOR));
    }
    
    /**
     * Test getting role permissions
     */
    public function testGetRolePermissions(): void
    {
        $editorPerms = UserRole::getRolePermissions(UserRole::EDITOR, 'articles');
        $this->assertIsArray($editorPerms);
        $this->assertContains('create', $editorPerms);
        $this->assertContains('publish', $editorPerms);
        $this->assertContains('delete', $editorPerms);
        
        $authorPerms = UserRole::getRolePermissions(UserRole::AUTHOR, 'articles');
        $this->assertIsArray($authorPerms);
        $this->assertContains('create', $authorPerms);
        $this->assertContains('update_own', $authorPerms);
        $this->assertNotContains('delete', $authorPerms);
        
        // Invalid role returns empty array
        $invalidPerms = UserRole::getRolePermissions('invalid_role', 'articles');
        $this->assertIsArray($invalidPerms);
        $this->assertEmpty($invalidPerms);
    }
    
    /**
     * Test accessible content types
     */
    public function testGetAccessibleContentTypes(): void
    {
        $adminTypes = UserRole::getAccessibleContentTypes(UserRole::ADMINISTRATOR);
        $this->assertContains('articles', $adminTypes);
        $this->assertContains('products', $adminTypes);
        $this->assertContains('comments', $adminTypes);
        $this->assertContains('media', $adminTypes);
        
        $shopManagerTypes = UserRole::getAccessibleContentTypes(UserRole::SHOP_MANAGER);
        $this->assertContains('products', $shopManagerTypes);
        $this->assertContains('media', $shopManagerTypes);
        
        $guestTypes = UserRole::getAccessibleContentTypes(UserRole::GUEST);
        $this->assertContains('articles', $guestTypes);
        $this->assertContains('products', $guestTypes);
        $this->assertContains('comments', $guestTypes);
        $this->assertNotContains('media', $guestTypes);
    }
    
    /**
     * Test admin role checking
     */
    public function testIsAdminRole(): void
    {
        $this->assertTrue(UserRole::isAdminRole(UserRole::ADMINISTRATOR));
        $this->assertTrue(UserRole::isAdminRole(UserRole::EDITOR));
        $this->assertTrue(UserRole::isAdminRole(UserRole::SHOP_MANAGER));
        
        $this->assertFalse(UserRole::isAdminRole(UserRole::AUTHOR));
        $this->assertFalse(UserRole::isAdminRole(UserRole::CONTRIBUTOR));
        $this->assertFalse(UserRole::isAdminRole(UserRole::LOGGED_IN_USER));
        $this->assertFalse(UserRole::isAdminRole(UserRole::GUEST));
    }
    
    /**
     * Test content creator role checking
     */
    public function testCanCreateContent(): void
    {
        $this->assertTrue(UserRole::canCreateContent(UserRole::ADMINISTRATOR));
        $this->assertTrue(UserRole::canCreateContent(UserRole::EDITOR));
        $this->assertTrue(UserRole::canCreateContent(UserRole::AUTHOR));
        $this->assertTrue(UserRole::canCreateContent(UserRole::CONTRIBUTOR));
        
        $this->assertFalse(UserRole::canCreateContent(UserRole::SUBSCRIBER));
        $this->assertFalse(UserRole::canCreateContent(UserRole::CUSTOMER));
        $this->assertFalse(UserRole::canCreateContent(UserRole::LOGGED_IN_USER));
        $this->assertFalse(UserRole::canCreateContent(UserRole::GUEST));
    }
    
    /**
     * Test default role
     */
    public function testGetDefaultRole(): void
    {
        $defaultRole = UserRole::getDefaultRole();
        $this->assertEquals(UserRole::LOGGED_IN_USER, $defaultRole);
    }
    
    /**
     * Test role display names
     */
    public function testGetRoleName(): void
    {
        $this->assertEquals('Administrator', UserRole::getRoleName(UserRole::ADMINISTRATOR));
        $this->assertEquals('Editor', UserRole::getRoleName(UserRole::EDITOR));
        $this->assertEquals('Shop Manager', UserRole::getRoleName(UserRole::SHOP_MANAGER));
        $this->assertEquals('Logged In User', UserRole::getRoleName(UserRole::LOGGED_IN_USER));
        
        // Unknown role returns formatted version
        $this->assertEquals('Unknown Role', UserRole::getRoleName('unknown_role'));
    }
    
    /**
     * Test role descriptions
     */
    public function testGetRoleDescription(): void
    {
        $adminDesc = UserRole::getRoleDescription(UserRole::ADMINISTRATOR);
        $this->assertStringContainsString('Full system access', $adminDesc);
        
        $editorDesc = UserRole::getRoleDescription(UserRole::EDITOR);
        $this->assertStringContainsString('publish and manage posts', $editorDesc);
        
        // Unknown role returns empty string
        $this->assertEquals('', UserRole::getRoleDescription('unknown_role'));
    }
}
