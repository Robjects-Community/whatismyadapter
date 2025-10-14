<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Table\ArticlesTable;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Log\LogTrait;
use DateTime;
use Exception;

/**
 * PageViews Controller
 *
 * Manages page view statistics for articles.
 *
 * @property \App\Model\Table\PageViewsTable $PageViews
 */
class PageViewsController extends AppController
{
    use LogTrait;

    /**
     * Articles Table
     *
     * @var \App\Model\Table\ArticlesTable
     * This property holds an instance of the ArticlesTable class.
     * It is used to interact with the articles table in the database.
     * The ArticlesTable class provides methods for querying and manipulating
     * article data, such as finding, saving, and deleting articles.
     */
    protected ArticlesTable $Articles;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Articles = $this->fetchTable('Articles');
    }

    /**
     * Retrieves page view statistics for a specific article.
     *
     * This method fetches an article by its ID and retrieves the number of page views
     * grouped by date. It then sets the data to be used in the view.
     *
     * @param string $articleId The ID of the article to retrieve statistics for
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found
     */
    public function pageViewStats(string $articleId): void
    {
        $article = $this->Articles->find()
            ->select(['id', 'title', 'slug'])
            ->where(['id' => $articleId])
            ->first();

        if (!$article) {
            throw new NotFoundException(__('Article not found'));
        }

        $viewsOverTime = $this->PageViews->find()
            ->where(['article_id' => $articleId])
            ->select([
                'date' => 'DATE(created)',
                'count' => $this->PageViews->find()->func()->count('*'),
            ])
            ->groupBy('DATE(created)')
            ->orderBy(['DATE(created)' => 'DESC'])
            ->all();

        $this->set(compact('viewsOverTime', 'article'));
    }

    /**
     * Retrieves view records for a specific article.
     *
     * This method fetches an article by its ID and retrieves all associated page view records.
     * If a date query parameter is provided, it filters the page views by that date.
     * The results are then set to be available in the view.
     *
     * @param string $articleId The ID of the article to retrieve view records for
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException If the article is not found
     */
    public function viewRecords(string $articleId): void
    {
        $article = $this->Articles->find()
            ->select(['id', 'title', 'slug'])
            ->where(['id' => $articleId])
            ->first();

        if (!$article) {
            throw new NotFoundException(__('Article not found'));
        }

        $query = $this->PageViews->find()
            ->where(['article_id' => $articleId])
            ->orderBy(['created' => 'DESC']);

        if ($this->request->getQuery('date')) {
            $date = new DateTime($this->request->getQuery('date'));
            $query->where([
                'DATE(created)' => $date->format('Y-m-d'),
            ]);
        }

        $viewRecords = $query->all();

        $this->set(compact('viewRecords', 'article'));
    }

    /**
     * Filters page view statistics for a specific article based on date range.
     *
     * @param string $articleId The ID of the article to retrieve statistics for
     * @return \Cake\Http\Response|null JSON response with filtered data or error message
     */
    public function filterStats(string $articleId): ?Response
    {
        if (Configure::read('debug')) {
            $this->log('Filter request received for article ID: ' . $articleId, 'debug');
            $this->log('Start date: ' . $this->request->getQuery('start'), 'debug');
            $this->log('End date: ' . $this->request->getQuery('end'), 'debug');
        }

        try {
            $article = $this->Articles->find()
                ->select(['id', 'title', 'slug'])
                ->where(['id' => $articleId])
                ->first();

            if (!$article) {
                throw new NotFoundException(__('Article not found'));
            }

            $startDate = new DateTime($this->request->getQuery('start'));
            $endDate = new DateTime($this->request->getQuery('end'));

            $viewsOverTime = $this->PageViews->find()
                ->where([
                    'article_id' => $articleId,
                    'created >=' => $startDate->format('Y-m-d'),
                    'created <=' => $endDate->format('Y-m-d 23:59:59'),
                ])
                ->select([
                    'date' => 'DATE(created)',
                    'count' => $this->PageViews->find()->func()->count('*'),
                ])
                ->groupBy('DATE(created)')
                ->orderBy(['DATE(created)' => 'ASC'])
                ->all();

            $totalViews = array_sum(array_column($viewsOverTime->toArray(), 'count'));

            $filteredData = [
                'viewsOverTime' => $viewsOverTime,
                'totalViews' => $totalViews,
            ];

            if (Configure::read('debug')) {
                $this->log('Filtered data: ' . json_encode($filteredData), 'debug');
            }

            return $this->response->withType('application/json')->withStringBody(json_encode($filteredData));
        } catch (Exception $e) {
            $this->log('Error in filterStats: ' . $e->getMessage(), 'error');

            $errorMsg = __('An error occurred while processing your request.');

            return $this->response->withStatus(500)
                ->withType('application/json')
                ->withStringBody(json_encode(['error' => $errorMsg]));
        }
    }

    /**
     * Enhanced analytics dashboard with comprehensive metrics
     *
     * @return void
     */
    public function dashboard(): void
    {
        // Get date range from request or default to last 30 days
        $endDate = new DateTime();
        $startDate = (clone $endDate)->modify('-30 days');

        if ($this->request->getQuery('start')) {
            $startDate = new DateTime($this->request->getQuery('start'));
        }
        if ($this->request->getQuery('end')) {
            $endDate = new DateTime($this->request->getQuery('end'));
        }

        // Overall statistics
        $totalViews = $this->PageViews->find()
            ->where([
                'created >=' => $startDate->format('Y-m-d'),
                'created <=' => $endDate->format('Y-m-d 23:59:59'),
            ])
            ->count();

        $uniqueVisitors = $this->PageViews->find()
            ->where([
                'created >=' => $startDate->format('Y-m-d'),
                'created <=' => $endDate->format('Y-m-d 23:59:59'),
            ])
            ->select(['ip_address'])
            ->distinct(['ip_address'])
            ->count();

        // Views over time
        $viewsOverTime = $this->PageViews->find()
            ->where([
                'created >=' => $startDate->format('Y-m-d'),
                'created <=' => $endDate->format('Y-m-d 23:59:59'),
            ])
            ->select([
                'date' => 'DATE(created)',
                'count' => $this->PageViews->find()->func()->count('*'),
            ])
            ->groupBy('DATE(created)')
            ->orderBy(['DATE(created)' => 'ASC'])
            ->all();

        // Top articles
        $topArticles = $this->PageViews->find()
            ->contain(['Articles' => ['fields' => ['id', 'title', 'slug']]])
            ->where([
                'PageViews.created >=' => $startDate->format('Y-m-d'),
                'PageViews.created <=' => $endDate->format('Y-m-d 23:59:59'),
            ])
            ->select([
                'article_id',
                'count' => $this->PageViews->find()->func()->count('*'),
            ])
            ->groupBy(['article_id'])
            ->orderBy(['count' => 'DESC'])
            ->limit(10)
            ->all();

        // Get additional analytics data
        $browserStats = $this->getBrowserStats($startDate, $endDate);
        $hourlyDistribution = $this->getHourlyDistribution($startDate, $endDate);
        $topReferrers = $this->getTopReferrers($startDate, $endDate);

        $this->set(compact(
            'totalViews',
            'uniqueVisitors',
            'viewsOverTime',
            'topArticles',
            'browserStats',
            'hourlyDistribution',
            'topReferrers',
            'startDate',
            'endDate',
        ));
    }

    /**
     * Get browser statistics
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @return array Browser statistics
     */
    private function getBrowserStats(DateTime $startDate, DateTime $endDate): array
    {
        $results = $this->PageViews->find()
            ->where([
                'created >=' => $startDate->format('Y-m-d'),
                'created <=' => $endDate->format('Y-m-d 23:59:59'),
            ])
            ->select(['user_agent'])
            ->all();

        $browserCounts = [];
        foreach ($results as $result) {
            $userAgent = $result->user_agent ?? '';
            $browser = $this->extractBrowser($userAgent);
            $browserCounts[$browser] = ($browserCounts[$browser] ?? 0) + 1;
        }

        arsort($browserCounts);

        return array_slice($browserCounts, 0, 10, true);
    }

    /**
     * Extract browser name from user agent string
     *
     * @param string $userAgent User agent string
     * @return string Browser name
     */
    private function extractBrowser(string $userAgent): string
    {
        $browsers = [
            'Chrome' => '/Chrome\/[\d.]+/',
            'Firefox' => '/Firefox\/[\d.]+/',
            'Safari' => '/Safari\/[\d.]+/',
            'Edge' => '/Edg\/[\d.]+/',
            'Opera' => '/OPR\/[\d.]+/',
            'Internet Explorer' => '/MSIE [\d.]+/',
        ];

        foreach ($browsers as $browser => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $browser;
            }
        }

        return 'Other';
    }

    /**
     * Get hourly distribution of views
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @return array Hourly distribution
     */
    private function getHourlyDistribution(DateTime $startDate, DateTime $endDate): array
    {
        $results = $this->PageViews->find()
            ->where([
                'created >=' => $startDate->format('Y-m-d'),
                'created <=' => $endDate->format('Y-m-d 23:59:59'),
            ])
            ->select([
                'hour' => 'HOUR(created)',
                'count' => $this->PageViews->find()->func()->count('*'),
            ])
            ->groupBy('HOUR(created)')
            ->orderBy(['hour' => 'ASC'])
            ->all();

        $hourlyData = array_fill(0, 24, 0);
        foreach ($results as $result) {
            $hourlyData[(int)$result->hour] = $result->count;
        }

        return $hourlyData;
    }

    /**
     * Get top referrers
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @return array Top referrers
     */
    private function getTopReferrers(DateTime $startDate, DateTime $endDate): array
    {
        $results = $this->PageViews->find()
            ->where([
                'created >=' => $startDate->format('Y-m-d'),
                'created <=' => $endDate->format('Y-m-d 23:59:59'),
                'referer IS NOT' => null,
                'referer !=' => '',
            ])
            ->select([
                'referer',
                'count' => $this->PageViews->find()->func()->count('*'),
            ])
            ->groupBy(['referer'])
            ->orderBy(['count' => 'DESC'])
            ->limit(10)
            ->all();

        $referrers = [];
        foreach ($results as $result) {
            $domain = parse_url($result->referer, PHP_URL_HOST) ?? $result->referer;
            $referrers[] = [
                'domain' => $domain,
                'count' => $result->count,
                'url' => $result->referer,
            ];
        }

        return $referrers;
    }
}
