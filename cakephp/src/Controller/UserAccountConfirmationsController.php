<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * UserAccountConfirmations Controller
 *
 * @property \App\Model\Table\UserAccountConfirmationsTable $UserAccountConfirmations
 */
class UserAccountConfirmationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->UserAccountConfirmations->find()
            ->contain(['Users']);
        $userAccountConfirmations = $this->paginate($query);

        $this->set(compact('userAccountConfirmations'));
    }

    /**
     * View method
     *
     * @param string|null $id User Account Confirmation id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $userAccountConfirmation = $this->UserAccountConfirmations->get($id, contain: ['Users']);
        $this->set(compact('userAccountConfirmation'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $userAccountConfirmation = $this->UserAccountConfirmations->newEmptyEntity();
        if ($this->request->is('post')) {
            $userAccountConfirmation = $this->UserAccountConfirmations->patchEntity($userAccountConfirmation, $this->request->getData());
            if ($this->UserAccountConfirmations->save($userAccountConfirmation)) {
                $this->Flash->success(__('The user account confirmation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user account confirmation could not be saved. Please, try again.'));
        }
        $users = $this->UserAccountConfirmations->Users->find('list', limit: 200)->all();
        $this->set(compact('userAccountConfirmation', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User Account Confirmation id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $userAccountConfirmation = $this->UserAccountConfirmations->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $userAccountConfirmation = $this->UserAccountConfirmations->patchEntity($userAccountConfirmation, $this->request->getData());
            if ($this->UserAccountConfirmations->save($userAccountConfirmation)) {
                $this->Flash->success(__('The user account confirmation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user account confirmation could not be saved. Please, try again.'));
        }
        $users = $this->UserAccountConfirmations->Users->find('list', limit: 200)->all();
        $this->set(compact('userAccountConfirmation', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User Account Confirmation id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $userAccountConfirmation = $this->UserAccountConfirmations->get($id);
        if ($this->UserAccountConfirmations->delete($userAccountConfirmation)) {
            $this->Flash->success(__('The user account confirmation has been deleted.'));
        } else {
            $this->Flash->error(__('The user account confirmation could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
