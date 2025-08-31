<?php
declare(strict_types=1);

namespace App\Controller;

use App\Utility\I18nManager;
use Cake\Cache\Cache;
use Cake\Event\EventInterface;
use Cake\I18n\DateTime;
use Cake\Routing\Router;
use Cake\View\XmlView;
use Exception;

/**
 * SitemapController handles the generation of XML sitemaps for the application.
 *
 * This controller generates a sitemap.xml file that includes all published pages,
 * articles, and tags with their respective priorities and change frequencies.
 * The sitemap supports internationalization (i18n) and works across different locales.
 * It also provides a sitemap index file that lists all language-specific sitemaps.
 */
class SitemapController extends AppController
{
    /**
     * Configures authentication requirements before controller actions are executed.
     *
     * This method is called before each action in the controller. It configures which
     * actions can be accessed without authentication. In this case, the 'index' and
     * 'sitemapIndex' actions are allowed to be accessed by unauthenticated users,
     * enabling public access to the sitemap files.
     *
     * @param \Cake\Event\EventInterface $event The event object
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['index']);
    }

    /**
     * Specifies the view classes that this controller can use.
     *
     * @return array<class-string> Array containing XmlView class for sitemap generation
     */
    public function viewClasses(): array
    {
        return [XmlView::class];
    }

    /**
     * Generates the XML sitemap for the application with hreflang support.
     *
     * This method fetches all published pages, articles, and tags from the database
     * and generates a sitemap.xml file according to the sitemap protocol specifications.
     * It includes hreflang annotations for multi-language support.
     *
     * Different content types are assigned different priorities and change frequencies:
     * - Homepage: Priority 1.0, daily changes
     * - Pages: Priority 0.8, weekly changes
     * - Articles: Priority 0.6, daily changes
     * - Tags: Priority 0.4, weekly changes
     *
     * @return void
     * @link https://www.sitemaps.org/protocol.html Sitemap protocol reference
     * @link https://support.google.com/webmasters/answer/189077 Hreflang in sitemaps
     */
    public function index(): void
    {
        try {
            // Get all enabled languages
            $enabledLanguages = $this->getEnabledLanguages();

            // Build cache key based on all languages and last modification
            $lastModified = $this->getOverallLastModifiedDate();
            $cacheKey = 'sitemap_all_' . $lastModified->format('YmdHis');

            // Try to get from cache
            $urls = Cache::read($cacheKey, 'default');

            if ($urls === null) {
                $articlesTable = $this->fetchTable('Articles');

                // Optimize queries by selecting only needed fields
                // Get published hierarchical pages
                $pages = $articlesTable->find('threaded')
                    ->select(['id', 'slug', 'modified', 'lft'])
                    ->where([
                        'kind' => 'page',
                        'is_published' => 1,
                    ])
                    ->orderByAsc('lft')
                    ->all();

                // Get published regular articles
                $articles = $articlesTable->find()
                    ->select(['id', 'slug', 'modified'])
                    ->where([
                        'kind' => 'article',
                        'is_published' => 1,
                    ])
                    ->orderByDesc('modified')
                    ->all();

                // Get all tags
                $tagsTable = $this->fetchTable('Tags');
                $tags = $tagsTable->find()
                    ->select(['id', 'slug', 'modified'])
                    ->orderByAsc('title')
                    ->all();

                $urls = [];

                // Add homepage for all enabled languages
                foreach ($enabledLanguages as $lang) {
                    $urls[] = [
                        'loc' => Router::url([
                            '_name' => 'home',
                            'lang' => $lang,
                            '_full' => true,
                        ]),
                        'changefreq' => 'daily',
                        'priority' => '1.0',
                        'lastmod' => $lastModified->format('Y-m-d'),
                    ];
                }

                // Add pages for all enabled languages
                foreach ($pages as $page) {
                    foreach ($enabledLanguages as $lang) {
                        $urls[] = [
                            'loc' => Router::url([
                                '_name' => 'page-by-slug',
                                'slug' => $page->slug,
                                'lang' => $lang,
                                '_full' => true,
                            ]),
                            'lastmod' => $page->modified->format('Y-m-d'),
                            'changefreq' => 'weekly',
                            'priority' => '0.8',
                        ];
                    }
                }

                // Add articles for all enabled languages
                foreach ($articles as $article) {
                    foreach ($enabledLanguages as $lang) {
                        $urls[] = [
                            'loc' => Router::url([
                                '_name' => 'article-by-slug',
                                'slug' => $article->slug,
                                'lang' => $lang,
                                '_full' => true,
                            ]),
                            'lastmod' => $article->modified->format('Y-m-d'),
                            'changefreq' => 'daily',
                            'priority' => '0.6',
                        ];
                    }
                }

                // Add tags for all enabled languages
                foreach ($tags as $tag) {
                    foreach ($enabledLanguages as $lang) {
                        $urls[] = [
                            'loc' => Router::url([
                                '_name' => 'tag-by-slug',
                                'slug' => $tag->slug,
                                'lang' => $lang,
                                '_full' => true,
                            ]),
                            'lastmod' => $tag->modified->format('Y-m-d'),
                            'changefreq' => 'weekly',
                            'priority' => '0.4',
                        ];
                    }
                }

                // Cache the generated URLs for 1 hour
                Cache::write($cacheKey, $urls, 'default');
            }

            $this->viewBuilder()
                ->setOption('rootNode', 'urlset')
                ->setOption('serialize', ['@xmlns', 'url']);

            $this->set([
                '@xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
                'url' => $urls,
            ]);

            // Set response type and cache headers with smart caching based on last modified
            $this->response = $this->response
                ->withType('xml')
                ->withHeader('Content-Type', 'application/xml')
                ->withCache($lastModified->toUnixString(), '+1 day');
        } catch (Exception $e) {
            $this->log('Sitemap generation failed: ' . $e->getMessage(), 'error');

            // Return empty but valid sitemap on error
            $this->viewBuilder()
                ->setOption('rootNode', 'urlset')
                ->setOption('serialize', ['@xmlns', 'url']);

            $this->set([
                '@xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
                'url' => [],
            ]);

            $this->response = $this->response
                ->withType('xml')
                ->withHeader('Content-Type', 'application/xml');
        }
    }

    /**
     * Gets the list of enabled languages from settings.
     *
     * @return array<string> Array of enabled language codes
     */
    protected function getEnabledLanguages(): array
    {
        // Always include English as default
        $languages = ['en'];

        // Get enabled languages using I18nManager
        $enabledLanguages = array_keys(I18nManager::getEnabledLanguages());

        // Merge with default, removing duplicates
        foreach ($enabledLanguages as $lang) {
            if (!in_array($lang, $languages)) {
                $languages[] = $lang;
            }
        }

        return $languages;
    }

    /**
     * Gets the last modified date for content in a specific language.
     *
     * @param string $language The language code
     * @return \Cake\I18n\DateTime The last modification date
     */
    protected function getLastModifiedDateForLanguage(string $language): DateTime
    {
        $articlesTable = $this->fetchTable('Articles');

        // Get the most recently modified article or page
        $lastArticle = $articlesTable->find()
            ->select(['modified'])
            ->where(['is_published' => 1])
            ->orderByDesc('modified')
            ->first();

        // Get the most recently modified tag
        $tagsTable = $this->fetchTable('Tags');
        $lastTag = $tagsTable->find()
            ->select(['modified'])
            ->orderByDesc('modified')
            ->first();

        $dates = [];
        if ($lastArticle) {
            $dates[] = $lastArticle->modified;
        }
        if ($lastTag) {
            $dates[] = $lastTag->modified;
        }

        // Return the most recent date, or current date if no content
        return !empty($dates) ? max($dates) : new DateTime();
    }

    /**
     * Gets the overall last modified date across all languages.
     *
     * @return \Cake\I18n\DateTime The last modification date
     */
    protected function getOverallLastModifiedDate(): DateTime
    {
        // For now, just use the same logic as single language
        // In the future, this could check translations table
        return $this->getLastModifiedDateForLanguage('en');
    }

    /**
     * Generates hreflang links for a given route and entity.
     *
     * @param string $routeName The route name
     * @param \Cake\Datasource\EntityInterface|null $entity The entity (article, page, tag)
     * @param array<string> $languages Array of enabled language codes
     * @return array<array> Array of hreflang link data
     */
    protected function generateHreflangLinks(string $routeName, ?object $entity, array $languages): array
    {
        $links = [];

        foreach ($languages as $lang) {
            $urlParams = [
                '_name' => $routeName,
                'lang' => $lang,
                '_full' => true,
            ];

            // Add slug parameter if entity is provided
            if ($entity !== null && isset($entity->slug)) {
                $urlParams['slug'] = $entity->slug;
            }

            $links[] = [
                '@rel' => 'alternate',
                '@hreflang' => $lang,
                '@href' => Router::url($urlParams),
            ];
        }

        // Add x-default for the primary language (first in the list)
        if (!empty($languages)) {
            $defaultUrlParams = [
                '_name' => $routeName,
                'lang' => $languages[0],
                '_full' => true,
            ];

            if ($entity !== null && isset($entity->slug)) {
                $defaultUrlParams['slug'] = $entity->slug;
            }

            $links[] = [
                '@rel' => 'alternate',
                '@hreflang' => 'x-default',
                '@href' => Router::url($defaultUrlParams),
            ];
        }

        return $links;
    }
}
