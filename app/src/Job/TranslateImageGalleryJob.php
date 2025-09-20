<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Google\GoogleApiService;
use App\Utility\SettingsManager;
use Cake\Queue\Job\Message;
use Cake\Queue\QueueManager;
use Interop\Queue\Processor;

class TranslateImageGalleryJob extends AbstractJob
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
        return 'image gallery translation';
    }

    /**
     * Executes the image gallery translation process based on the received message.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the gallery ID and name.
     * @return string|null The processing result.
     */
    public function execute(Message $message): ?string
    {
        if (!$this->validateArguments($message, ['id', 'name'])) {
            return Processor::REJECT;
        }

        $id = $message->getArgument('id');
        $name = $message->getArgument('name');
        $attempt = $message->getArgument('_attempt', 0);

        // Check if translations are enabled
        if (empty(array_filter(SettingsManager::read('Translations', [])))) {
            $this->log(
                sprintf('No languages enabled for translation: %s : %s', $id, $name),
                'warning',
                ['group_name' => static::class],
            );

            return Processor::REJECT;
        }

        $galleriesTable = $this->getTable('ImageGalleries');
        $gallery = $galleriesTable->get($id);

        // If there are empty SEO fields, requeue to wait for them to be populated
        if (!empty($galleriesTable->emptySeoFields($gallery))) {
            return $this->handleEmptySeoFields($id, $name, $attempt);
        }

        return $this->executeWithErrorHandling($id, function () use ($gallery, $galleriesTable) {
            $result = $this->apiService->translateImageGallery(
                (string)$gallery->name,
                (string)$gallery->description,
                (string)$gallery->meta_title,
                (string)$gallery->meta_description,
                (string)$gallery->meta_keywords,
                (string)$gallery->facebook_description,
                (string)$gallery->linkedin_description,
                (string)$gallery->instagram_description,
                (string)$gallery->twitter_description,
            );

            if ($result) {
                foreach ($result as $locale => $translation) {
                    $gallery->translation($locale)->name = $translation['name'];
                    $gallery->translation($locale)->description = $translation['description'];
                    $gallery->translation($locale)->meta_title = $translation['meta_title'];
                    $gallery->translation($locale)->meta_description = $translation['meta_description'];
                    $gallery->translation($locale)->meta_keywords = $translation['meta_keywords'];
                    $gallery->translation($locale)->facebook_description = $translation['facebook_description'];
                    $gallery->translation($locale)->linkedin_description = $translation['linkedin_description'];
                    $gallery->translation($locale)->instagram_description = $translation['instagram_description'];
                    $gallery->translation($locale)->twitter_description = $translation['twitter_description'];

                    $galleriesTable->save($gallery, ['noMessage' => true]);
                }

                return true;
            }

            return false;
        }, $name);
    }

    /**
     * Handle the case where SEO fields are empty by requeuing the job
     *
     * @param string $id The gallery ID
     * @param string $name The gallery name
     * @param int $attempt The current attempt number
     * @return string|null
     */
    private function handleEmptySeoFields(string $id, string $name, int $attempt): ?string
    {
        if ($attempt >= 5) {
            $this->logJobError($id, sprintf('Gallery still has empty SEO fields after %d attempts', $attempt), $name);

            return Processor::REJECT;
        }

        $data = [
            'id' => $id,
            'name' => $name,
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
                'Gallery has empty SEO fields, re-queuing with %d second delay: %s : %s',
                10 * ($attempt + 1),
                $id,
                $name,
            ),
            'info',
            ['group_name' => static::class],
        );

        return Processor::ACK;
    }
}
