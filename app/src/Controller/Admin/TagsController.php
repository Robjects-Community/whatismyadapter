<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Http\Response;
use Exception;

/**
 * Tags Controller
 *
 * Manages tags, providing functionalities to list, view, add, edit, and delete tags.
 *
 * @property \App\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController
{
    /**
     * Clears the content cache (used for both articles and tags)
     *
     * @return void
     */
    private function clearContentCache(): void
    {
        Cache::clear('content');
    }

    /**
     * Retrieves a hierarchical list of tags.
     *
     * @return void
     */
    public function treeIndex(): ?Response
    {
        $session = $this->request->getSession();
        $session->write('Tags.indexAction', 'treeIndex');

        $statusFilter = $this->request->getQuery('status');
        $conditions = [];

        if ($statusFilter === '1') {
            //$conditions['Articles.is_published'] = '1';
        } elseif ($statusFilter === '0') {
            //$conditions['Articles.is_published'] = '0';
        }

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $conditions['OR'] = [
                    'Tags.title LIKE' => '%' . $search . '%',
                    'Tags.slug LIKE' => '%' . $search . '%',
                    'Tags.description LIKE' => '%' . $search . '%',
                    'Tags.meta_title LIKE' => '%' . $search . '%',
                    'Tags.meta_description LIKE' => '%' . $search . '%',
                    'Tags.meta_keywords LIKE' => '%' . $search . '%',
                ];
            }
            $tags = $this->Tags->getTree($conditions, [
                'slug',
                'created',
                'modified',
            ]);
            $this->set(compact('tags'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('tree_index_search_results');
        }

        $tags = $this->Tags->getTree($conditions, [
            'slug',
            'created',
            'modified',
        ]);

        $this->set(compact('tags'));

        return null;
    }

    /**
     * Updates the tree structure of articles.
     *
     * @return \Cake\Http\Response|null The JSON response indicating success or failure.
     * @throws \Exception If an error occurs during the reordering process.
     */
    public function updateTree(): ?Response
    {
        $this->request->allowMethod(['post', 'put']);
        $data = $this->request->getData();

        try {
            $result = $this->Tags->reorder($data);
            $this->clearContentCache();

            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'result' => $result]));
        } catch (Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Index method for Tags Controller
     *
     * Handles the display of tags. Supports both standard and AJAX requests.
     * For AJAX requests, performs a search based on the 'search' query parameter and returns
     * the results in an 'ajax' layout. For standard requests, paginates the tags.
     *
     * @return \Cake\Http\Response|null Returns a Response object for AJAX requests, null otherwise.
     */
    public function index(): ?Response
    {
        $session = $this->request->getSession();
        $session->write('Tags.indexAction', 'index');
        $statusFilter = $this->request->getQuery('level');

        $query = $this->Tags->find()
            ->contain(['ParentTag'])
            ->select();

        if ($statusFilter == '0') {
            $query->where(['Tags.parent_id IS' => null]);
        }

        if ($statusFilter == '1') {
            $query->where(['Tags.parent_id IS NOT' => null]);
        }

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Tags.title LIKE' => '%' . $search . '%',
                    'Tags.slug LIKE' => '%' . $search . '%',
                    'Tags.meta_title LIKE' => '%' . $search . '%',
                    'Tags.meta_description LIKE' => '%' . $search . '%',
                    'Tags.meta_keywords LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $tags = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('tags', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('tags'));

        return null;
    }

    /**
     * View method
     *
     * Displays details of a specific tag, including associated articles and their authors.
     *
     * @param string|null $id Tag id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $tag = $this->Tags->get($id, contain: ['Articles.Users']);
        $this->set(compact('tag'));
    }

    /**
     * Add method
     *
     * Handles the creation of a new tag.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, null otherwise.
     */
    public function add(): ?Response
    {
        $session = $this->request->getSession();
        $tag = $this->Tags->newEmptyEntity();
        if ($this->request->is('post')) {
            $tag = $this->Tags->patchEntity($tag, $this->request->getData());
            if ($this->Tags->save($tag)) {
                $this->clearContentCache();
                $this->Flash->success(__('The tag has been saved.'));

                return $this->redirect(['action' => $session->read('Tags.indexAction', 'treeIndex')]);
            }
            $this->Flash->error(__('The tag could not be saved. Please, try again.'));
        }
        $articles = $this->Tags->Articles->find('list', limit: 200)->all();
        $parentTags = $this->Tags->find('list')->all();

        $this->set(compact('tag', 'articles', 'parentTags'));

        return null;
    }

    /**
     * Edit method
     *
     * Handles the editing of an existing tag.
     *
     * @param string|null $id Tag id.
     * @return \Cake\Http\Response|null Redirects on successful edit, null otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $session = $this->request->getSession();
        $tag = $this->Tags->get($id, contain: ['Articles']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $tag = $this->Tags->patchEntity($tag, $this->request->getData());
            if ($this->Tags->save($tag)) {
                $this->clearContentCache();
                $this->Flash->success(__('The tag has been saved.'));

                return $this->redirect(['action' => $session->read('Tags.indexAction', 'treeIndex')]);
            }
            $this->Flash->error(__('The tag could not be saved. Please, try again.'));
        }
        $articles = $this->Tags->Articles->find('list', limit: 200)->all();
        $parentTags = $this->Tags->find('list')
        ->where(['id NOT IN' => $id])
        ->all();

        $this->set(compact('tag', 'articles', 'parentTags'));

        return null;
    }

    /**
     * Delete method
     *
     * Handles the deletion of a tag.
     *
     * @param string|null $id Tag id.
     * @return \Cake\Http\Response Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     * @throws \Cake\Http\Exception\MethodNotAllowedException When invalid method is used.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $session = $this->request->getSession();
        $tag = $this->Tags->get($id);
        if ($this->Tags->delete($tag)) {
            $this->clearContentCache();
            $this->Flash->success(__('The tag has been deleted.'));
        } else {
            $this->Flash->error(__('The tag could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => $session->read('Tags.indexAction', 'treeIndex')]);
    }
}
