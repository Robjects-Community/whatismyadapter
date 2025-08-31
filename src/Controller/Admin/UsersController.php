<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * Users Controller
 *
 * Manages user-related actions such as listing, viewing, adding, editing, and deleting users.
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Configures actions that can be accessed without authentication.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['login']);
    }

    /**
     * Lists users and handles search functionality.
     *
     * Processes both standard and AJAX requests for listing users.
     * Paginates user data for standard requests and performs search for AJAX requests.
     *
     * @return \Cake\Http\Response|null Returns a response for AJAX requests, null otherwise.
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $query = $this->Users->find()
            ->select([
                'Users.id',
                'Users.username',
                'Users.email',
                'Users.is_admin',
                'Users.active',
                'Users.created',
                'Users.modified',
                'Users.image',
                'Users.dir',
            ]);

        if ($statusFilter !== null) {
            $query->where(['Users.active' => (int)$statusFilter]);
        }

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Users.username LIKE' => '%' . $search . '%',
                    'Users.email LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $users = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('users'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $this->set(compact('users'));

        return null;
    }

    /**
     * Displays details of a specific user.
     *
     * @param string|null $id User id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $user = $this->Users->get($id, contain: ['Articles', 'Comments.Articles']);
        $this->set(compact('user'));
    }

    /**
     * Adds a new user.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, null otherwise.
     */
    public function add(): ?Response
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user->setAccess('is_admin', true);
            $user->setAccess('active', true);
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));

        return null;
    }

    /**
     * Edits user information.
     *
     * Handles updating of user details with security checks to prevent self-locking their account.
     *
     * @param string|null $id The ID of the user to edit.
     * @return \Cake\Http\Response|null Redirects on successful edit, null otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $user = $this->Users->get($id, contain: []);
        $currentUser = $this->Authentication->getIdentity();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user->setAccess('is_admin', true);
            $user->setAccess('active', true);

            $data = $this->request->getData();

            // Prevent changing own admin status
            if ($user->lockAdminAccountError($currentUser->id, $data)) {
                $this->Flash->error(__('You cannot remove your own admin status.'));

                return $this->redirect(['action' => 'edit', $id]);
            }
            //prevent disabling own account
            if ($user->lockEnabledAccountError($currentUser->id, $data)) {
                $this->Flash->error(__('You cannot disable your own account.'));

                return $this->redirect(['action' => 'edit', $id]);
            }

            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));

        return null;
    }

    /**
     * Deletes a user.
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Cake\Http\Exception\MethodNotAllowedException When invalid method is used.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        $currentUser = $this->Authentication->getIdentity();
        if ($currentUser->id == $user->id) {
            $this->Flash->error(__('No deleting your own account.'));

            return $this->redirect(['action' => 'index']);
        }
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Logs out the current user.
     *
     * @return \Cake\Http\Response|null Redirects to the login page.
     */
    public function logout(): ?Response
    {
        $this->Authentication->logout();

        return $this->redirect('/');
    }
}
