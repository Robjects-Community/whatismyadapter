<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
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
    public function index(): ?Response
    {
        $search = trim((string)$this->request->getQuery('search', ''));
        $isActive = $this->request->getQuery('is_active');
        $category = trim((string)$this->request->getQuery('category', ''));

        $query = $this->Aiprompts->find()
            ->select([
                'Aiprompts.id',
                'Aiprompts.task_type',
                'Aiprompts.system_prompt',
                'Aiprompts.model',
                'Aiprompts.max_tokens',
                'Aiprompts.temperature',
                'Aiprompts.status',
                'Aiprompts.last_used',
                'Aiprompts.usage_count',
                'Aiprompts.success_rate',
                'Aiprompts.description',
                'Aiprompts.is_active',
                'Aiprompts.category',
                'Aiprompts.version',
                'Aiprompts.created',
                'Aiprompts.modified',
            ]);

        if (!empty($search)) {
            $like = '%' . $search . '%';
            $query->where([
                'OR' => [
                    'Aiprompts.task_type LIKE' => $like,
                    'Aiprompts.system_prompt LIKE' => $like,
                    'Aiprompts.model LIKE' => $like,
                    'Aiprompts.status LIKE' => $like,
                    'Aiprompts.category LIKE' => $like,
                    'Aiprompts.description LIKE' => $like,
                    'Aiprompts.version LIKE' => $like,
                ],
            ]);
        }

        if ($isActive !== null && $isActive !== '') {
            $query->where(['Aiprompts.is_active' => (bool)$isActive]);
        }

        if (!empty($category)) {
            $query->where(['Aiprompts.category LIKE' => '%' . $category . '%']);
        }

        $query->order(['Aiprompts.modified' => 'DESC', 'Aiprompts.id' => 'DESC']);

        $aiprompts = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('aiprompts', 'search', 'isActive', 'category'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('aiprompts', 'search', 'isActive', 'category'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Aiprompt id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $aiprompt = $this->Aiprompts->get($id, contain: []);
        $this->set(compact('aiprompt'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
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

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Aiprompt id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
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

        return null;
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

        return $this->redirect($this->referer());
    }
}
