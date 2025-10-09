<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * ImageGalleriesTranslations Controller
 */
class ImageGalleriesTranslationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->ImageGalleriesTranslations->find();
        $imageGalleriesTranslations = $this->paginate($query);

        $this->set(compact('imageGalleriesTranslations'));
    }

    /**
     * View method
     *
     * @param string|null $id Image Galleries Translation id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $imageGalleriesTranslation = $this->ImageGalleriesTranslations->get($id, contain: []);
        $this->set(compact('imageGalleriesTranslation'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $imageGalleriesTranslation = $this->ImageGalleriesTranslations->newEmptyEntity();
        if ($this->request->is('post')) {
            $imageGalleriesTranslation = $this->ImageGalleriesTranslations->patchEntity($imageGalleriesTranslation, $this->request->getData());
            if ($this->ImageGalleriesTranslations->save($imageGalleriesTranslation)) {
                $this->Flash->success(__('The image galleries translation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image galleries translation could not be saved. Please, try again.'));
        }
        $this->set(compact('imageGalleriesTranslation'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Image Galleries Translation id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $imageGalleriesTranslation = $this->ImageGalleriesTranslations->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $imageGalleriesTranslation = $this->ImageGalleriesTranslations->patchEntity($imageGalleriesTranslation, $this->request->getData());
            if ($this->ImageGalleriesTranslations->save($imageGalleriesTranslation)) {
                $this->Flash->success(__('The image galleries translation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image galleries translation could not be saved. Please, try again.'));
        }
        $this->set(compact('imageGalleriesTranslation'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Image Galleries Translation id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $imageGalleriesTranslation = $this->ImageGalleriesTranslations->get($id);
        if ($this->ImageGalleriesTranslations->delete($imageGalleriesTranslation)) {
            $this->Flash->success(__('The image galleries translation has been deleted.'));
        } else {
            $this->Flash->error(__('The image galleries translation could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
