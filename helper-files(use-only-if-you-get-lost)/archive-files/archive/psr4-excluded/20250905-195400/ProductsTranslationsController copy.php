<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * ProductsTranslations Controller
 *
 * @property \App\Model\Table\ProductsTranslationsTable $ProductsTranslations
 */
class ProductsTranslationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->ProductsTranslations->find();
        $productsTranslations = $this->paginate($query);

        $this->set(compact('productsTranslations'));
    }

    /**
     * View method
     *
     * @param string|null $id Products Translation id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $productsTranslation = $this->ProductsTranslations->get($id, contain: []);
        $this->set(compact('productsTranslation'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $productsTranslation = $this->ProductsTranslations->newEmptyEntity();
        if ($this->request->is('post')) {
            $productsTranslation = $this->ProductsTranslations->patchEntity($productsTranslation, $this->request->getData());
            if ($this->ProductsTranslations->save($productsTranslation)) {
                $this->Flash->success(__('The products translation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The products translation could not be saved. Please, try again.'));
        }
        $this->set(compact('productsTranslation'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Products Translation id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $productsTranslation = $this->ProductsTranslations->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $productsTranslation = $this->ProductsTranslations->patchEntity($productsTranslation, $this->request->getData());
            if ($this->ProductsTranslations->save($productsTranslation)) {
                $this->Flash->success(__('The products translation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The products translation could not be saved. Please, try again.'));
        }
        $this->set(compact('productsTranslation'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Products Translation id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $productsTranslation = $this->ProductsTranslations->get($id);
        if ($this->ProductsTranslations->delete($productsTranslation)) {
            $this->Flash->success(__('The products translation has been deleted.'));
        } else {
            $this->Flash->error(__('The products translation could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
