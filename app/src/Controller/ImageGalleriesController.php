<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * ImageGalleries Controller
 *
 * @property \App\Model\Table\ImageGalleriesTable $ImageGalleries
 */
class ImageGalleriesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->ImageGalleries->find();
        $imageGalleries = $this->paginate($query);

        $this->set(compact('imageGalleries'));
    }

    /**
     * View method
     *
     * @param string|null $id Image Gallery id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $imageGallery = $this->ImageGalleries->get($id, contain: ['Images', 'Slugs', 'ImageGalleriesTranslations']);
        $this->set(compact('imageGallery'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $imageGallery = $this->ImageGalleries->newEmptyEntity();
        if ($this->request->is('post')) {
            $imageGallery = $this->ImageGalleries->patchEntity($imageGallery, $this->request->getData());
            if ($this->ImageGalleries->save($imageGallery)) {
                $this->Flash->success(__('The image gallery has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image gallery could not be saved. Please, try again.'));
        }
        $images = $this->ImageGalleries->Images->find('list', limit: 200)->all();
        $this->set(compact('imageGallery', 'images'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Image Gallery id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $imageGallery = $this->ImageGalleries->get($id, contain: ['Images']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $imageGallery = $this->ImageGalleries->patchEntity($imageGallery, $this->request->getData());
            if ($this->ImageGalleries->save($imageGallery)) {
                $this->Flash->success(__('The image gallery has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image gallery could not be saved. Please, try again.'));
        }
        $images = $this->ImageGalleries->Images->find('list', limit: 200)->all();
        $this->set(compact('imageGallery', 'images'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Image Gallery id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $imageGallery = $this->ImageGalleries->get($id);
        if ($this->ImageGalleries->delete($imageGallery)) {
            $this->Flash->success(__('The image gallery has been deleted.'));
        } else {
            $this->Flash->error(__('The image gallery could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
