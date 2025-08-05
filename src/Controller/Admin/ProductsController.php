
<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

// src/Controller/Admin/ProductsController.php (Admin Management)
class ProductsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {

        // Admin product management with advanced filtering
        $query = $this->Products->find()
            ->contain(['Users', 'Articles', 'Tags'])
            ->orderBy(['Products.created' => 'DESC']); // Fix column ambiguity
            
        $products = $this->paginate($query);
        $this->set(compact('products'));
        $adapters = $this->Products->find('all')
            ->where(['is_published' => 1, 'verification_status' => 'verified'])
            ->orderby(['Products.created' => 'DESC'])
            ->limit(20); // Paginate as needed

        $this->set(compact('products', 'adapters'));

  
    }
    public function pendingReview()
    {
  
    $products = $this->Products->find()
        ->where(['verification_status' => 'pending'])
        ->contain(['Users', 'Articles', 'Tags'])
        ->orderBy(['Products.created' => 'DESC']);

    $this->set(compact('products'));

        // // Handle pending product submissions
        // $pendingProducts = $this->Products->find()
        //     ->where(['verification_status IN' => ['pending', 'flagged']])
        //     ->contain(['Users'])
        //     ->orderBy(['Products.created' => 'DESC']);
            
        // $this->set('products', $this->paginate($pendingProducts));
    }

    public function dashboard()
{
    // Product analytics dashboard
    $stats = [
        'total_products' => $this->Products->find()->count(),
        'published' => $this->Products->find()->where(['is_published' => true])->count(),
        'pending_review' => $this->Products->find()->where(['verification_status' => 'pending'])->count(),
        'top_connectors' => $this->Products->find()
            ->select(['connector_type_a', 'connector_type_b', 'count' => 'COUNT(*)'])
            ->group(['connector_type_a', 'connector_type_b'])
            ->orderBy(['count' => 'DESC'])
            ->limit(10)
            ->toArray()
    ];
    
    $this->set(compact('stats'));
}

public function add()
{
    $product = $this->Products->newEmptyEntity();
    
    if ($this->request->is('post')) {
        $product = $this->Products->patchEntity($product, $this->request->getData());
        
        if ($this->Products->save($product)) {
            $this->Flash->success(__('The adapter has been saved.'));
            
            // Smart redirection based on user context
            $redirectAction = $this->determineRedirectAction($product);
            return $this->redirect($redirectAction);
        }
        
        $this->Flash->error(__('The adapter could not be saved. Please, try again.'));
    }
    
    $this->set(compact('product'));
}


private function determineRedirectAction($product)
{
    // Redirect logic based on product status and user role
    if ($product->verification_status === 'pending') {
        return ['action' => 'pendingReview'];
    } elseif ($product->is_published) {
        return ['action' => 'index'];
    } else {
        return ['action' => 'edit', $product->id];
    }

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
        $product = $this->Products->get($id, contain: ['Users', 'Articles', 'Tags']);
        $this->set(compact('product'));
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
        $product = $this->Products->get($id, contain: ['Tags']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $product = $this->Products->patchEntity($product, $this->request->getData());
            if ($this->Products->save($product)) {
                $this->Flash->success(__('The product has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product could not be saved. Please, try again.'));
        }
        $users = $this->Products->Users->find('list', ['limit' => 200])->all();
        $articles = $this->Products->Articles->find('list', ['limit' => 200])->all();
        $tags = $this->Products->Tags->find('list', ['limit' => 200])->all();
        $this->set(compact('product', 'users', 'articles', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Product id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $product = $this->Products->get($id);
        if ($this->Products->delete($product)) {
            $this->Flash->success(__('The product has been deleted.'));
        } else {
            $this->Flash->error(__('The product could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}


