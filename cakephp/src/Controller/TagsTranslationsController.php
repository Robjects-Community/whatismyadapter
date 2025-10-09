<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * TagsTranslations Controller
 */
class TagsTranslationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->TagsTranslations->find();
        $tagsTranslations = $this->paginate($query);

        $this->set(compact('tagsTranslations'));
    }

    /**
     * View method
     *
     * @param string|null $id Tags Translation id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $tagsTranslation = $this->TagsTranslations->get($id, contain: []);
        $this->set(compact('tagsTranslation'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $tagsTranslation = $this->TagsTranslations->newEmptyEntity();
        if ($this->request->is('post')) {
            $tagsTranslation = $this->TagsTranslations->patchEntity($tagsTranslation, $this->request->getData());
            if ($this->TagsTranslations->save($tagsTranslation)) {
                $this->Flash->success(__('The tags translation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The tags translation could not be saved. Please, try again.'));
        }
        $this->set(compact('tagsTranslation'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Tags Translation id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $tagsTranslation = $this->TagsTranslations->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $tagsTranslation = $this->TagsTranslations->patchEntity($tagsTranslation, $this->request->getData());
            if ($this->TagsTranslations->save($tagsTranslation)) {
                $this->Flash->success(__('The tags translation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The tags translation could not be saved. Please, try again.'));
        }
        $this->set(compact('tagsTranslation'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Tags Translation id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $tagsTranslation = $this->TagsTranslations->get($id);
        if ($this->TagsTranslations->delete($tagsTranslation)) {
            $this->Flash->success(__('The tags translation has been deleted.'));
        } else {
            $this->Flash->error(__('The tags translation could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
