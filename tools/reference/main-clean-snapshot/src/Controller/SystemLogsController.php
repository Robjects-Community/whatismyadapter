<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * SystemLogs Controller
 *
 * @property \App\Model\Table\SystemLogsTable $SystemLogs
 */
class SystemLogsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->SystemLogs->find();
        $systemLogs = $this->paginate($query);

        $this->set(compact('systemLogs'));
    }

    /**
     * View method
     *
     * @param string|null $id System Log id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $systemLog = $this->SystemLogs->get($id, contain: []);
        $this->set(compact('systemLog'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $systemLog = $this->SystemLogs->newEmptyEntity();
        if ($this->request->is('post')) {
            $systemLog = $this->SystemLogs->patchEntity($systemLog, $this->request->getData());
            if ($this->SystemLogs->save($systemLog)) {
                $this->Flash->success(__('The system log has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The system log could not be saved. Please, try again.'));
        }
        $this->set(compact('systemLog'));
    }

    /**
     * Edit method
     *
     * @param string|null $id System Log id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $systemLog = $this->SystemLogs->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $systemLog = $this->SystemLogs->patchEntity($systemLog, $this->request->getData());
            if ($this->SystemLogs->save($systemLog)) {
                $this->Flash->success(__('The system log has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The system log could not be saved. Please, try again.'));
        }
        $this->set(compact('systemLog'));
    }

    /**
     * Delete method
     *
     * @param string|null $id System Log id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $systemLog = $this->SystemLogs->get($id);
        if ($this->SystemLogs->delete($systemLog)) {
            $this->Flash->success(__('The system log has been deleted.'));
        } else {
            $this->Flash->error(__('The system log could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
