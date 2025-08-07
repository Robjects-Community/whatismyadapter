<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class ProductsController extends AppController
{
    public function importAdapters()
    {
        $csvData = $this->request->getUploadedFiles()['csv_file'];

        foreach ($this->parseCsv($csvData) as $row) {
            $adapter = $this->Products->newEntity([
                'title' => "{$row['connector _type_a']} to {$row['connector_type_b']} Adapter",
                'connector_type_a' => $row['connector_type_a'],
                'connector_type_b' => $row['connector_type_b'],
                'supports_usb_pd' => (bool)$row['supports_usb_pd'],
                'max_power_delivery' => $row['max_power_delivery'],
                'price' => $row['price_usd'],
                'category_rating' => $row['category_rating'],
                'verification_status' => 'pending',
                'user_id' => $this->Authentication->getIdentity()->id,
            ]);
            $this->Products->save($adapter);
        }
    }

    public function index()
    {
        // Customer-facing adapter catalog
        $adapters = $this->Products->find()
            ->where(['is_published' => true, 'verification_status' => 'verified'])
            ->contain(['Tags'])
            ->order(['featured' => 'DESC', 'view_count' => 'DESC'])
            ->limit(50);

        $this->set(compact('adapters'));
    }

    public function indexSimple()
    {
        // Customer-facing adapter catalog
        $adapters = $this->Products->find()
            ->where(['is_published' => true, 'verification_status' => 'verified'])
            ->contain(['Tags'])
            ->order(['featured' => 'DESC', 'view_count' => 'DESC'])
            ->limit(50);

        $this->set(compact('adapters'));
    }

    public function viewBySlug($slug = null)
    {
        // Individual adapter details with compatibility info
        $adapter = $this->Products->findBySlug($slug)
            ->where(['is_published' => true])
            ->contain(['Tags', 'Users'])
            ->firstOrFail();

        // Increment view count
        $this->Products->updateAll(['view_count' => $adapter->view_count + 1], ['id' => $adapter->id]);

        $this->set(compact('adapter'));
    }

    public function search()
    {
        // AJAX-powered adapter search by connector types
        $criteria = $this->request->getQuery();
        $adapters = $this->Products->searchAdapters($criteria);

        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $this->set(compact('adapters', 'criteria'));
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function indexSearch(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $query = $this->Products->find()
            ->contain(['Users', 'Articles']);

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
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
    public function view(?string $id = null)
    {
        $product = $this->Products->get($id, contain: ['Users', 'Articles', 'Tags', 'Slugs']);
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
        $articles = $this->Products->Articles->find('list', limit: 200)->all();
        $tags = $this->Products->Tags->find('list', limit: 200)->all();
        $this->set(compact('product', 'users', 'articles', 'tags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $product = $this->Products->get($id, contain: ['Tags']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $product = $this->Products->patchEntity($product, $this->request->getData());
            if ($this->Products->save($product)) {
                $this->Flash->success(__('The product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }
        $users = $this->Products->Users->find('list', limit: 200)->all();
        $articles = $this->Products->Articles->find('list', limit: 200)->all();
        $tags = $this->Products->Tags->find('list', limit: 200)->all();
        $this->set(compact('product', 'users', 'articles', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
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
