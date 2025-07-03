<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use App\Model\Entity\UserAccountConfirmation;
use App\Utility\SettingsManager;
use Authentication\IdentityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\Log\LogTrait;
use Cake\Queue\QueueManager;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Exception;

/**
 * Users Controller
 *
 * Handles user-related operations such as registration, login, logout, and account management.
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use LogTrait;

    /**
     * Configures actions that can be accessed without authentication.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(
            [
                'login',
                'logout',
                'register',
                'confirmEmail',
                'forgotPassword',
                'resetPassword',
            ],
        );
    }

    /**
     * Handles user login functionality.
     *
     * Authenticates the user and redirects them based on their role and previous page.
     *
     * @return \Cake\Http\Response|null Redirects on successful login, or null on failure.
     */
    public function login(): ?Response
    {
        $result = $this->Authentication->getResult();
        if ($result != null && $result->isValid()) {
            $identity = $this->Authentication->getIdentity();
            $this->handleCookieConsent($identity);
            $user = $this->Users->get($identity->getIdentifier());

            return $this->getRedirectResponse($user);
        }

        if ($this->request->is('post')) {
            $this->Flash->error(__('Invalid username or password'));
        }

        return null;
    }

    /**
     * Determines and returns the appropriate redirect response based on user role.
     *
     * This method handles post-login redirection by checking if the user is an admin.
     * Admin users are redirected to the admin articles section, while regular users
     * are redirected to either their intended destination or the homepage.
     *
     * @param \App\Model\Entity\User $user The authenticated user entity
     * @return \Cake\Http\Response The redirect response object
     */
    private function getRedirectResponse(User $user): Response
    {
        // Send admin users to admin area
        if ($user->is_admin) {
            return $this->redirect('/admin/articles');
        }
        // send everyone else to non admin
        $target = $this->Authentication->getLoginRedirect() ?? '/';

        return $this->redirect($target);
    }

    /**
     * Handles cookie consent management on login for users.
     *
     * This method manages the cookie consent process by:
     * - Checking for existing consent cookies
     * - Validating consent against the current user
     * - Updating consent records when user IDs don't match
     * - Setting or expiring consent cookies as needed
     * - Making consent data available to the view
     *
     * @param \Authentication\IdentityInterface $user The authenticated user entity
     * @return void
     * @throws \Cake\Database\Exception\DatabaseException When database operations fail
     * @throws \Cake\Http\Exception\InvalidCsrfTokenException When CSRF validation fails
     * @throws \RuntimeException When cookie operations fail
     */
    private function handleCookieConsent(IdentityInterface $user): void
    {
        $sessionId = $this->request->getSession()->id();
        $consentTable = $this->fetchTable('CookieConsents');
        $consentCookie = $this->request->getCookie('consent_cookie');
        $consentData = null;

        if ($consentCookie) {
            $consentCookie = json_decode($consentCookie, true);
            if ($consentCookie['user_id'] != $user->getIdentifier()) {
                $consent = $consentTable->getLatestConsent($sessionId, $user->getIdentifier());
                if ($consent) {
                    $consent['user_id'] = $user->getIdentifier();
                    $consent = $consentTable->newEntity($consent);
                    if ($consentCookie['user_id'] == null) {
                        $consentTable->save($consent);
                    }
                    $cookie = $consentTable->createConsentCookie($consent);
                    $this->response = $this->response->withCookie($cookie);
                    $consentCookie = $this->request->getCookie('consent_cookie');
                    // Set the cookie data to the view
                    $consentData = json_decode($consentCookie, true);
                    $this->set('consentData', $consentData);
                } else {
                    $this->response = $this->response->withExpiredCookie(new Cookie('consent_cookie'));
                    $this->set('consentData', null);
                }
            }
        } else {
            $this->set('consentData', null);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return \Cake\Http\Response|null Redirects to the login page.
     */
    public function logout(): ?Response
    {
        $this->Authentication->logout();

        return $this->redirect(['_name' => 'login', 'prefix' => false]);
    }

    /**
     * Handles user registration process.
     *
     * Creates a new user account and sends a confirmation email.
     *
     * @return \Cake\Http\Response|null Redirects on successful registration, or null on failure.
     */
    public function register(): ?Response
    {
        if (!SettingsManager::read('Users.registrationEnabled', false)) {
            return $this->redirect($this->referer());
        }

        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            // Set username to be the same as email
            $data['username'] = $data['email'];

            $user = $this->Users->patchEntity($user, $data);
            // Be super certain is_admin is false for new registrations
            $user->is_admin = false;
            $user->setAccess('is_admin', false);
            $user->active = true;
            $user->setAccess('active', true);

            if ($this->Users->save($user)) {
                $confirmationsTable = $this->fetchTable('UserAccountConfirmations');
                $confirmation = $confirmationsTable->newEntity([
                    'user_id' => $user->id,
                    'confirmation_code' => Text::uuid(),
                ]);

                if ($confirmationsTable->save($confirmation)) {
                    $this->Flash->success(__('Registration successful. Please check your email for confirmation.'));
                    $this->sendConfirmationEmailMessage($user, $confirmation);
                } else {
                    $this->Flash->error(
                        __('Registration successful, but there was an issue creating the confirmation link.'),
                    );
                }

                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error(__('Registration failed. Please, try again.'));

                return $this->response->withStatus(403);
            }
        }
        $this->set(compact('user'));

        return null;
    }

    /**
     * Sends a confirmation email to the user.
     *
     * @param \App\Model\Entity\User $user The user entity.
     * @param \App\Model\Entity\UserAccountConfirmation $confirmation The confirmation entity.
     * @return void
     */
    private function sendConfirmationEmailMessage(User $user, UserAccountConfirmation $confirmation): void
    {
        if (env('CAKE_ENV') === 'test') {
            return;
        }

        try {
            $data = [
                'template_identifier' => 'confirm_email',
                'from' => SettingsManager::read('Email.reply_email', 'noreply@example.com'),
                'to' => $user->email,
                'viewVars' => [
                    'username' => $user->username,
                    'confirmation_code' => $confirmation->confirmation_code,
                    'confirm_email_link' => Router::url([
                        'controller' => 'Users',
                        'action' => 'confirmEmail',
                        $confirmation->confirmation_code,
                    ], true),
                ],
            ];

            QueueManager::push('App\Job\SendEmailJob', $data);
        } catch (Exception $e) {
            Log::error(__('Failed to send confirmation email message: {0}', $e->getMessage()));
        }
    }

    /**
     * Allows a user to edit their own account information.
     *
     * @param string|null $id The ID of the user to be edited.
     * @return \Cake\Http\Response|null Redirects after editing, or null on GET requests.
     */
    public function edit(?string $id = null): ?Response
    {
        $currentUserId = $this->Authentication->getIdentity()->getIdentifier();

        if ($id !== $currentUserId) {
            $this->log('Unauthorized access attempt to edit another user\'s account', 'warning', [
                'group_name' => 'unauthorized_user_edit_attempt',
                'user_id' => $currentUserId,
                'attempted_user_id' => $id,
                'url' => $this->request->getRequestTarget(),
                'ip' => $this->request->clientIp(),
                'scope' => ['user'],
            ]);
            $this->Flash->error(__('We were unable to find that account.'));

            return $this->redirect(['_name' => 'account', $currentUserId]);
        }

        $user = $this->Users->get($this->Authentication->getIdentity()->getIdentifier(), contain: []);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user->setAccess('is_admin', false);
            $user->setAccess('active', false);
            $data = $this->request->getData();
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your account has been updated.'));
            } else {
                $this->Flash->error(__('Your account could not be updated.'));
            }
        }
        $this->set(compact('user'));

        return null;
    }

    /**
     * Confirms a user's email address using a confirmation code.
     *
     * @param string $confirmationCode The confirmation code to validate.
     * @return \Cake\Http\Response|null Redirects after confirmation attempt.
     */
    public function confirmEmail(string $confirmationCode): ?Response
    {
        $confirmationsTable = $this->fetchTable('UserAccountConfirmations');
        $confirmation = $confirmationsTable->find()
            ->where(['confirmation_code' => $confirmationCode])
            ->first();

        if ($confirmation) {
            $user = $this->Users->get($confirmation->user_id);
            $user->setAccess('active', true);
            $user->active = true;

            if ($this->Users->save($user)) {
                $confirmationsTable->delete($confirmation);
                $this->Flash->success(__('Your account has been confirmed. You can now log in.'));

                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error(__('There was an issue confirming your account. Please try again.'));

                return $this->redirect(['action' => 'register']);
            }
        } else {
            $this->Flash->error(__('Invalid confirmation code.'));

            return $this->redirect(['action' => 'register']);
        }

        return null;
    }

    /**
     * Handles the forgot password functionality.
     *
     * Allows users to request a password reset link via email.
     *
     * @return \Cake\Http\Response|null Redirects on successful request, or null on failure.
     */
    public function forgotPassword(): ?Response
    {
        if (!SettingsManager::read('Users.registrationEnabled', false)) {
            return $this->redirect($this->referer());
        }

        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $user = $this->Users->findByEmail($email)->first();

            if ($user) {
                $confirmationsTable = $this->fetchTable('UserAccountConfirmations');
                $confirmation = $confirmationsTable->newEntity([
                    'user_id' => $user->id,
                    'confirmation_code' => Text::uuid(),
                ]);

                if ($confirmationsTable->save($confirmation)) {
                    $this->sendPasswordResetEmail($user, $confirmation);
                    $this->Flash->success(__(
                        'If your email is registered, you will receive a link to reset your password.',
                    ));

                    return $this->redirect(['action' => 'login']);
                }
            }
            $this->Flash->success(__('If your email is registered, you will receive a link to reset your password.'));
        }

        return null;
    }

    /**
     * Sends a password reset email to the user.
     *
     * @param \App\Model\Entity\User $user The user entity.
     * @param \App\Model\Entity\UserAccountConfirmation $confirmation The confirmation entity.
     * @return void
     */
    private function sendPasswordResetEmail(User $user, UserAccountConfirmation $confirmation): void
    {
        if (env('CAKE_ENV') === 'test') {
            return;
        }

        try {
            $data = [
                'template_identifier' => 'reset_password',
                'from' => SettingsManager::read('Email.reply_email', 'noreply@example.com'),
                'to' => $user->email,
                'viewVars' => [
                    'username' => $user->username,
                    'reset_password_link' => Router::url([
                        '_name' => 'reset-password',
                        $confirmation->confirmation_code,
                    ], true),
                ],
            ];

            QueueManager::push('App\Job\SendEmailJob', $data);
        } catch (Exception $e) {
            Log::error(__('Failed to send password reset email: {0}', $e->getMessage()));
        }
    }

    /**
     * Handles the password reset functionality.
     *
     * Allows users to reset their password using a valid confirmation code.
     *
     * @param string $confirmationCode The confirmation code from the password reset link.
     * @return \Cake\Http\Response|null Redirects after successful password reset, or null on failure.
     */
    public function resetPassword(string $confirmationCode): ?Response
    {
        if (!SettingsManager::read('Users.registrationEnabled', false)) {
            return $this->redirect($this->referer());
        }

        $confirmationsTable = $this->fetchTable('UserAccountConfirmations');
        $confirmation = $confirmationsTable->find()
            ->where(['confirmation_code' => $confirmationCode])
            ->first();

        if (!$confirmation) {
            $this->Flash->error(__('Invalid or expired password reset link.'));

            return $this->redirect(['action' => 'login']);
        }

        $user = $this->Users->get($confirmation->user_id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user->setAccess('is_admin', false);
            $user->setAccess('active', false);
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'validate' => 'resetPassword',
            ]);

            if ($this->Users->save($user)) {
                $confirmationsTable->delete($confirmation);
                $this->Flash->success(__('Your password has been reset. Please log in with your new password.'));

                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error(__('There was an issue resetting your password. Please try again.'));
            }
        }

        $this->set(compact('user', 'confirmationCode'));

        return null;
    }
}
