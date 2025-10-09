<?php
declare(strict_types=1);

namespace App\Controller;

use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Event\EventInterface;
use Cake\Routing\Router;
use Cake\View\XmlView;

/**
 * RssController handles the generation of RSS feeds for the application.
 */
class RssController extends AppController
{
    /**
     * Before filter method to allow unauthenticated access to the index action.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['index']);
    }

    /**
     * Specifies the view classes to be used for rendering the RSS feed.
     *
     * @return array An array of view class names.
     */
    public function viewClasses(): array
    {
        return [XmlView::class];
    }

    /**
     * Generates the RSS feed for the latest articles.
     *
     * This method fetches published articles, constructs the RSS feed metadata,
     * and sets the necessary view options and response headers.
     *
     * @return void
     */
    public function index(): void
    {
        // Ensure XmlView is used and serialization is enabled so no template is required
        $this->viewBuilder()
            ->setClassName(XmlView::class)
            ->setOption('rootNode', 'rss')
            ->setOption('serialize', ['@version', 'channel'])
            ->setLayout(null);

        $currentLang = $this->request->getParam('lang', 'en');
        $siteName = SettingsManager::read('SEO.siteName');

        $articlesTable = $this->fetchTable('Articles');

        // Get published articles (cached per-language)
        $cacheKey = 'rss_articles_' . $currentLang;
        $articles = Cache::remember($cacheKey, function () use ($articlesTable) {
            return $articlesTable->find('all')
                ->select(['id', 'title', 'slug', 'summary', 'created'])
                ->where([
                    'kind' => 'article',
                    'is_published' => true,
                ])
                ->orderByDesc('created')
                ->all();
        }, 'default');

        $siteUrl = Router::url(['_name' => 'home', 'lang' => $currentLang], true);

        // Build channel metadata
        $channelData = [
            'title' => __('Latest Articles from {0}', $siteName),
            'link' => $siteUrl,
            'description' => __('Latest articles and updates from our website'),
            'language' => $currentLang,
            'copyright' => 'Copyright ' . date('Y') . ' ' . $siteName,
            'generator' => $siteName,
            'docs' => 'https://www.sitemaps.org/protocol.html',
        ];

        // Add image data
        $channelData['image'] = [
            'url' => Router::url('/img/logo.png', true),
            'title' => __('Latest Articles from {0}', $siteName),
            'link' => $siteUrl,
        ];

        // Add items
        foreach ($articles as $article) {
            $articleUrl = Router::url([
                '_name' => 'article-by-slug',
                'slug' => $article->slug,
                'lang' => $currentLang,
            ], true);

            $channelData['item'][] = [
                'title' => $article->title,
                'link' => $articleUrl,
                'description' => strip_tags((string)$article->summary),
                'pubDate' => $article->created->format('r'),
                'guid' => $articleUrl,
                'category' => __('Articles'),
                'author' => SettingsManager::read('Email.reply_email') . ' (' . $siteName . ')',
            ];
        }

        $this->set([
            '@version' => '2.0',
            'channel' => $channelData,
        ]);

        // Set response type and cache headers
        $this->response = $this->response
            ->withType('xml')
            ->withHeader('Content-Type', 'text/xml; charset=UTF-8')
            ->withCache('-1 minute', '-1 minute');
    }
}
