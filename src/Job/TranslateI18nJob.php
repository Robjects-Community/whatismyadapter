<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Api\Google\GoogleApiService;
use App\Utility\SettingsManager;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * TranslateI18nJob
 *
 * This job processes messages to update internationalizations using either the Anthropic or Google API.
 * It retrieves the internationalizations based on the provided IDs and updates them in batches.
 */
class TranslateI18nJob extends AbstractJob
{
    /**
     * @var \App\Service\Api\Anthropic\AnthropicApiService|\App\Service\Api\Google\GoogleApiService The API service instance.
     */
    private AnthropicApiService|GoogleApiService $apiService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\Anthropic\AnthropicApiService|null $anthropicService
     * @param \App\Service\Api\Google\GoogleApiService|null $googleService
     */
    public function __construct(?AnthropicApiService $anthropicService = null, ?GoogleApiService $googleService = null)
    {
        $apiProvider = SettingsManager::read('i18n.provider', 'google');

        if ($apiProvider === 'google') {
            $this->apiService = $googleService ?? new GoogleApiService();
        } else {
            $this->apiService = $anthropicService ?? new AnthropicApiService();
        }
    }

    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    protected static function getJobType(): string
    {
        return 'i18n translation';
    }

    /**
     * Executes the job to update internationalizations.
     *
     * This method processes the message, retrieves the internationalizations based on the provided IDs,
     * and updates them in batches using the selected API service.
     *
     * @param \Cake\Queue\Job\Message $message The message containing internationalization IDs.
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure.
     */
    public function execute(Message $message): ?string
    {
        if (!$this->validateArguments($message, ['internationalisations', 'locale'])) {
            return Processor::REJECT;
        }

        $internationalisations = $message->getArgument('internationalisations');
        $locale = $message->getArgument('locale');

        $i18nTable = $this->getTable('internationalisations');
        $internationalizations = $i18nTable->find()
            ->select(['id', 'locale', 'message_id'])
            ->where(['id IN' => $internationalisations])
            ->all();

        if ($internationalizations->isEmpty()) {
            $this->logJobError($locale, 'No internationalizations found for the provided IDs');

            return Processor::REJECT;
        }

        $messageStrings = $internationalizations->extract('message_id')->toArray();

        return $this->executeWithErrorHandling($locale, function () use ($messageStrings, $locale, $i18nTable) {
            $translatedMessages = $this->apiService->translateStrings(
                $messageStrings,
                'en_GB',
                $locale,
            );

            foreach ($translatedMessages['translations'] as $index => $translation) {
                $originalMessage = $messageStrings[$index];

                $existingTranslation = $i18nTable->find()
                    ->where([
                        'message_id' => $originalMessage,
                        'locale' => $locale,
                    ])
                    ->first();

                if ($existingTranslation) {
                    $existingTranslation->message_str = $translation['translated'];
                    $i18nTable->save($existingTranslation);
                }
            }

            return true;
        });
    }
}
