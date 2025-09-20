<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\User;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\User Test Case
 */
class UserTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\User
     */
    protected $User;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->User = new User();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->User);

        parent::tearDown();
    }

    /**
     * Test to ensure that the 'is_admin' field cannot be changed during the save process.
     *
     * This test verifies that when a new user entity is created with 'is_admin' set to 1,
     * the 'is_admin' field is reset to 0 by default and remains 0 after saving the entity.
     *
     * Steps:
     * 1. Create a new user entity with 'is_admin' set to 1.
     * 2. Assert that 'is_admin' is 0 after entity creation, indicating default behavior.
     * 3. Save the user entity to the database.
     * 4. Assert that the save operation was successful.
     * 5. Retrieve the saved user from the database.
     * 6. Assert that 'is_admin' is still 0 after saving, ensuring it cannot be changed.
     *
     * @return void
     */
    public function testIsAdminCannotBeChangedOnSave()
    {
        $usersTable = $this->getTableLocator()->get('Users');

        $userData = [
            'username' => 'testuser',
            'password' => 'password123',
            'confirm_password' => 'password123', // Add this line
            'email' => 'test@example.com',
            'is_admin' => 1,
        ];

        $user = $usersTable->newEntity($userData);

        // Assert that is_admin is 0 after entity creation
        $this->assertEquals(0, $user->is_admin, 'is_admin should be 0 by default');

        // Save the user
        $result = $usersTable->save($user);

        // Assert that the save was successful
        $this->assertNotFalse($result, 'User should be saved successfully');

        // Fetch the user from the database to ensure the saved state
        $savedUser = $usersTable->get($user->id);

        // Assert that is_admin is still 0 after saving
        $this->assertEquals(0, $savedUser->is_admin, 'is_admin should remain 0 after saving');
    }

    /**
     * Test password hashing functionality.
     *
     * This test ensures that when a plain text password is assigned to a user,
     * it is stored in a hashed format. It also verifies that the hashed password
     * can be correctly checked against the original plain text password.
     *
     * The test performs the following steps:
     * 1. Creates a new User entity.
     * 2. Assigns a plain text password to the user.
     * 3. Verifies that the stored password is not the same as the plain text (i.e., it's hashed).
     * 4. Checks that the hashed password can be verified against the original plain text.
     *
     * @return void
     */
    public function testPasswordHashing()
    {
        $user = new User();
        $plainPassword = 'testPassword123';
        $user->password = $plainPassword;
        $this->assertNotEquals($plainPassword, $user->password, 'Password should be hashed');
        $this->assertTrue((new DefaultPasswordHasher())->check($plainPassword, $user->password), 'Hashed password should be verifiable');
    }

    /**
     * Test the lockAdminAccountError method
     *
     * @return void
     */
    public function testLockAdminAccountError(): void
    {
        $user = new User(['id' => '1']);

        // Test trying to set is_admin false for your own account
        $result = $user->lockAdminAccountError('1', ['is_admin' => false]);
        $this->assertTrue($result);
    }

    /**
     * Test the lockEnabledAccountError method
     *
     * @return void
     */
    public function lockEnabledAccountError(): void
    {
        $user = new User(['id' => '1']);

        // Test trying to set is_admin false for your own account
        $result = $user->lockEnabledAccountError('1', ['active' => false]);
        $this->assertTrue($result);
    }
}
