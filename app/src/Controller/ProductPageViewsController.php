<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * PageViews Controller
 *
 * @property \App\Model\Table\PageViewsTable $PageViews
 */
class ProductPageViewsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->PageViews->find()
            ->contain(['Products']);
        $pageViews = $this->paginate($query);

        $this->set(compact('pageViews'));
    }

    /**
     * View method
     *
     * @param string|null $id Page View id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $pageView = $this->PageViews->get($id, contain: ['Products']);
        $this->set(compact('pageView'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $pageView = $this->PageViews->newEmptyEntity();
        if ($this->request->is('post')) {
            $pageView = $this->PageViews->patchEntity($pageView, $this->request->getData());
            if ($this->PageViews->save($pageView)) {
                $this->Flash->success(__('The page view has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The page view could not be saved. Please, try again.'));
        }
        $products = $this->PageViews->Products->find('list', limit: 200)->all();
        $this->set(compact('pageView', 'products'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Page View id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $pageView = $this->PageViews->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $pageView = $this->PageViews->patchEntity($pageView, $this->request->getData());
            if ($this->PageViews->save($pageView)) {
                $this->Flash->success(__('The page view has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The page view could not be saved. Please, try again.'));
        }
        $products = $this->PageViews->Products->find('list', limit: 200)->all();
        $this->set(compact('pageView', 'products'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Page View id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $pageView = $this->PageViews->get($id);
        if ($this->PageViews->delete($pageView)) {
            $this->Flash->success(__('The page view has been deleted.'));
        } else {
            $this->Flash->error(__('The page view could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
