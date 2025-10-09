<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Products Controller
 *
 * @property \App\Model\Table\ProductsTable $Products
 */
class ProductsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $query = $this->Products->find()
            ->contain(['Users']);

        
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Products.kind LIKE' => '%' . $search . '%',
                    'Products.title LIKE' => '%' . $search . '%',
                    'Products.lede LIKE' => '%' . $search . '%',
                    'Products.slug LIKE' => '%' . $search . '%',
                    'Products.body LIKE' => '%' . $search . '%',
                    'Products.markdown LIKE' => '%' . $search . '%',
                    'Products.summary LIKE' => '%' . $search . '%',
                    'Products.alt_text LIKE' => '%' . $search . '%',
                    'Products.keywords LIKE' => '%' . $search . '%',
                    'Products.name LIKE' => '%' . $search . '%',
                    'Products.dir LIKE' => '%' . $search . '%',
                    'Products.mime LIKE' => '%' . $search . '%',
                    'Products.meta_title LIKE' => '%' . $search . '%',
                    'Products.meta_description LIKE' => '%' . $search . '%',
                    'Products.meta_keywords LIKE' => '%' . $search . '%',
                    'Products.facebook_description LIKE' => '%' . $search . '%',
                    'Products.linkedin_description LIKE' => '%' . $search . '%',
                    'Products.instagram_description LIKE' => '%' . $search . '%',
                    'Products.twitter_description LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $products = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('products', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('products'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $product = $this->Products->get($id, contain: ['Users', 'Images', 'Tags', 'Comments', 'Slugs', 'ProductsTranslations', 'PageViews']);
        $this->set(compact('product'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $product = $this->Products->newEmptyEntity();
        if ($this->request->is('post')) {
            $product = $this->Products->patchEntity($product, $this->request->getData());
            if ($this->Products->save($product)) {
                $this->Flash->success(__('The product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }
        $users = $this->Products->Users->find('list', limit: 200)->all();
        $images = $this->Products->Images->find('list', limit: 200)->all();
        $tags = $this->Products->Tags->find('list', limit: 200)->all();
        $this->set(compact('product', 'users', 'images', 'tags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $product = $this->Products->get($id, contain: ['Images', 'Tags']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $product = $this->Products->patchEntity($product, $this->request->getData());
            if ($this->Products->save($product)) {
                $this->Flash->success(__('The product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }
        $users = $this->Products->Users->find('list', limit: 200)->all();
        $images = $this->Products->Images->find('list', limit: 200)->all();
        $tags = $this->Products->Tags->find('list', limit: 200)->all();
        $this->set(compact('product', 'users', 'images', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $product = $this->Products->get($id);
        if ($this->Products->delete($product)) {
            $this->Flash->success(__('The product has been deleted.'));
        } else {
            $this->Flash->error(__('The product could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
