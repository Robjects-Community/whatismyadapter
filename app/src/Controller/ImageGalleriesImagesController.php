<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * ImageGalleriesImages Controller
 *
 * @property \App\Model\Table\ImageGalleriesImagesTable $ImageGalleriesImages
 */
class ImageGalleriesImagesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->ImageGalleriesImages->find()
            ->contain(['ImageGalleries', 'Images']);
        $imageGalleriesImages = $this->paginate($query);

        $this->set(compact('imageGalleriesImages'));
    }

    /**
     * View method
     *
     * @param string|null $id Image Galleries Image id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $imageGalleriesImage = $this->ImageGalleriesImages->get($id, contain: ['ImageGalleries', 'Images']);
        $this->set(compact('imageGalleriesImage'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $imageGalleriesImage = $this->ImageGalleriesImages->newEmptyEntity();
        if ($this->request->is('post')) {
            $imageGalleriesImage = $this->ImageGalleriesImages->patchEntity($imageGalleriesImage, $this->request->getData());
            if ($this->ImageGalleriesImages->save($imageGalleriesImage)) {
                $this->Flash->success(__('The image galleries image has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image galleries image could not be saved. Please, try again.'));
        }
        $imageGalleries = $this->ImageGalleriesImages->ImageGalleries->find('list', limit: 200)->all();
        $images = $this->ImageGalleriesImages->Images->find('list', limit: 200)->all();
        $this->set(compact('imageGalleriesImage', 'imageGalleries', 'images'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Image Galleries Image id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $imageGalleriesImage = $this->ImageGalleriesImages->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $imageGalleriesImage = $this->ImageGalleriesImages->patchEntity($imageGalleriesImage, $this->request->getData());
            if ($this->ImageGalleriesImages->save($imageGalleriesImage)) {
                $this->Flash->success(__('The image galleries image has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The image galleries image could not be saved. Please, try again.'));
        }
        $imageGalleries = $this->ImageGalleriesImages->ImageGalleries->find('list', limit: 200)->all();
        $images = $this->ImageGalleriesImages->Images->find('list', limit: 200)->all();
        $this->set(compact('imageGalleriesImage', 'imageGalleries', 'images'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Image Galleries Image id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $imageGalleriesImage = $this->ImageGalleriesImages->get($id);
        if ($this->ImageGalleriesImages->delete($imageGalleriesImage)) {
            $this->Flash->success(__('The image galleries image has been deleted.'));
        } else {
            $this->Flash->error(__('The image galleries image could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
