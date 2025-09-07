<?php
declare(strict_types=1);

namespace App\Controller;

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
            'confirmEmail'
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
    public function delete(?string $id = null)
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
    public function logout()
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
            $user = $this->Users->patchEntity($user, $this->request->getData());
            // Set default values for new users
            $user->is_admin = false;
            $user->active = true;
            
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
                // This is a placeholder - you would typically send a reset email here
                $this->Flash->success(__('If the email exists in our system, you will receive a password reset link.'));
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
    public function resetPassword($confirmationCode = null)
    {
        if (!$confirmationCode) {
            $this->Flash->error(__('Invalid reset link.'));
            return $this->redirect(['action' => 'login']);
        }

        if ($this->request->is('post')) {
            // This is a placeholder - you would typically validate the confirmation code
            // and update the user's password
            $this->Flash->success(__('Password has been reset successfully.'));
            return $this->redirect(['action' => 'login']);
        }

        $this->set(compact('confirmationCode'));
    }

    /**
     * Confirm email method
     *
     * @param string|null $confirmationCode
     * @return \Cake\Http\Response|null|void
     */
    public function confirmEmail($confirmationCode = null)
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
