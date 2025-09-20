<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Aiprompts Controller
 *
 * @property \App\Model\Table\AipromptsTable $Aiprompts
 */
class AipromptsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Aiprompts->find();
        $aiprompts = $this->paginate($query);

        $this->set(compact('aiprompts'));
    }

    /**
     * View method
     *
     * @param string|null $id Aiprompt id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $aiprompt = $this->Aiprompts->get($id, contain: []);
        $this->set(compact('aiprompt'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $aiprompt = $this->Aiprompts->newEmptyEntity();
        if ($this->request->is('post')) {
            $aiprompt = $this->Aiprompts->patchEntity($aiprompt, $this->request->getData());
            if ($this->Aiprompts->save($aiprompt)) {
                $this->Flash->success(__('The aiprompt has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The aiprompt could not be saved. Please, try again.'));
        }
        $this->set(compact('aiprompt'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Aiprompt id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $aiprompt = $this->Aiprompts->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $aiprompt = $this->Aiprompts->patchEntity($aiprompt, $this->request->getData());
            if ($this->Aiprompts->save($aiprompt)) {
                $this->Flash->success(__('The aiprompt has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The aiprompt could not be saved. Please, try again.'));
        }
        $this->set(compact('aiprompt'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Aiprompt id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $aiprompt = $this->Aiprompts->get($id);
        if ($this->Aiprompts->delete($aiprompt)) {
            $this->Flash->success(__('The aiprompt has been deleted.'));
        } else {
            $this->Flash->error(__('The aiprompt could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
