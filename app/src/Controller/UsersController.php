<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\Mailer\Mailer;
use Cake\Utility\Security;
use Cake\Routing\Router;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Authentication->allowUnauthenticated([
            'login',
            'logout',
            'register',
            'forgotPassword',
            'resetPassword',
            'confirmEmail',
        ]);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Users->find();
        $users = $this->paginate($query);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $user = $this->Users->get($id, contain: ['Articles', 'Comments']);
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $user = $this->Users->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Login method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful login, renders view otherwise.
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            $this->Flash->success(__('Login successful'));

            // Get the authenticated user
            $user = $this->Authentication->getIdentity();

            // Check if user is admin and redirect accordingly
            if ($user && $user->is_admin) {
                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index']);
            }

            // Check for a stored redirect URL
            $redirect = $this->Authentication->getLoginRedirect();
            if ($redirect) {
                return $this->redirect($redirect);
            }

            // Default redirect for regular users
            return $this->redirect(['controller' => 'Articles', 'action' => 'index']);
        }

        // Display error if user submitted and authentication failed
        if ($this->request->is('post')) {
            $this->Flash->error(__('Invalid username or password'));
        }
    }

    /**
     * Logout method
     *
     * @return \Cake\Http\Response Redirects after logout.
     */
    public function logout(): Response
    {
        $this->Authentication->logout();
        $this->Flash->success(__('You have been logged out.'));

        return $this->redirect(['controller' => 'Articles', 'action' => 'index']);
    }

    /**
     * Register method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful registration, renders view otherwise.
     */
    public function register()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'validate' => 'register'
            ]);
            
            // Set default values for new users
            $user->is_admin = false;
            $user->active = true;
            $user->role = 'user'; // Set default role

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Registration successful. You can now log in.'));

                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('Registration failed. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Forgot password method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function forgotPassword()
    {
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            if ($email) {
                $user = $this->Users->findByEmail($email)->first();
                
                if ($user) {
                    // Generate a secure reset token
                    $resetToken = bin2hex(Security::randomBytes(32));
                    $expiryTime = new \DateTime('+24 hours');
                    
                    // Save the reset token to the user
                    $user->reset_token = $resetToken;
                    $user->reset_token_expires = $expiryTime;
                    
                    if ($this->Users->save($user)) {
                        // Send password reset email
                        $resetUrl = Router::url([
                            '_name' => 'reset-password',
                            'confirmationCode' => $resetToken
                        ], true);
                        
                        try {
                            // Use mailpit for local development, otherwise use default transport
                            $transport = env('DEBUG', false) ? 'mailpit' : env('EMAIL_DEFAULT_TRANSPORT', 'gmail');
                            $mailer = new Mailer();
                            
                            $emailContent = "Hello " . ($user->username ?: $user->email) . ",\n\n" .
                                           "You have requested a password reset for your account.\n\n" .
                                           "Please click the following link to reset your password:\n" .
                                           $resetUrl . "\n\n" .
                                           "This link will expire in 24 hours.\n\n" .
                                           "If you did not request this password reset, please ignore this email.\n\n" .
                                           "Best regards,\n" .
                                           env('APP_NAME', 'WillowCMS');
                            
                            $mailer
                                ->setTransport($transport)
                                ->setTo($email)
                                ->setFrom([env('EMAIL_FROM_ADDRESS', 'noreply@willowcms.app') => env('EMAIL_FROM_NAME', 'WillowCMS')])
                                ->setSubject('Password Reset Request - ' . env('APP_NAME', 'WillowCMS'))
                                ->deliver($emailContent);
                                
                            $this->Flash->success(__('If the email exists in our system, you will receive a password reset link.'));
                        } catch (\Exception $e) {
                            // Log the error but don't reveal it to the user
                            $this->log('Password reset email failed: ' . $e->getMessage(), 'error');
                            $this->Flash->success(__('If the email exists in our system, you will receive a password reset link.'));
                        }
                    } else {
                        $this->Flash->error(__('There was an error processing your request. Please try again.'));
                        return;
                    }
                } else {
                    // Don't reveal that the email doesn't exist
                    $this->Flash->success(__('If the email exists in our system, you will receive a password reset link.'));
                }

                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('Please enter a valid email address.'));
        }
    }

    /**
     * Reset password method
     *
     * @param string|null $confirmationCode
     * @return \Cake\Http\Response|null|void
     */
    public function resetPassword(?string $confirmationCode = null)
    {
        if (!$confirmationCode) {
            $this->Flash->error(__('Invalid reset link.'));
            return $this->redirect(['action' => 'login']);
        }

        // Find user by reset token
        $user = $this->Users->find()
            ->where([
                'reset_token' => $confirmationCode,
                'reset_token_expires >' => new \DateTime()
            ])
            ->first();

        if (!$user) {
            $this->Flash->error(__('Invalid or expired reset link. Please request a new password reset.'));
            return $this->redirect(['action' => 'forgotPassword']);
        }

        if ($this->request->is('post')) {
            $password = $this->request->getData('password');
            $confirmPassword = $this->request->getData('confirm_password');
            
            if (empty($password) || strlen($password) < 6) {
                $this->Flash->error(__('Password must be at least 6 characters long.'));
            } elseif ($password !== $confirmPassword) {
                $this->Flash->error(__('Passwords do not match.'));
            } else {
                // Update the user's password and clear reset token
                $user->password = $password;
                $user->reset_token = null;
                $user->reset_token_expires = null;
                
                if ($this->Users->save($user)) {
                    $this->Flash->success(__('Password has been reset successfully. You can now log in with your new password.'));
                    return $this->redirect(['action' => 'login']);
                } else {
                    $this->Flash->error(__('There was an error updating your password. Please try again.'));
                }
            }
        }

        $this->set(compact('confirmationCode', 'user'));
    }

    /**
     * Confirm email method
     *
     * @param string|null $confirmationCode
     * @return \Cake\Http\Response|null|void
     */
    public function confirmEmail(?string $confirmationCode = null)
    {
        if (!$confirmationCode) {
            $this->Flash->error(__('Invalid confirmation link.'));

            return $this->redirect(['action' => 'login']);
        }

        // This is a placeholder - you would typically validate the confirmation code
        // and activate the user's account
        $this->Flash->success(__('Email confirmed successfully.'));

        return $this->redirect(['action' => 'login']);
    }
}
