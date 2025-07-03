<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Google\GoogleApiService;
use App\Utility\SettingsManager;
use Cake\Queue\Job\Message;
use Cake\Queue\QueueManager;
use Interop\Queue\Processor;

/**
 * TranslateTagJob class
 *
 * This job is responsible for translating tag data using the Google API service.
 * It retrieves the tag data from the database, translates the specified fields,
 * and saves the translated data back to the database.
 */
class TranslateTagJob extends AbstractJob
{
    /**
     * @var \App\Service\Api\Google\GoogleApiService The API service instance.
     */
    private GoogleApiService $apiService;

    /**
     * Constructor to allow dependency injection for testing.
     *
     * @param \App\Service\Api\Google\GoogleApiService|null $googleService The Google API service instance.
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
        return 'tag translation';
    }

    /**
     * Execute the job.
     *
     * @param \Cake\Queue\Job\Message $message The job message.
     * @return string|null The result of the job execution.
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

        $tagsTable = $this->getTable('Tags');
        $tag = $tagsTable->get($id);

        // If there are empty SEO fields, requeue to wait for them to be populated
        if (!empty($tagsTable->emptySeoFields($tag))) {
            return $this->handleEmptySeoFields($id, $title, $attempt);
        }

        return $this->executeWithErrorHandling($id, function () use ($tag, $tagsTable) {
            $result = $this->apiService->translateTag(
                (string)$tag->title,
                (string)$tag->description,
                (string)$tag->meta_title,
                (string)$tag->meta_description,
                (string)$tag->meta_keywords,
                (string)$tag->facebook_description,
                (string)$tag->linkedin_description,
                (string)$tag->instagram_description,
                (string)$tag->twitter_description,
            );

            if ($result) {
                foreach ($result as $locale => $translation) {
                    $tag->translation($locale)->title = $translation['title'];
                    $tag->translation($locale)->description = $translation['description'];
                    $tag->translation($locale)->meta_title = $translation['meta_title'];
                    $tag->translation($locale)->meta_description = $translation['meta_description'];
                    $tag->translation($locale)->meta_keywords = $translation['meta_keywords'];
                    $tag->translation($locale)->facebook_description = $translation['facebook_description'];
                    $tag->translation($locale)->linkedin_description = $translation['linkedin_description'];
                    $tag->translation($locale)->instagram_description = $translation['instagram_description'];
                    $tag->translation($locale)->twitter_description = $translation['twitter_description'];

                    $tagsTable->save($tag, ['noMessage' => true]);
                }

                return true;
            }

            return false;
        }, $title);
    }

    /**
     * Handle the case where SEO fields are empty by requeuing the job
     *
     * @param string $id The tag ID
     * @param string $title The tag title
     * @param int $attempt The current attempt number
     * @return string|null
     */
    private function handleEmptySeoFields(string $id, string $title, int $attempt): ?string
    {
        if ($attempt >= 5) {
            $this->logJobError($id, sprintf('Tag still has empty SEO fields after %d attempts', $attempt), $title);

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
                'Tag has empty SEO fields, re-queuing with %d second delay: %s : %s',
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
