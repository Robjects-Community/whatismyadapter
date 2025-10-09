<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * BlockedIps Controller
 *
 * @property \App\Model\Table\BlockedIpsTable $BlockedIps
 */
class BlockedIpsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->BlockedIps->find();
        $blockedIps = $this->paginate($query);

        $this->set(compact('blockedIps'));
    }

    /**
     * View method
     *
     * @param string|null $id Blocked Ip id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $blockedIp = $this->BlockedIps->get($id, contain: []);
        $this->set(compact('blockedIp'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $blockedIp = $this->BlockedIps->newEmptyEntity();
        if ($this->request->is('post')) {
            $blockedIp = $this->BlockedIps->patchEntity($blockedIp, $this->request->getData());
            if ($this->BlockedIps->save($blockedIp)) {
                $this->Flash->success(__('The blocked ip has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The blocked ip could not be saved. Please, try again.'));
        }
        $this->set(compact('blockedIp'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Blocked Ip id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $blockedIp = $this->BlockedIps->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $blockedIp = $this->BlockedIps->patchEntity($blockedIp, $this->request->getData());
            if ($this->BlockedIps->save($blockedIp)) {
                $this->Flash->success(__('The blocked ip has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The blocked ip could not be saved. Please, try again.'));
        }
        $this->set(compact('blockedIp'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Blocked Ip id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $blockedIp = $this->BlockedIps->get($id);
        if ($this->BlockedIps->delete($blockedIp)) {
            $this->Flash->success(__('The blocked ip has been deleted.'));
        } else {
            $this->Flash->error(__('The blocked ip could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
