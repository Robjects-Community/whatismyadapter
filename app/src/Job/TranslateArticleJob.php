<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Google\GoogleApiService;
use App\Utility\SettingsManager;
use Cake\Queue\Job\Message;
use Cake\Queue\QueueManager;
use Interop\Queue\Processor;

class TranslateArticleJob extends AbstractJob
{
    /**
     * @var \App\Service\Api\Google\GoogleApiService The API service instance.
     */
    private GoogleApiService $apiService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\Google\GoogleApiService|null $googleService
     */
    public function __construct(?GoogleApiService $googleService = null)
    {
        $this->apiService = $googleService ?? new GoogleApiService();
    }

    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    protected static function getJobType(): string
    {
        return 'article translation';
    }

    /**
     * Executes the article translation process based on the received message.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the article ID and title.
     * @return string|null The processing result.
     */
    public function execute(Message $message): ?string
    {
        if (!$this->validateArguments($message, ['id', 'title'])) {
            return Processor::REJECT;
        }

        $id = $message->getArgument('id');
        $title = $message->getArgument('title');
        $attempt = $message->getArgument('_attempt', 0);

        // Check if translations are enabled
        if (empty(array_filter(SettingsManager::read('Translations', [])))) {
            $this->log(
                sprintf('No languages enabled for translation: %s : %s', $id, $title),
                'warning',
                ['group_name' => static::class],
            );

            return Processor::REJECT;
        }

        $articlesTable = $this->getTable('Articles');
        $article = $articlesTable->get($id);

        // If there are empty SEO fields, requeue to wait for them to be populated
        if (!empty($articlesTable->emptySeoFields($article))) {
            return $this->handleEmptySeoFields($id, $title, $attempt);
        }

        return $this->executeWithErrorHandling($id, function () use ($article, $articlesTable) {
            $result = $this->apiService->translateArticle(
                (string)$article->title,
                (string)$article->lede,
                (string)$article->body,
                (string)$article->summary,
                (string)$article->meta_title,
                (string)$article->meta_description,
                (string)$article->meta_keywords,
                (string)$article->facebook_description,
                (string)$article->linkedin_description,
                (string)$article->instagram_description,
                (string)$article->twitter_description,
            );

            if ($result) {
                foreach ($result as $locale => $translation) {
                    $article->translation($locale)->title = $translation['title'];
                    $article->translation($locale)->lede = $translation['lede'];
                    $article->translation($locale)->body = $translation['body'];
                    $article->translation($locale)->summary = $translation['summary'];
                    $article->translation($locale)->meta_title = $translation['meta_title'];
                    $article->translation($locale)->meta_description = $translation['meta_description'];
                    $article->translation($locale)->meta_keywords = $translation['meta_keywords'];
                    $article->translation($locale)->facebook_description = $translation['facebook_description'];
                    $article->translation($locale)->linkedin_description = $translation['linkedin_description'];
                    $article->translation($locale)->instagram_description = $translation['instagram_description'];
                    $article->translation($locale)->twitter_description = $translation['twitter_description'];

                    $articlesTable->save($article, ['noMessage' => true]);
                }

                return true;
            }

            return false;
        }, $title);
    }

    /**
     * Handle the case where SEO fields are empty by requeuing the job
     *
     * @param string $id The article ID
     * @param string $title The article title
     * @param int $attempt The current attempt number
     * @return string|null
     */
    private function handleEmptySeoFields(string $id, string $title, int $attempt): ?string
    {
        if ($attempt >= 5) {
            $this->logJobError($id, sprintf('Article still has empty SEO fields after %d attempts', $attempt), $title);

            return Processor::REJECT;
        }

        $data = [
            'id' => $id,
            'title' => $title,
            '_attempt' => $attempt + 1,
        ];

        QueueManager::push(
            static::class,
            $data,
            [
                'config' => 'default',
                'delay' => 10 * ($attempt + 1),
            ],
        );

        $this->log(
            sprintf(
                'Article has empty SEO fields, re-queuing with %d second delay: %s : %s',
                10 * ($attempt + 1),
                $id,
                $title,
            ),
            'info',
            ['group_name' => static::class],
        );

        return Processor::ACK;
    }
}
