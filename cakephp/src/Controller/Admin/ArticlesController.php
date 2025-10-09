<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Exception;

/**
 * Articles Controller
 *
 * Handles CRUD operations for articles, including pages and blog posts.
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
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
     * Retrieves a hierarchical list of articles that are marked as pages.
     *
     * @return void
     */
    public function treeIndex(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $menuFilter = $this->request->getQuery('menu');
        $conditions = [
            'Articles.kind' => 'page',
        ];

        if ($statusFilter === '1') {
            $conditions['Articles.is_published'] = '1';
        } elseif ($statusFilter === '0') {
            $conditions['Articles.is_published'] = '0';
        }

        // Apply menu filter
        if ($menuFilter === 'header') {
            $conditions['Articles.main_menu'] = 1;
        } elseif ($menuFilter === 'footer') {
            $conditions['Articles.footer_menu'] = 1;
        } elseif ($menuFilter === 'both') {
            $conditions['Articles.main_menu'] = 1;
            $conditions['Articles.footer_menu'] = 1;
        } elseif ($menuFilter === 'none') {
            $conditions['Articles.main_menu'] = 0;
            $conditions['Articles.footer_menu'] = 0;
        }

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $conditions['OR'] = [
                    'Articles.title LIKE' => '%' . $search . '%',
                    'Articles.slug LIKE' => '%' . $search . '%',
                    'Articles.body LIKE' => '%' . $search . '%',
                    'Articles.meta_title LIKE' => '%' . $search . '%',
                    'Articles.meta_description LIKE' => '%' . $search . '%',
                    'Articles.meta_keywords LIKE' => '%' . $search . '%',
                ];
            }
            $articles = $this->Articles->getTree($conditions, [
                'slug',
                'created',
                'modified',
                'is_published',
                'main_menu',
                'footer_menu',
            ]);

            $this->set(compact('articles'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('tree_index_search_results');
        }

        $articles = $this->Articles->getTree($conditions, [
            'slug',
            'created',
            'modified',
            'view_count',
            'is_published',
            'main_menu',
            'footer_menu',
        ]);
        $this->set(compact('articles'));

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
            $result = $this->Articles->reorder($data);
            $this->clearContentCache();

            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'result' => $result]));
        } catch (Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => false, 'error' => $e->getMessage()]));
        }
    }

    /**
     * Displays a list of articles with search functionality.
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');

        $query = $this->Articles->find()
            ->select([
                'Articles.id',
                'Articles.user_id',
                'Articles.title',
                'Articles.slug',
                'Articles.image',
                'Articles.dir',
                'Articles.alt_text',
                'Articles.created',
                'Articles.modified',
                'Articles.published',
                'Articles.is_published',
                'Articles.body',
                'Articles.summary',
                'Articles.meta_title',
                'Articles.meta_description',
                'Articles.meta_keywords',
                'Articles.linkedin_description',
                'Articles.facebook_description',
                'Articles.instagram_description',
                'Articles.twitter_description',
                'Articles.word_count',
                'Articles.view_count',
                'Users.id',
                'Users.username',
            ])
            ->leftJoinWith('Users')
            ->leftJoinWith('PageViews')
            ->where(['Articles.kind' => 'article'])
            ->groupBy([
                'Articles.id',
                'Articles.user_id',
                'Articles.title',
                'Articles.slug',
                'Articles.created',
                'Articles.modified',
                'Users.id',
                'Users.username',
            ])
            ->orderBy(['Articles.created' => 'DESC']);

        if ($statusFilter !== null) {
            $query->where(['Articles.is_published' => (int)$statusFilter]);
        }

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Articles.title LIKE' => '%' . $search . '%',
                    'Articles.slug LIKE' => '%' . $search . '%',
                    'Articles.body LIKE' => '%' . $search . '%',
                    'Articles.meta_title LIKE' => '%' . $search . '%',
                    'Articles.meta_description LIKE' => '%' . $search . '%',
                    'Articles.meta_keywords LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $articles = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('articles', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('articles'));

        return null;
    }

    /**
     * Displays details of a specific article.
     *
     * @param string|null $id Article id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $article = $this->Articles->get($id, contain: [
            'Users',
            'PageViews',
            'Tags',
            'Images',
            'Slugs',
            'Comments',
        ]);

        if (!$article) {
            throw new RecordNotFoundException(__('Article not found'));
        }

        $this->set(compact('article'));
    }

    /**
     * Adds a new article.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $article = $this->Articles->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['kind'] = $this->request->getQuery('kind', 'article');
            $article = $this->Articles->patchEntity($article, $data);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads');
            if (!empty($imageUploads['image_uploads'])) {
                $article->imageUploads = $imageUploads['image_uploads'];
            }

            if ($this->Articles->save($article)) {
                $this->clearContentCache();
                $this->Flash->success(__('The article has been saved.'));

                // Redirect to treeIndex if is page, otherwise to index
                if ($article->kind == 'page') {
                    return $this->redirect(['action' => 'treeIndex']);
                } else {
                    return $this->redirect(['action' => 'index']);
                }
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }

        // Fetch parent articles if 'kind' is page
        $parentArticles = [];
        if ($this->request->getQuery('kind') == 'page') {
            $parentArticles = $this->Articles->find('list')
                ->where(['kind' => 'page'])
                ->all();
        }

        // Get parent inheritance data for menu settings (in case parent_id is set in query params)
        $parentInheritance = [];
        $parentId = $this->request->getQuery('parent_id');
        if (!empty($parentId)) {
            try {
                $parent = $this->Articles->get($parentId);
                $parentInheritance = [
                    'main_menu' => $parent->main_menu ?? false,
                    'footer_menu' => $parent->footer_menu ?? false,
                    'parent_title' => $parent->title ?? '',
                ];
            } catch (RecordNotFoundException $e) {
                // Parent not found, continue without inheritance data
            }
        }

        $users = $this->Articles->Users->find('list', limit: 200)->all();
        $tags = $this->Articles->Tags->find('list', limit: 200)->all();
        $token = $this->request->getAttribute('csrfToken');
        $this->set(compact('article', 'users', 'tags', 'token', 'parentArticles', 'parentInheritance'));

        return null;
    }

    /**
     * Edits an existing article.
     *
     * @param string|null $id Article ID.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $article = $this->Articles->get($id, contain: ['Tags', 'Images']);

        if (!empty($article->body) && empty($article->markdown)) {
            $article->markdown = $article->body;
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $data['kind'] = $this->request->getQuery('kind', 'article');
            $article = $this->Articles->patchEntity($article, $data);

            // Handle image uploads
            $imageUploads = $this->request->getUploadedFiles('image_uploads') ?? [];
            if (!empty($imageUploads['image_uploads'])) {
                $article->imageUploads = $imageUploads['image_uploads'];
            }

            // Handle image unlinking
            $unlinkedImages = $this->request->getData('unlink_images') ?? [];
            $article->unlinkedImages = $unlinkedImages;

            $saveOptions = [];
            if (isset($data['regenerateTags'])) {
                $saveOptions['regenerateTags'] = $data['regenerateTags'];
            }

            if ($this->Articles->save($article, $saveOptions)) {
                $this->clearContentCache();
                $this->Flash->success(__('The article has been saved.'));

                // Redirect to treeIndex if kind is page, otherwise to index
                if ($article->kind == 'page') {
                    return $this->redirect(['action' => 'treeIndex']);
                } else {
                    return $this->redirect(['action' => 'index']);
                }
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }

        // Fetch parent articles if 'kind' is page
        $parentArticles = [];
        if ($this->request->getQuery('kind') == 'page') {
            $parentArticles = $this->Articles->find('list')
                ->where([
                    'kind' => 'page',
                    'id !=' => $id,
                ])
                ->all();
        }

        // Get parent inheritance data for menu settings
        $parentInheritance = [];
        if (!empty($article->parent_id)) {
            $parent = $this->Articles->get($article->parent_id);
            $parentInheritance = [
                'main_menu' => $parent->main_menu ?? false,
                'footer_menu' => $parent->footer_menu ?? false,
                'parent_title' => $parent->title ?? '',
            ];
        }

        $users = $this->Articles->Users->find('list', limit: 200)->all();
        $tags = $this->Articles->Tags->find('list', limit: 200)->all();
        $this->set(compact('article', 'users', 'tags', 'parentArticles', 'parentInheritance'));

        return null;
    }

    /**
     * Deletes an article.
     *
     * @param string|null $id Article ID.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $article = $this->Articles->get($id);
        if ($this->Articles->delete($article)) {
            $this->clearContentCache();

            $this->Flash->success(__('The article has been deleted.'));
        } else {
            $this->Flash->error(__('The article could not be deleted. Please, try again.'));
        }

        // Check if 'kind' is in the request parameters
        if ($this->request->getData('kind') == 'page') {
            $this->redirect(['action' => 'treeIndex']);
        }

        $action = $this->request->getQuery('kind') == 'page' ? 'tree-index' : 'index';

        $this->redirect(['action' => $action]);
    }
}
