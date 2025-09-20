<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * ArticlesTranslations Controller
 *
 * @property \App\Model\Table\ArticlesTranslationsTable $ArticlesTranslations
 */
class ArticlesTranslationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->ArticlesTranslations->find();
        $articlesTranslations = $this->paginate($query);

        $this->set(compact('articlesTranslations'));
    }

    /**
     * View method
     *
     * @param string|null $id Articles Translation id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $articlesTranslation = $this->ArticlesTranslations->get($id, contain: []);
        $this->set(compact('articlesTranslation'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $articlesTranslation = $this->ArticlesTranslations->newEmptyEntity();
        if ($this->request->is('post')) {
            $articlesTranslation = $this->ArticlesTranslations->patchEntity($articlesTranslation, $this->request->getData());
            if ($this->ArticlesTranslations->save($articlesTranslation)) {
                $this->Flash->success(__('The articles translation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The articles translation could not be saved. Please, try again.'));
        }
        $this->set(compact('articlesTranslation'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Articles Translation id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $articlesTranslation = $this->ArticlesTranslations->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $articlesTranslation = $this->ArticlesTranslations->patchEntity($articlesTranslation, $this->request->getData());
            if ($this->ArticlesTranslations->save($articlesTranslation)) {
                $this->Flash->success(__('The articles translation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The articles translation could not be saved. Please, try again.'));
        }
        $this->set(compact('articlesTranslation'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Articles Translation id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $articlesTranslation = $this->ArticlesTranslations->get($id);
        if ($this->ArticlesTranslations->delete($articlesTranslation)) {
            $this->Flash->success(__('The articles translation has been deleted.'));
        } else {
            $this->Flash->error(__('The articles translation could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
