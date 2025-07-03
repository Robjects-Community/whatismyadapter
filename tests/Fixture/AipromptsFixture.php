<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AipromptsFixture
 */
class AipromptsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'b196ad21-26c0-41db-886e-57c2d873c244',
                'task_type' => 'image_analysis',
                'system_prompt' => 'You are an image analysis robot. For each image, generate:

- **name**: A concise, descriptive string (max 255 characters) of the image\'s main subject.
- **alt_text**: A detailed description for visually impaired users (max 255 characters).
- **keywords**: Space-separated keywords capturing key elements/themes (max 30 words).

Respond in valid JSON with these data items. Use your best judgment for ambiguous images.',
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 1000,
                'temperature' => 0.2,
                'created' => 1728732013,
                'modified' => 1728732013,
            ],
            [
                'id' => '5f1a33b2-a011-444d-bb26-1ea290395ae1',
                'task_type' => 'article_summarization',
                'system_prompt' => 'You are an expert article summarizer. Given an article, provide:

- **summary**: A concise summary of the main points (max 500 characters).
- **key_points**: A bullet-point list of 3-5 key takeaways.
- **audience**: The target audience for this article.

Respond in valid JSON format with these elements.',
                'model' => 'gpt-4-1106-preview',
                'max_tokens' => 1500,
                'temperature' => 0.3,
                'created' => 1728732014,
                'modified' => 1728732014,
            ],
        ];
        parent::init();
    }
}
