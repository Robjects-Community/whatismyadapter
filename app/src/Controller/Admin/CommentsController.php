<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Table\ArticlesTable;
use Cake\Cache\Cache;
use Cake\Http\Response;
use Cake\ORM\Query;

/**
 * Comments Controller
 *
 * Manages CRUD operations for comments in the admin area.
 *
 * @property \App\Model\Table\CommentsTable $Comments
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class CommentsController extends AppController
{
    protected ArticlesTable $Articles;

    /**
     * Initializes the controller and loads the Articles table.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Articles = $this->fetchTable('Articles');
    }

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
     * Displays a paginated list of comments with search functionality.
     *
     * @return \Cake\Http\Response|null Renders view or returns search results for AJAX requests.
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $query = $this->Comments->find()
            ->contain([
                'Users',
                'Articles' => function (Query $q) {
                    return $q->select(['Articles.id', 'Articles.title', 'Articles.slug', 'Articles.kind']);
                },
            ]);

        if ($statusFilter !== null) {
            $query->where(['Comments.display' => (int)$statusFilter]);
        }

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'content LIKE' => '%' . $search . '%',
            ]);
        }
        $comments = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('comments'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('comments'));

        return null;
    }

    /**
     * Displays details of a specific comment.
     *
     * @param string|null $id Comment id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When comment is not found.
     */
    public function view(?string $id = null): void
    {
        $comment = $this->Comments->get($id, contain: ['Users', 'Articles']);
        $this->set(compact('comment'));
    }

    /**
     * Edits an existing comment and clears the cache for the associated article.
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When comment is not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $comment = $this->Comments->get($id, contain: ['Articles']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $comment->setAccess('is_inappropriate', true);
            $comment = $this->Comments->patchEntity($comment, $this->request->getData());
            if ($this->Comments->save($comment)) {
                if ($comment->article) {
                    $this->clearContentCache();
                }
                $this->Flash->success(__('The comment has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The comment could not be saved. Please, try again.'));
        }
        $users = $this->Comments->Users->find('list', limit: 200)->all();
        $this->set(compact('comment', 'users'));

        return null;
    }

    /**
     * Deletes a comment and clears the cache for the associated article.
     *
     * @param string|null $id Comment id.
     * @return \Cake\Http\Response Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When comment is not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $comment = $this->Comments->get($id);

        if ($this->Comments->delete($comment)) {
            if ($comment->article) {
                $this->clearContentCache();
            }

            $this->Flash->success(__('The comment has been deleted.'));
        } else {
            $this->Flash->error(__('The comment could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
