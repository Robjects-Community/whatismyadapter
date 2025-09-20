<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * ProductsTags Controller
 *
 * @property \App\Model\Table\ProductsTagsTable $ProductsTags
 */
class ProductsTagsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->ProductsTags->find()
            ->contain(['Products', 'Tags']);
        $productsTags = $this->paginate($query);

        $this->set(compact('productsTags'));
    }

    /**
     * View method
     *
     * @param string|null $id Products Tag id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $productsTag = $this->ProductsTags->get($id, contain: ['Products', 'Tags']);
        $this->set(compact('productsTag'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $productsTag = $this->ProductsTags->newEmptyEntity();
        if ($this->request->is('post')) {
            $productsTag = $this->ProductsTags->patchEntity($productsTag, $this->request->getData());
            if ($this->ProductsTags->save($productsTag)) {
                $this->Flash->success(__('The products tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The products tag could not be saved. Please, try again.'));
        }
        $products = $this->ProductsTags->Products->find('list', limit: 200)->all();
        $tags = $this->ProductsTags->Tags->find('list', limit: 200)->all();
        $this->set(compact('productsTag', 'products', 'tags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Products Tag id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $productsTag = $this->ProductsTags->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $productsTag = $this->ProductsTags->patchEntity($productsTag, $this->request->getData());
            if ($this->ProductsTags->save($productsTag)) {
                $this->Flash->success(__('The products tag has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The products tag could not be saved. Please, try again.'));
        }
        $products = $this->ProductsTags->Products->find('list', limit: 200)->all();
        $tags = $this->ProductsTags->Tags->find('list', limit: 200)->all();
        $this->set(compact('productsTag', 'products', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Products Tag id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $productsTag = $this->ProductsTags->get($id);
        if ($this->ProductsTags->delete($productsTag)) {
            $this->Flash->success(__('The products tag has been deleted.'));
        } else {
            $this->Flash->error(__('The products tag could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
