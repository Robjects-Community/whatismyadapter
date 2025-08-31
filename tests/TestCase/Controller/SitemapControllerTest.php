<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * SitemapController Test Case
 */
class SitemapControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures needed for the test case
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Tags',
        'app.Articles',
        'app.Settings',
    ];

    /**
     * Test basic sitemap structure and response
     *
     * @return void
     */
    public function testSitemapBasicStructure(): void
    {
        $this->get('/en/sitemap.xml');

        $this->assertResponseOk();
        $this->assertContentType('application/xml');
        $this->assertResponseContains('<?xml version="1.0" encoding="UTF-8"?>');
        $this->assertResponseContains('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
    }

    /**
     * Test published pages in sitemap
     *
     * @return void
     */
    public function testPublishedPagesInSitemap(): void
    {
        $this->get('/en/sitemap.xml');
        //debug($this->_response);
        // Test published pages
        $publishedPages = [
            'page-one',
            'page-four',
        ];

        foreach ($publishedPages as $slug) {
            $pageUrl = Router::url([
                '_name' => 'page-by-slug',
                $slug,
                '_full' => true,
            ]);
            $this->assertResponseContains('<loc>' . $pageUrl . '</loc>');
            $this->assertResponseContains('<changefreq>weekly</changefreq>');
            $this->assertResponseContains('<priority>0.8</priority>');
        }
    }

    /**
     * Test published articles in sitemap
     *
     * @return void
     */
    public function testPublishedArticlesInSitemap(): void
    {
        $this->get('/en/sitemap.xml');

        // Test published articles
        $publishedArticles = [
            'article-one',
            'article-two',
            'article-three',
            'article-four',
            'article-six',
        ];

        foreach ($publishedArticles as $slug) {
            $articleUrl = Router::url([
                '_name' => 'article-by-slug',
                $slug,
                '_full' => true,
            ]);
            $this->assertResponseContains('<loc>' . $articleUrl . '</loc>');
            $this->assertResponseContains('<changefreq>daily</changefreq>');
            $this->assertResponseContains('<priority>0.6</priority>');
        }
    }

    /**
     * Test unpublished content exclusion from sitemap
     *
     * @return void
     */
    public function testUnpublishedContentExclusion(): void
    {
        $this->get('/en/sitemap.xml');

        // Test unpublished pages
        $unpublishedPages = [
            'page-five',
            'page-six',
        ];

        foreach ($unpublishedPages as $slug) {
            $pageUrl = Router::url([
                '_name' => 'page-by-slug',
                $slug,
                '_full' => true,
            ]);
            $this->assertResponseNotContains('<loc>' . $pageUrl . '</loc>');
        }

        // Test unpublished article
        $articleUrl = Router::url([
            '_name' => 'article-by-slug',
            'article-five',
            '_full' => true,
        ]);
        $this->assertResponseNotContains('<loc>' . $articleUrl . '</loc>');
    }

    /**
     * Test homepage inclusion in sitemap
     *
     * @return void
     */
    public function testHomepageInSitemap(): void
    {
        $this->get('/en/sitemap.xml');
        //debug($this->_response);
        $homepageUrl = Router::url(['_name' => 'home', 'lang' => 'en'], true);
        $this->assertResponseContains('<loc>' . $homepageUrl . '</loc>');
        $this->assertResponseContains('<changefreq>daily</changefreq>');
        $this->assertResponseContains('<priority>1.0</priority>');
    }

    /**
     * Test lastmod dates format in sitemap
     *
     * @return void
     */
    public function testLastModifiedDatesFormat(): void
    {
        $this->get('/en/sitemap.xml');

        // Test for properly formatted dates (YYYY-MM-DD)
        $this->assertResponseRegExp('/<lastmod>\d{4}-\d{2}-\d{2}<\/lastmod>/');

        // Test specific known modification date from fixture
        $this->assertResponseContains('<lastmod>2024-09-27</lastmod>');
    }

    /**
     * Test sitemap content ordering
     *
     * @return void
     */
    public function testSitemapContentOrdering(): void
    {
        $this->get('/en/sitemap.xml');
        $response = (string)$this->_response->getBody();

        // Homepage should appear first
        $homepagePosition = strpos($response, Router::url('/', true));

        // Get positions of a page and article URL
        $pagePosition = strpos($response, 'page-one');
        $articlePosition = strpos($response, 'article-one');

        $this->assertNotFalse($homepagePosition);
        $this->assertNotFalse($pagePosition);
        $this->assertNotFalse($articlePosition);

        // Verify ordering: homepage -> pages -> articles
        $this->assertLessThan($pagePosition, $homepagePosition);
        $this->assertLessThan($articlePosition, $pagePosition);
    }

    /**
     * Test sitemap accessibility without authentication
     *
     * @return void
     */
    public function testSitemapAccessibility(): void
    {
        // Test without authentication
        $this->get('/en/sitemap.xml');
        $this->assertResponseOk();

        // Test with invalid authentication
        $this->configRequest([
            'headers' => ['Authorization' => 'Bearer invalid-token'],
        ]);
        $this->get('/en/sitemap.xml');
        $this->assertResponseOk();
    }

    /**
     * Test sitemap with single language (no hreflang)
     *
     * @return void
     */
    public function testSitemapSingleLanguage(): void
    {
        // Clear cache
        Cache::clear('default');

        $this->get('/en/sitemap.xml');

        $this->assertResponseOk();

        // Should not have xhtml namespace when only one language
        $this->assertResponseNotContains('xmlns:xhtml');
        $this->assertResponseNotContains('<xhtml:link');
        $this->assertResponseNotContains('hreflang');
    }

    /**
     * Test sitemap caching behavior
     *
     * @return void
     */
    public function testSitemapCaching(): void
    {
        // Clear cache first
        Cache::clear('default');

        // First request should generate fresh sitemap
        $this->get('/en/sitemap.xml');
        $this->assertResponseOk();

        // Second request should use cache
        $this->get('/en/sitemap.xml');
        $this->assertResponseOk();

        // Check cache headers
        $this->assertHeader('Cache-Control', 'public, max-age=86400');
    }
}
