<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Adapters Controller
 *
 * @property \App\Model\Table\AdaptersTable $Adapters
 */
class AdaptersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Adapters->find();
        $adapters = $this->paginate($query);

        $this->set(compact('adapters'));
    }

    /**
     * View method
     *
     * @param string|null $id Adapter id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $adapter = $this->Adapters->get($id, contain: []);
        $this->set(compact('adapter'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $adapter = $this->Adapters->newEmptyEntity();
        if ($this->request->is('post')) {
            $adapter = $this->Adapters->patchEntity($adapter, $this->request->getData());
            if ($this->Adapters->save($adapter)) {
                $this->Flash->success(__('The adapter has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The adapter could not be saved. Please, try again.'));
        }
        $this->set(compact('adapter'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Adapter id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $adapter = $this->Adapters->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $adapter = $this->Adapters->patchEntity($adapter, $this->request->getData());
            if ($this->Adapters->save($adapter)) {
                $this->Flash->success(__('The adapter has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The adapter could not be saved. Please, try again.'));
        }
        $this->set(compact('adapter'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Adapter id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $adapter = $this->Adapters->get($id);
        if ($this->Adapters->delete($adapter)) {
            $this->Flash->success(__('The adapter has been deleted.'));
        } else {
            $this->Flash->error(__('The adapter could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
