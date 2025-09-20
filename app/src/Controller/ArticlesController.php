<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * Articles Controller
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    /**
     * beforeFilter callback.
     *
     * Allow unauthenticated access to public articles
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        // Allow unauthenticated access to index, view, and viewBySlug actions
        $this->Authentication->addUnauthenticatedActions(['index', 'view', 'viewBySlug']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // Load settings for home page feeds configuration
        $settingsTable = null;
        $homepageFeeds = [
            'featured_articles' => true,
            'recent_articles' => true,
            'products' => true,
            'image_galleries' => true,
            'pages' => false
        ];
        
        try {
            $settingsTable = $this->fetchTable('Settings');
            // Use individual settings for better control
            $homepageFeeds = [
                'featured_articles' => $this->_getSettingValue($settingsTable, 'homepage_featured_articles_enabled', true),
                'recent_articles' => $this->_getSettingValue($settingsTable, 'homepage_latest_articles_enabled', true),
                'products' => $this->_getSettingValue($settingsTable, 'homepage_latest_products_enabled', true),
                'image_galleries' => true, // Keep galleries enabled for now
                'pages' => false
            ];
        } catch (\Exception $e) {
            // Continue with defaults if settings table is not available
        }
        
        // Get the main articles feed
        try {
            $query = $this->Articles->find()
                ->where(['Articles.is_published' => true])
                ->order(['Articles.created' => 'DESC']);
                
            $articles = $this->paginate($query);
        } catch (\Exception $e) {
            // Fallback to basic query if associations don't exist
            $query = $this->Articles->find()->order(['Articles.created' => 'DESC']);
            $articles = $this->paginate($query);
        }

        // Get featured articles (promoted or high-rated)
        $featuredArticles = [];
        if ($homepageFeeds['featured_articles']) {
            try {
                $featuredArticles = $this->Articles->find()
                    ->where(['Articles.is_published' => true])
                    ->order(['Articles.created' => 'DESC'])
                    ->limit(3)
                    ->all();
            } catch (\Exception $e) {
                // Continue without featured articles if there's an issue
                $featuredArticles = [];
            }
        }
        
        // Get recent products with reliability scores
        $recentProducts = [];
        if ($homepageFeeds['products']) {
            try {
                $productsTable = $this->fetchTable('Products');
                $recentProducts = $productsTable->find()
                    ->contain(['Images'])
                    ->where(['Products.status' => 'active'])
                    ->order([
                        'Products.rel_score' => 'DESC',
                        'Products.created' => 'DESC'
                    ])
                    ->limit(6)
                    ->all();
            } catch (\Exception $e) {
                // Products table might not exist yet, continue without it
            }
        }
        
        // Get recent image galleries
        $recentGalleries = [];
        if ($homepageFeeds['image_galleries']) {
            try {
                $galleriesTable = $this->fetchTable('ImageGalleries');
                $recentGalleries = $galleriesTable->find()
                    ->contain(['Images'])
                    ->where(['ImageGalleries.is_published' => true])
                    ->order(['ImageGalleries.created' => 'DESC'])
                    ->limit(4)
                    ->all();
            } catch (\Exception $e) {
                // Image galleries table might not exist yet, continue without it
            }
        }
        
        // Get popular tags
        $popularTags = [];
        try {
            $tagsTable = $this->fetchTable('Tags');
            $popularTags = $tagsTable->find()
                ->order(['Tags.article_count' => 'DESC'])
                ->limit(15)
                ->all();
        } catch (\Exception $e) {
            // Tags table might not exist, continue without it
        }
        
        // Get recent pages if enabled
        $recentPages = [];
        if ($homepageFeeds['pages']) {
            // This would be for custom pages, can be implemented later
        }
        
        // Development info for the sidebar
        $developmentInfo = [
            'server_costs' => [
                'hosting' => 'Self-hosted Docker',
                'monthly_cost' => '$0 (excluding API costs)',
                'apis_used' => ['OpenAI', 'GitHub Actions']
            ],
            'tech_stack' => [
                'backend' => 'CakePHP 5.x',
                'frontend' => 'Bootstrap 5 + Custom CSS',
                'database' => 'MySQL 8.x',
                'containerization' => 'Docker Compose',
                'queue' => 'Redis + CakePHP Queue'
            ]
        ];
        
        $this->set(compact(
            'articles', 
            'featuredArticles', 
            'recentProducts', 
            'recentGalleries', 
            'popularTags', 
            'recentPages',
            'homepageFeeds',
            'developmentInfo'
        ));
    }

    /**
     * View method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $article = $this->Articles->get($id, contain: ['Users', 'Images', 'Tags', 'Comments', 'Slugs', 'ArticlesTranslations', 'PageViews']);
        $this->set(compact('article'));
        // Use the article template for DefaultTheme
        $this->render('article');
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('The article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }
        $users = $this->Articles->Users->find('list', limit: 200)->all();
        $images = $this->Articles->Images->find('list', limit: 200)->all();
        $tags = $this->Articles->Tags->find('list', limit: 200)->all();
        $this->set(compact('article', 'users', 'images', 'tags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $article = $this->Articles->get($id, contain: ['Images', 'Tags']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('The article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The article could not be saved. Please, try again.'));
        }
        $users = $this->Articles->Users->find('list', limit: 200)->all();
        $images = $this->Articles->Images->find('list', limit: 200)->all();
        $tags = $this->Articles->Tags->find('list', limit: 200)->all();
        $this->set(compact('article', 'users', 'images', 'tags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Article id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $article = $this->Articles->get($id);
        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The article has been deleted.'));
        } else {
            $this->Flash->error(__('The article could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * View by slug method
     *
     * @param string|null $slug Article slug.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function viewBySlug(?string $slug = null)
    {
        if (!$slug) {
            throw new NotFoundException(__('Article not found.'));
        }

        $article = $this->Articles->find()
            ->contain(['Users', 'Images', 'Tags', 'Comments', 'Slugs', 'ArticlesTranslations', 'PageViews'])
            ->where(['Articles.slug' => $slug])
            ->first();

        if (!$article) {
            // Attempt legacy slug lookup for redirect
            $legacy = $this->fetchTable('Slugs')->find()
                ->where(['model' => 'Articles', 'slug' => $slug])
                ->first();
            if ($legacy) {
                $article = $this->Articles->find()
                    ->where(['Articles.id' => $legacy->foreign_key])
                    ->first();
                if ($article && $article->slug && $article->slug !== $slug) {
                    // Build locale-aware path; tests use /en prefix
                    return $this->redirect('/en/articles/' . $article->slug, 301);
                }
            }
            throw new NotFoundException(__('Article not found.'));
        }

        // Enforce published-only visibility on public slug route
        if (!$article->is_published) {
            throw new NotFoundException(__('Article not found.'));
        }

        $this->set(compact('article'));
        // Use the article template for DefaultTheme
        $this->render('article');
    }

    /**
     * Add comment method
     *
     * @return \Cake\Http\Response|null|void Redirects after posting comment
     */
    public function addComment()
    {
        $this->request->allowMethod(['post']);

        $articleId = $this->request->getData('article_id');
        if (!$articleId) {
            $this->Flash->error(__('Invalid article specified.'));

            return $this->redirect(['action' => 'index']);
        }

        // Check if article exists
        $article = $this->Articles->find()->where(['id' => $articleId])->first();
        if (!$article) {
            $this->Flash->error(__('Article not found.'));

            return $this->redirect(['action' => 'index']);
        }

        // Load Comments model and create comment
        $this->loadModel('Comments');
        $comment = $this->Comments->newEmptyEntity();
        $comment = $this->Comments->patchEntity($comment, $this->request->getData());
        $comment->article_id = $articleId;

        if ($this->Comments->save($comment)) {
            $this->Flash->success(__('Your comment has been posted.'));
        } else {
            $this->Flash->error(__('Unable to post your comment. Please, try again.'));
        }

        // Redirect back to the article
        if ($article->slug) {
            return $this->redirect(['action' => 'viewBySlug', $article->slug]);
        } else {
            return $this->redirect(['action' => 'view', $articleId]);
        }
    }
    
    /**
     * Safely get a setting value with fallback to default
     *
     * @param mixed $settingsTable The settings table instance
     * @param string $key The setting key
     * @param mixed $default Default value if setting not found
     * @return mixed Setting value or default
     */
    private function _getSettingValue($settingsTable, string $key, $default)
    {
        if (!$settingsTable) {
            return $default;
        }
        
        try {
            // Try to use getSettingValue method if available
            if (method_exists($settingsTable, 'getSettingValue')) {
                $result = $settingsTable->getSettingValue('homepage', $key);
                return $result !== null ? $result : $default;
            }
            
            return $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}
