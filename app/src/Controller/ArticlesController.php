<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\PageViewsTable;
use App\Model\Table\SlugsTable;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Routing\Router;

/**
 * Articles Controller
 *
 * Manages article-related operations including viewing, listing, and commenting.
 *
 * @property \App\Model\Table\ArticlesTable $Articles
 * @property \App\Model\Table\PageViewsTable $PageViews
 * @property \App\Model\Table\SlugsTable $Slugs
 */
class ArticlesController extends AppController
{
    /**
     * Default pagination configuration.
     *
     * Defines the default settings for paginating article records.
     * The limit determines how many articles are displayed per page.
     *
     * @var array<string, mixed> $paginate Configuration array for pagination
     */
    protected array $paginate = [
        'limit' => 6,
    ];

    /**
     * PageViews Table
     *
     * @var \App\Model\Table\PageViewsTable $PageViews
     *
     * This property holds an instance of the PageViewsTable class.
     * It is used to interact with the page_views table in the database.
     * The PageViewsTable provides methods for querying and manipulating
     * page view data, such as tracking article views and retrieving view statistics.
     */
    protected PageViewsTable $PageViews;

    /**
     * Slugs Table
     *
     * @var \App\Model\Table\SlugsTable $Slugs
     *
     * This property holds an instance of the SlugsTable class.
     * It is used to interact with the slugs table in the database.
     * The SlugsTable provides methods for querying and manipulating
     * slug data, such as creating new slugs, finding the latest slug
     * for an article, and managing slug history.
     */
    protected SlugsTable $Slugs;

    /**
     * Initializes the controller.
     *
     * Sets up the Slugs and PageViews table instances.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Slugs = $this->fetchTable('Slugs');
        $this->PageViews = $this->fetchTable('PageViews');
    }

    /**
     * Configures authentication for specific actions.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions(['view', 'index', 'viewBySlug', 'pageIndex']);

        if ($this->request->getParam('action') === 'addComment' && $this->request->is('post')) {
            $result = $this->Authentication->getResult();
            if (!$result || !$result->isValid()) {
                $session = $this->request->getSession();
                $session->write('Comment.formData', $this->request->getData());
            }
        }
    }

    /**
     * Displays the page index.
     *
     * Retrieves and sets the root page article and a threaded list of all page articles.
     *
     * @return void
     */
    public function pageIndex(): void
    {
        $article = $this->Articles->find()
            ->orderBy(['lft' => 'ASC'])
            ->where([
                'Articles.kind' => 'page',
                'Articles.is_published' => 1,
            ])
            ->first();

        $articles = $this->Articles->getTree();

        $this->set(compact('article', 'articles'));
    }

    /**
     * Displays a paginated list of published articles.
     *
     * Retrieves published articles with optional tag filtering.
     *
     * @return void
     */
    public function index(): void
    {
        $cacheKey = $this->cacheKey;
        $articles = Cache::read($cacheKey, 'content');
        $selectedTagId = $this->request->getQuery('tag');

        if (!$articles) {
            $query = $this->Articles->find()
                ->where([
                    'Articles.kind' => 'article',
                    'Articles.is_published' => 1,
                ])
                ->contain(['Users', 'Tags'])
                ->orderBy(['Articles.published' => 'DESC']);

            if ($selectedTagId) {
                $query->matching('Tags', function ($q) use ($selectedTagId) {
                    return $q->where(['Tags.id' => $selectedTagId]);
                });
            }

            $year = $this->request->getQuery('year');
            $month = $this->request->getQuery('month');

            if ($year) {
                $conditions = ['YEAR(Articles.published)' => $year];
                if ($month) {
                    $conditions['MONTH(Articles.published)'] = $month;
                }
                $query->where($conditions);
            }

            $articles = $this->paginate($query);
            Cache::write($cacheKey, $articles, 'content');
        }

        $recentArticles = [];
        if ($this->request->getQuery('page') > 1) {
            $recentArticles = $this->Articles->getRecentArticles($this->cacheKey);
        }

        $this->set(compact(
            'articles',
            'selectedTagId',
            'recentArticles',
        ));

        $this->viewBuilder()->setLayout('article_index');
    }

    /**
     * Displays an article by its slug.
     *
     * Retrieves an article using the provided slug, handling caching and redirects.
     *
     * @param string $slug The slug of the article to view.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found.
     */
    public function viewBySlug(string $slug): ?Response
    {
        $cacheKey = $slug . $this->cacheKey;
        $article = Cache::read($cacheKey, 'content');

        if (empty($article)) {
            // If not in cache, we need to check if this is the latest slug
            $slugEntity = $this->Slugs->find()
                ->where([
                    'slug' => $slug,
                    'model' => 'Articles',
                    ])
                ->orderBy(['created' => 'DESC'])
                ->select(['foreign_key'])
                ->first();

            if (!$slugEntity) {
                // If no slug found, try to find the article directly (fallback)
                $article = $this->Articles->find()
                    ->where(['slug' => $slug, 'is_published' => 1])
                    ->first();

                if (!$article) {
                    throw new NotFoundException(__('Article not found'));
                }

                $articleId = $article->id;
            } else {
                $articleId = $slugEntity->foreign_key;
            }

            // Check if it's the latest slug for the article
            $latestSlug = $this->Slugs->find()
                ->where(['foreign_key' => $articleId])
                ->orderBy(['created' => 'DESC'])
                ->select(['slug'])
                ->first();

            // If $slug is not the same as the latestSlug, do a 301 redirect
            if ($latestSlug && $latestSlug->slug !== $slug) {
                return $this->redirect(
                    [
                        'controller' => 'Articles',
                        'action' => 'view-by-slug',
                        'slug' => $latestSlug->slug,
                        '_full' => true,
                    ],
                    301,
                );
            }

            // Fetch the full article with its associations
            $article = $this->Articles->find()
                ->where([
                    'Articles.id' => $articleId,
                    'Articles.is_published' => 1,
                ])
                ->contain([
                    'Users',
                    'Tags',
                    'Comments' => function ($q) {
                        return $q->where(['Comments.display' => 1])
                                ->orderBy(['Comments.created' => 'DESC'])
                                ->contain(['Users']);
                    },
                    'Images',
                ])
                ->first();

            if (!$article) {
                throw new NotFoundException(__('Article not found'));
            }

            Cache::write($cacheKey, $article, 'content');
        }

        $this->viewBuilder()->setLayout($article->kind);

        $selectedTagId = false;

        // Get the child pages and breadcrumbs for the current article
        $childPages = $this->Articles->find('children', for: $article->id)
            ->orderBy(['lft' => 'ASC'])
            ->cache($cacheKey . '_children', 'content')
            ->toArray();

        // Breadcrumbs
        $crumbs = $this->Articles->find('path', for: $article->id)
            ->cache($cacheKey . '_crumbs', 'content')
            ->select(['slug', 'title', 'id'])
            ->all();

        $recentArticles = $this->Articles->getRecentArticles($this->cacheKey, ['Articles.id NOT IN' => [$article->id]]);

        $this->recordPageView($article->id);

        $this->set(compact(
            'article',
            'childPages',
            'selectedTagId',
            'crumbs',
            'recentArticles',
        ));

        return $this->render($article->kind);
    }

    /**
     * Adds a new comment to an article.
     *
     * @param string $articleId The ID of the article to which the comment will be added.
     * @return \Cake\Http\Response|null
     */
    public function addComment(string $articleId): ?Response
    {
        if (!$this->request->getSession()->read('Auth.id')) {
            $this->Flash->error(__('You must be logged in to add a comment.'));

            return $this->redirect($this->referer());
        }

        $article = $this->Articles
            ->find()
            ->where(['id' => $articleId])
            ->contain([])
            ->first();

        if (!$article) {
            $this->Flash->error(__('Article not found.'));

            return $this->redirect($this->referer());
        }

        if (
            (!SettingsManager::read('Comments.articlesEnabled') && $article->kind == 'article')
            || (!SettingsManager::read('Comments.pagesEnabled') && $article->kind == 'page')
        ) {
            $this->Flash->error(__('Comments are not enabled'));

            return $this->redirect($this->referer());
        }

        $userId = $this->request->getSession()->read('Auth.id');
        $content = $this->request->getData('content');

        if ($this->Articles->addComment($articleId, $userId, $content)) {
            $this->Flash->success(__('Your comment has been added.'));
        } else {
            $this->Flash->error(__('Unable to add your comment.'));
        }

        return $this->redirect(Router::url([
            '_name' => 'article-by-slug',
            'slug' => $article->slug,
        ], true));
    }

    /**
     * Records a page view for a given article.
     *
     * @param string $articleId The ID of the article being viewed
     * @return void
     */
    private function recordPageView(string $articleId): void
    {
        $pageView = $this->PageViews->newEmptyEntity();
        $pageView->article_id = $articleId;
        $pageView->ip_address = $this->request->clientIp();
        $pageView->user_agent = $this->request->getHeaderLine('User-Agent');
        $pageView->referer = $this->request->referer();
        $this->PageViews->save($pageView);
    }
}
