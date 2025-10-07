<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class AipromptsFixture extends TestFixture
{
    public string $table = 'aiprompts';

    public function init(): void
    {
        $this->records = [
            [
                'id' => '40000000-0000-0000-0000-000000000000',
                'task_type' => 'summarize',
                'system_prompt' => 'Summarize the following text clearly and concisely.',
                'model' => 'gpt-4o-mini',
                'max_tokens' => 256,
                'temperature' => 0.20,
                'status' => 'stable',
                'last_used' => '2025-08-12 09:00:00',
                'usage_count' => 10,
                'success_rate' => 95.50,
                'description' => 'General summarization prompt',
                'preview_sample' => 'Preview text',
                'expected_output' => 'A concise summary',
                'is_active' => 1,
                'category' => 'content',
                'version' => 'v1',
                'created' => '2025-08-10 12:00:00',
                'modified' => '2025-08-10 12:00:00',
            ],
            [
                'id' => '40000000-0000-0000-0000-000000000001',
                'task_type' => 'classify',
                'system_prompt' => 'Classify the sentiment of the text as positive, neutral, or negative.',
                'model' => 'gpt-4o-mini',
                'max_tokens' => 64,
                'temperature' => 0.00,
                'status' => 'beta',
                'last_used' => null,
                'usage_count' => 0,
                'success_rate' => null,
                'description' => 'Sentiment classification',
                'preview_sample' => 'Sample text',
                'expected_output' => 'One of: positive | neutral | negative',
                'is_active' => 1,
                'category' => 'nlp',
                'version' => 'v1',
                'created' => '2025-08-11 12:00:00',
                'modified' => '2025-08-11 12:00:00',
            ],
        ];
        parent::init();
    }
}
