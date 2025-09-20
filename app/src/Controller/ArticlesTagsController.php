<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * ArticlesTags Controller
 *
 * @property \App\Model\Table\ArticlesTagsTable $ArticlesTags
 */
class ArticlesTagsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->ArticlesTags->find()
            ->contain(['Articles', 'Tags']);
        $articlesTags = $this->paginate($query);

        $this->set(compact('articlesTags'));
    }

    /**
     * View method
     *
     * @param string|null $id Articles Tag id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $articlesTag = $this->ArticlesTags->get($id, contain: ['Articles', 'Tags']);
        $this->set(compact('articlesTag'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $articlesTag = $this->ArticlesTags->newEmptyEntity();
        if ($this->request->is('post')) {
            $articlesTag = $this->ArticlesTags->patchEntity($articlesTag, $this->request->getData());
            if ($this->ArticlesTags->save($articlesTag)) {
                $this->Flash->success(__('The articles tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The articles tag could not be saved. Please, try again.'));
        }
        $articles = $this->ArticlesTags->Articles->find('list', limit: 200)->all();
        $tags = $this->ArticlesTags->Tags->find('list', limit: 200)->all();
        $this->set(compact('articlesTag', 'articles', 'tags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Articles Tag id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $articlesTag = $this->ArticlesTags->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $articlesTag = $this->ArticlesTags->patchEntity($articlesTag, $this->request->getData());
            if ($this->ArticlesTags->save($articlesTag)) {
                $this->Flash->success(__('The articles tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The articles tag could not be saved. Please, try again.'));
        }
        $articles = $this->ArticlesTags->Articles->find('list', limit: 200)->all();
        $tags = $this->ArticlesTags->Tags->find('list', limit: 200)->all();
        $this->set(compact('articlesTag', 'articles', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Articles Tag id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $articlesTag = $this->ArticlesTags->get($id);
        if ($this->ArticlesTags->delete($articlesTag)) {
            $this->Flash->success(__('The articles tag has been deleted.'));
        } else {
            $this->Flash->error(__('The articles tag could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
