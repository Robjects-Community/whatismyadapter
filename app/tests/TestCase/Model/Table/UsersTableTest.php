<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Enum\Role;
use App\Model\Table\UsersTable;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

/**
 * App\Model\Table\UsersTable Test Case
 *
 * Comprehensive test suite for UsersTable including:
 * - Validation tests (default, register, resetPassword)
 * - Authentication and authorization
 * - Custom finder methods
 * - Association tests
 * - Business rules validation
 */
class UsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Articles',
        'app.Comments',
        'app.UserAccountConfirmations',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Users') ? [] : ['className' => UsersTable::class];
        $this->Users = $this->getTableLocator()->get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Users);

        parent::tearDown();
    }

    // ============================================================
    // Initialization Tests
    // ============================================================

    /**
     * Test initialize method sets up table correctly
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertEquals('users', $this->Users->getTable());
        $this->assertEquals('username', $this->Users->getDisplayField());
        $this->assertEquals('id', $this->Users->getPrimaryKey());
        
        // Test behaviors are attached
        $this->assertTrue($this->Users->hasBehavior('QueueableImage'));
        $this->assertTrue($this->Users->hasBehavior('Timestamp'));
        
        // Test associations
        $this->assertTrue($this->Users->hasAssociation('Articles'));
        $this->assertTrue($this->Users->hasAssociation('Comments'));
    }

    // ============================================================
    // Validation Tests - Default
    // ============================================================

    /**
     * Test validationDefault with valid data
     *
     * @return void
     */
    public function testValidationDefaultSuccess(): void
    {
        $data = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
            'role' => Role::USER->value,
            'active' => 1,
        ];
        
        $user = $this->Users->newEntity($data);
        $this->assertEmpty($user->getErrors(), 'Expected no validation errors');
    }

    /**
     * Test validationDefault requires username
     *
     * @return void
     */
    public function testValidationDefaultUsernameRequired(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
        ];
        
        $user = $this->Users->newEntity($data);
        $this->assertNotEmpty($user->getError('username'));
        $this->assertArrayHasKey('_required', $user->getError('username'));
    }

    /**
     * Test validationDefault username max length
     *
     * @return void
     */
    public function testValidationDefaultUsernameMaxLength(): void
    {
        $data = [
            'username' => str_repeat('a', 51), // Exceeds 50 char limit
            'email' => 'test@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
        ];
        
        $user = $this->Users->newEntity($data);
        $this->assertNotEmpty($user->getError('username'));
        $this->assertArrayHasKey('maxLength', $user->getError('username'));
    }

    /**
     * Test validationDefault requires email
     *
     * @return void
     */
    public function testValidationDefaultEmailRequired(): void
    {
        $data = [
            'username' => 'testuser',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
        ];
        
        $user = $this->Users->newEntity($data);
        $this->assertNotEmpty($user->getError('email'));
    }

    /**
     * Test validationDefault validates email format
     *
     * @return void
     */
    public function testValidationDefaultEmailFormat(): void
    {
        $data = [
            'username' => 'testuser',
            'email' => 'not-an-email',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
        ];
        
        $user = $this->Users->newEntity($data);
        $this->assertNotEmpty($user->getError('email'));
        $this->assertArrayHasKey('email', $user->getError('email'));
    }

    /**
     * Test validationDefault password minimum length
     *
     * @return void
     */
    public function testValidationDefaultPasswordMinLength(): void
    {
        $data = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'short',
            'confirm_password' => 'short',
        ];
        
        $user = $this->Users->newEntity($data);
        $this->assertNotEmpty($user->getError('password'));
        $this->assertArrayHasKey('minLength', $user->getError('password'));
    }

    /**
     * Test validationDefault password confirmation match
     *
     * @return void
     */
    public function testValidationDefaultPasswordConfirmationMatch(): void
    {
        $data = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'DifferentPass123',
        ];
        
        $user = $this->Users->newEntity($data);
        $this->assertNotEmpty($user->getError('confirm_password'));
        $this->assertArrayHasKey('sameAs', $user->getError('confirm_password'));
    }

    /**
     * Test validationDefault role validation
     *
     * @return void
     */
    public function testValidationDefaultRoleValidValues(): void
    {
        // Test valid roles
        foreach (Role::cases() as $role) {
            $data = [
                'username' => 'testuser_' . $role->value,
                'email' => "test_{$role->value}@example.com",
                'password' => 'SecurePass123',
                'confirm_password' => 'SecurePass123',
                'role' => $role->value,
            ];
            
            $user = $this->Users->newEntity($data);
            $this->assertEmpty($user->getError('role'), "Role {$role->value} should be valid");
        }
    }

    /**
     * Test validationDefault role invalid value
     *
     * @return void
     */
    public function testValidationDefaultRoleInvalidValue(): void
    {
        $data = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
            'role' => 'invalid_role',
        ];
        
        $user = $this->Users->newEntity($data);
        $this->assertNotEmpty($user->getError('role'));
        $this->assertArrayHasKey('inList', $user->getError('role'));
    }

    /**
     * Test validationDefault allows empty password on update
     *
     * @return void
     */
    public function testValidationDefaultPasswordOptionalOnUpdate(): void
    {
        // Create user first
        $user = $this->Users->get('90d91e66-5d90-412b-aeaa-4d51fa110794');
        
        // Update without password should be valid
        $user = $this->Users->patchEntity($user, [
            'username' => 'updateduser',
        ]);
        
        $this->assertEmpty($user->getError('password'), 'Password should be optional on update');
    }

    // ============================================================
    // Validation Tests - Register
    // ============================================================

    /**
     * Test validationRegister with valid data
     *
     * @return void
     */
    public function testValidationRegisterSuccess(): void
    {
        $data = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
        ];
        
        $user = $this->Users->newEntity($data, ['validate' => 'register']);
        $this->assertEmpty($user->getErrors(), 'Expected no validation errors');
    }

    /**
     * Test validationRegister requires all fields
     *
     * @return void
     */
    public function testValidationRegisterRequiredFields(): void
    {
        $data = [];
        
        $user = $this->Users->newEntity($data, ['validate' => 'register']);
        
        $this->assertNotEmpty($user->getError('username'));
        $this->assertNotEmpty($user->getError('email'));
        $this->assertNotEmpty($user->getError('password'));
        $this->assertNotEmpty($user->getError('confirm_password'));
    }

    /**
     * Test validationRegister password confirmation
     *
     * @return void
     */
    public function testValidationRegisterPasswordMatch(): void
    {
        $data = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'DifferentPass',
        ];
        
        $user = $this->Users->newEntity($data, ['validate' => 'register']);
        $this->assertNotEmpty($user->getError('confirm_password'));
    }

    // ============================================================
    // Validation Tests - Reset Password
    // ============================================================

    /**
     * Test validationResetPassword with valid data
     *
     * @return void
     */
    public function testValidationResetPasswordSuccess(): void
    {
        $data = [
            'password' => 'NewSecurePass456',
            'confirm_password' => 'NewSecurePass456',
        ];
        
        $user = $this->Users->newEntity($data, ['validate' => 'resetPassword']);
        $this->assertEmpty($user->getErrors(), 'Expected no validation errors');
    }

    /**
     * Test validationResetPassword requires password
     *
     * @return void
     */
    public function testValidationResetPasswordRequired(): void
    {
        $data = [];
        
        $user = $this->Users->newEntity($data, ['validate' => 'resetPassword']);
        
        $this->assertNotEmpty($user->getError('password'));
        $this->assertNotEmpty($user->getError('confirm_password'));
    }

    /**
     * Test validationResetPassword minimum length
     *
     * @return void
     */
    public function testValidationResetPasswordMinLength(): void
    {
        $data = [
            'password' => 'short',
            'confirm_password' => 'short',
        ];
        
        $user = $this->Users->newEntity($data, ['validate' => 'resetPassword']);
        $this->assertNotEmpty($user->getError('password'));
        $this->assertArrayHasKey('minLength', $user->getError('password'));
    }

    /**
     * Test validationResetPassword passwords must match
     *
     * @return void
     */
    public function testValidationResetPasswordMustMatch(): void
    {
        $data = [
            'password' => 'NewSecurePass456',
            'confirm_password' => 'DifferentPassword',
        ];
        
        $user = $this->Users->newEntity($data, ['validate' => 'resetPassword']);
        $this->assertNotEmpty($user->getError('confirm_password'));
        $this->assertArrayHasKey('sameAs', $user->getError('confirm_password'));
    }

    // ============================================================
    // Business Rules Tests
    // ============================================================

    /**
     * Test buildRules enforces unique username
     *
     * @return void
     */
    public function testBuildRulesUsernameUnique(): void
    {
        // First user with this username already exists in fixture
        $data = [
            'username' => 'Lorem ipsum dolor sit amet', // Same as fixture
            'email' => 'unique@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
        ];
        
        $user = $this->Users->newEntity($data);
        $result = $this->Users->save($user);
        
        $this->assertFalse($result, 'Save should fail for duplicate username');
        $this->assertNotEmpty($user->getError('username'));
    }

    /**
     * Test buildRules enforces unique email
     *
     * @return void
     */
    public function testBuildRulesEmailUnique(): void
    {
        // First user with this email already exists in fixture
        $data = [
            'username' => 'uniqueuser',
            'email' => 'Lorem ipsum dolor sit amet', // Same as fixture
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
        ];
        
        $user = $this->Users->newEntity($data);
        $result = $this->Users->save($user);
        
        $this->assertFalse($result, 'Save should fail for duplicate email');
        $this->assertNotEmpty($user->getError('email'));
    }

    // ============================================================
    // Custom Finder Tests
    // ============================================================

    /**
     * Test findAuth returns only active users
     *
     * @return void
     */
    public function testFindAuthReturnsOnlyActiveUsers(): void
    {
        // Create inactive user
        $inactiveUser = $this->Users->newEntity([
            'username' => 'inactiveuser',
            'email' => 'inactive@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
            'active' => 0,
        ]);
        $this->Users->save($inactiveUser);
        
        // Find with auth finder
        $results = $this->Users->find('auth')->all();
        
        // Should only return active users
        foreach ($results as $user) {
            $this->assertEquals(1, $user->active, 'findAuth should only return active users');
        }
    }

    /**
     * Test findAuth excludes inactive users
     *
     * @return void
     */
    public function testFindAuthExcludesInactiveUsers(): void
    {
        // Create both active and inactive users
        $activeUser = $this->Users->newEntity([
            'username' => 'activeuser',
            'email' => 'active@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
            'active' => 1,
        ]);
        $this->Users->save($activeUser);
        
        $inactiveUser = $this->Users->newEntity([
            'username' => 'inactiveuser2',
            'email' => 'inactive2@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
            'active' => 0,
        ]);
        $this->Users->save($inactiveUser);
        
        // Count with auth finder
        $authCount = $this->Users->find('auth')->count();
        $allCount = $this->Users->find()->count();
        
        $this->assertLessThan($allCount, $authCount, 'Auth count should be less than total count');
    }

    // ============================================================
    // Association Tests
    // ============================================================

    /**
     * Test Users has many Articles association
     *
     * @return void
     */
    public function testUsersHasManyArticles(): void
    {
        $user = $this->Users->get('90d91e66-5d90-412b-aeaa-4d51fa110794', [
            'contain' => ['Articles'],
        ]);
        
        $this->assertIsArray($user->articles);
        $this->assertTrue($this->Users->hasAssociation('Articles'));
    }

    /**
     * Test Users has many Comments association
     *
     * @return void
     */
    public function testUsersHasManyComments(): void
    {
        $user = $this->Users->get('90d91e66-5d90-412b-aeaa-4d51fa110794', [
            'contain' => ['Comments'],
        ]);
        
        $this->assertIsArray($user->comments);
        $this->assertTrue($this->Users->hasAssociation('Comments'));
    }

    // ============================================================
    // CRUD Operation Tests
    // ============================================================

    /**
     * Test successful user creation
     *
     * @return void
     */
    public function testCreateUserSuccess(): void
    {
        $data = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'SecurePass123',
            'confirm_password' => 'SecurePass123',
            'role' => Role::USER->value,
            'active' => 1,
        ];
        
        $user = $this->Users->newEntity($data);
        $result = $this->Users->save($user);
        
        $this->assertInstanceOf('App\Model\Entity\User', $result);
        $this->assertNotEmpty($result->id);
        $this->assertEquals('newuser', $result->username);
        $this->assertEquals('newuser@example.com', $result->email);
    }

    /**
     * Test updating existing user
     *
     * @return void
     */
    public function testUpdateUserSuccess(): void
    {
        $user = $this->Users->get('90d91e66-5d90-412b-aeaa-4d51fa110794');
        
        $user = $this->Users->patchEntity($user, [
            'username' => 'updatedusername',
        ]);
        
        $result = $this->Users->save($user);
        
        $this->assertNotFalse($result);
        $this->assertEquals('updatedusername', $result->username);
    }

    /**
     * Test deleting user
     *
     * @return void
     */
    public function testDeleteUserSuccess(): void
    {
        $user = $this->Users->get('90d91e66-5d90-412b-aeaa-4d51fa110794');
        
        $result = $this->Users->delete($user);
        
        $this->assertTrue($result);
        
        // Verify user is deleted
        $exists = $this->Users->exists(['id' => '90d91e66-5d90-412b-aeaa-4d51fa110794']);
        $this->assertFalse($exists);
    }
}
