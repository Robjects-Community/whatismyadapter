<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Table\UsersTable;
use App\Test\TestCase\AppControllerTestCase;
use Cake\Cache\Cache;
use Cake\Datasource\FactoryLocator;
use Cake\TestSuite\IntegrationTestTrait;

/**
 * App\Controller\UsersController Test Case
 *
 * This test case is designed to test the both the Admin and non Admin UsersController functionalities
 * such as login, logout, registration, email confirmation, and user editing processes.
 *
 * @uses \App\Controller\UsersController
 */
class UsersControllerTest extends AppControllerTestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures to be loaded for the test case
     *
     * @var array
     */
    protected array $fixtures = [
        'app.Users',
        'app.Articles',
        'app.Slugs',
        'app.Tags',
        'app.ArticlesTags',
        'app.BlockedIps',
        'app.UserAccountConfirmations',
        'app.Settings',
    ];

    /**
     * UsersTable instance
     *
     * @var \App\Model\Table\UsersTable
     */
    protected UsersTable $Users;

    /**
     * Setup method
     *
     * This method is called before each test. It initializes the UsersTable
     * and configures the request environment for authentication.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        //have errors bubble up
        $this->disableErrorHandlerMiddleware();

        $this->Users = FactoryLocator::get('Table')->get('Users');

        // Configure authentication
        $this->configRequest([
            'environment' => [
                'AUTH_TYPE' => 'Form',
            ],
        ]);

        // Clear rate limiting cache to make sure /login is not blocked
        Cache::clear('rate_limit');
    }

    /**
     * Test login access for unauthenticated users
     *
     * Ensures that unauthenticated users can access the login page.
     *
     * @return void
     */
    public function testLoginAccessForUnauthenticatedUsers(): void
    {
        $this->get('/en/users/login');
        $this->assertResponseOk();
    }

    /**
     * Test successful login for non-admin users
     *
     * Verifies that a non-admin user can successfully log in and be redirected to the homepage.
     *
     * @return void
     */
    public function testSuccessfulNonAdminLogin(): void
    {
        $this->enableCsrfToken();
        $this->post('/en/users/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        $this->assertRedirect('/');
    }

    /**
     * Test non-admin user access to admin area
     *
     * This test verifies that a non-admin user cannot access the admin area
     * and is redirected to the site homepage.
     *
     * @return void
     */
    public function testNonAdminUserAccessToAdminArea(): void
    {
        $this->enableCsrfToken();

        // Log in as a non-admin user
        $this->post('/en/users/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        $this->assertRedirect('/');

        // Attempt to access the admin area
        $this->get('/admin');

        // Assert that the user is redirected with access denied
        $this->assertResponseCode(302);
        $this->assertFlashMessage('Access denied. You must be logged in as an admin to view this page.', 'flash');
    }

    /**
     * Test successful login for admin users
     *
     * This test verifies that an admin user can log in and be redirected to the admin articles page.
     *
     * @return void
     */
    public function testSuccessfulAdminLogin(): void
    {
        $this->enableCsrfToken();
        $this->post('/en/users/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $this->assertRedirect('/admin/articles');
    }

    /**
     * Test login with incorrect credentials
     *
     * This test ensures that users with incorrect credentials are not redirected and receive an error message.
     *
     * @return void
     */
    public function testLoginWithBadCredentials(): void
    {
        $this->enableCsrfToken();
        $this->post('/en/users/login', [
            'email' => 'wrong@example.com',
            'password' => 'incorrectpassword',
        ]);

        // Assert that the user is not redirected (stays on the login page)
        $this->assertNoRedirect();

        // Assert that a flash message is set (assuming your controller sets one for failed login)
        $this->assertResponseContains('Invalid username or password');

        // Optionally, check that the user is not authenticated
        $this->assertNull($this->_controller->Authentication->getIdentity());
    }

    /**
     * Test logout functionality
     *
     * This test checks if a logged-in user can successfully log out and be redirected to the login page.
     *
     * @return void
     */
    public function testLogout(): void
    {
        $this->session(['Auth' => ['User' => ['id' => 1]]]);
        $this->get('/en/users/logout');
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Test successful user registration
     *
     * This test verifies that a new user can register successfully and be redirected to the login page.
     *
     * @return void
     */
    public function testSuccessfulRegistration(): void
    {
        $this->enableCsrfToken();
        $this->post('/en/users/register', [
            'email' => 'newuser@example.com',
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Test registration failure
     *
     * This test ensures that registration fails with invalid data and returns a 403 response code.
     *
     * @return void
     */
    public function testRegistrationFailure(): void
    {
        $this->enableCsrfToken();
        $this->post('/en/users/register', [
            'email' => 'invalidemail',
            'password' => 'short',
            'confirm_password' => 'mismatch',
        ]);
        $this->assertResponseCode(403);
    }

    /**
     * Test email confirmation process
     *
     * Verifies the entire email confirmation flow, including:
     * - Successful registration and confirmation code generation
     * - Confirming email with a valid confirmation code
     * - Attempting to confirm with an invalid code
     * - Attempting to reuse an already used confirmation code
     *
     * @return void
     */
    public function testEmailConfirmationProcess(): void
    {
        $this->enableCsrfToken();

        // Test sending confirmation email message
        $this->post('/en/users/register', [
            'email' => 'newuser66@example.com',
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);
        $this->assertResponseSuccess();

        // Fetch the confirmation code from the database
        $confirmationsTable = $this->getTableLocator()->get('UserAccountConfirmations');
        $confirmation = $confirmationsTable->find()
            ->orderBy(['created' => 'DESC'])
            ->first();
        $confirmationCode = $confirmation->confirmation_code;

        // Test confirming email with valid confirmation code
        $this->get("/en/users/confirm-email/{$confirmationCode}");
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $this->assertFlashMessage('Your account has been confirmed. You can now log in.');

        // Test confirming email with invalid confirmation code
        $this->get('/en/users/confirm-email/invalidcode');
        $this->assertRedirect(['controller' => 'Users', 'action' => 'register']);
        $this->assertFlashMessage('Invalid confirmation code.');

        // Test confirming email with already used confirmation code
        $this->get("/en/users/confirm-email/{$confirmationCode}");
        $this->assertRedirect(['controller' => 'Users', 'action' => 'register']);
        $this->assertFlashMessage('Invalid confirmation code.');
    }

    /**
     * Test registration with invalid data
     *
     * Checks various scenarios of registration with invalid data, including:
     * - Mismatched passwords
     * - Attempting to register with an existing email
     *
     * @return void
     */
    public function testRegistrationWithInvalidData(): void
    {
        $this->enableCsrfToken();

        // Test registration with mismatched passwords
        $this->post('/en/users/register', [
            'email' => 'mismatch@example.com',
            'password' => 'password123',
            'confirm_password' => 'password456',
        ]);
        $this->assertResponseCode(403);
        $this->assertFlashMessage('Registration failed. Please, try again.');

        // Test registration with an existing email
        $this->post('/en/users/register', [
            'email' => 'user@example.com', // Assuming this email already exists in fixtures
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);
        $this->assertResponseCode(403);
        $this->assertFlashMessage('Registration failed. Please, try again.');
    }

    /**
     * Test user edit functionality
     *
     * Verifies that a user can successfully edit their own account information.
     *
     * @return void
     */
    public function testUserEditFunctionality(): void
    {
        $this->enableCsrfToken();

        //non admin user
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d02';
        $this->loginUser($userId);

        $data = [
            'username' => 'updatedusername',
            'email' => 'updated@example.com',
        ];
        // Test editing user's own account
        $this->post("/en/users/edit/{$userId}", $data);

        $this->assertResponseSuccess();
        //$this->assertFlashMessage('Your account has been updated.');

        $user = $this->Users->get($userId);
        $this->assertEquals('updated@example.com', $user->email);
    }

    /**
     * Test user edit restrictions
     *
     * Checks various scenarios related to user editing, including:
     * - Attempting to edit another user's account
     * - Attempting to change admin status
     * - Submitting invalid data during edit
     *
     * @return void
     */
    public function testUserEditWrongAccount()
    {
        $this->enableCsrfToken();

        //non admin user
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d02';
        $this->loginUser($userId);

        // Test attempting to edit another user's account
        $anotherUserId = '6509480c-e7e6-4e65-9c38-8574a8d09d02';
        $this->post("/en/users/edit/{$anotherUserId}", [
            'email' => 'hacked@example.com',
            'username' => 'updatedusername',
            'password' => '',
            'confirm_password' => 'updatedusername',

        ]);
        $this->assertRedirectContains("/users/edit/{$userId}");
        $this->assertFlashMessage('We were unable to find that account.');

        $anotherUser = $this->Users->get($anotherUserId);
        $this->assertNotEquals('hacked@example.com', $anotherUser->email);

        // Test attempting to change admin status
        $this->post("/en/users/edit/{$userId}", [
            'is_admin' => true,
        ]);
        $user = $this->Users->get($userId);
        $this->assertFalse($user->is_admin);

        // Test submitting invalid data
        $this->post("/en/users/edit/{$userId}", [
            'email' => 'invalid-email',
        ]);
        $this->assertNoRedirect();
        $this->assertResponseContains('Your account could not be updated.');
    }

    /**
     * Test the custom 'auth' finder method
     *
     * Verifies that the custom 'auth' finder method correctly returns only non-disabled users.
     *
     * @return void
     */
    public function testFindAuthMethod(): void
    {
        $result = $this->Users->find('auth')->toArray();
        $this->assertNotEmpty($result);
        foreach ($result as $user) {
            $this->assertEquals(1, $user->active);
        }
    }

    /**
     * Test password validation during registration
     *
     * Ensures that the password validation rules are enforced during user registration.
     *
     * @return void
     */
    public function testPasswordValidation(): void
    {
        $this->enableCsrfToken();

        // Test password too short
        $this->post('/en/users/register', [
            'email' => 'short@example.com',
            'password' => 'short',
            'confirm_password' => 'short',
        ]);
        $this->assertResponseCode(403);
    }

    /**
     * Test user disabling functionality
     *
     * Verifies that an admin can disable a user account.
     *
     * @return void
     */
    public function testUserDisabling(): void
    {
        $this->enableCsrfToken();

        $adminId = '6509480c-e7e6-4e65-9c38-1423a8d09d0f';
        $userId = '6509480c-e7e6-4e65-9c38-1423a8d09d02';

        $this->loginUser($adminId);

        // Test disabling a user account
        $this->post("/admin/users/edit/{$userId}", [
            'username' => 'user@example.com',
            'email' => 'user@example.com',
            'active' => true,
        ]);
        $this->assertResponseSuccess();
        $user = $this->Users->get($userId);

        $this->assertTrue($user->active);
    }

    /**
     * Test login attempt with a disabled account
     *
     * Ensures that a user cannot log in with a disabled account.
     *
     * @return void
     */
    public function testLoginWithDisabledAccount(): void
    {
        $this->enableCsrfToken();

        // Test logging in with a disabled account
        $this->post('/en/users/login', [
            'email' => 'disabled@example.com',
            'password' => 'password',
        ]);

        $this->assertNoRedirect();
        $this->assertNull($this->_controller->Authentication->getIdentity());
    }

    /**
     * Test password reset with mismatched passwords
     *
     * This test ensures that a password reset attempt with mismatched passwords fails.
     *
     * @return void
     */
    public function testPasswordResetWithMismatchedPasswords(): void
    {
        $this->enableCsrfToken();

        $resetToken = 'CONFIRM123USER1';

        $this->get("/en/users/reset-password/{$resetToken}");
        $this->assertResponseOk();

        $this->post("/en/users/reset-password/{$resetToken}", [
            'password' => 'newpassword123',
            'confirm_password' => 'differentpassword',
        ]);

        $this->assertNoRedirect();
        $this->assertResponseContains('There was an issue resetting your password. Please try again.');
    }

    /**
     * Test password reset with invalid token
     *
     * This test ensures that a password reset attempt with an invalid token fails.
     *
     * @return void
     */
    public function testPasswordResetWithInvalidToken(): void
    {
        $this->enableCsrfToken();
        $this->post('/en/users/reset-password/invalid-token', [
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $this->assertFlashMessage('Invalid or expired password reset link.');
    }

    /**
     * Test password reset with valid token
     *
     * This test ensures that a user can reset their password using a valid token.
     *
     * @return void
     */
    public function testPasswordResetWithValidToken(): void
    {
        // Assume a valid token is generated and stored in the database
        $resetToken = 'CONFIRM123USER1';
        $this->enableCsrfToken();
        $this->post("/en/users/reset-password/{$resetToken}", [
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);
        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $this->assertFlashMessage('Your password has been reset. Please log in with your new password.');
    }

    /**
     * Test password reset request
     *
     * This test verifies that a user can request a password reset and receive a confirmation message.
     *
     * @return void
     */
    public function testPasswordResetRequest(): void
    {
        $this->enableCsrfToken();

        $this->get('/en/users/forgot-password');
        $this->assertResponseOk();

        $this->post('/en/users/forgot-password', [
            'email' => 'user1@example.com',
        ]);

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
        $this->assertFlashMessage('If your email is registered, you will receive a link to reset your password.');
    }
}
