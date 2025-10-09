<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

class AddQuizSettings extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Add Quiz category settings for Akinator-style quiz configuration
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('settings')
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'Quiz',
                'key_name' => 'akinatorEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable the Akinator-style interactive quiz feature that guides users through questions to find their perfect product match. When enabled, users can access the quiz at /quiz/akinator.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 2,
                'category' => 'Quiz',
                'key_name' => 'maxQuestions',
                'value' => '10',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Maximum number of questions to ask in the Akinator-style quiz. The quiz will automatically terminate after this many questions, even if the optimal match hasn\'t been found. Recommended: 8-15 questions.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 3,
                'category' => 'Quiz',
                'key_name' => 'confidenceThreshold',
                'value' => '85',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Confidence threshold (as a percentage) at which the quiz will automatically terminate and show results. If the system is 85% confident it has found the right product matches, it will end the quiz early. Range: 70-95.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 4,
                'category' => 'Quiz',
                'key_name' => 'minProductsThreshold',
                'value' => '3',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Minimum number of product matches before the quiz can terminate. Even with high confidence, the quiz will continue until at least this many products are found that match the criteria.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 5,
                'category' => 'Quiz',
                'key_name' => 'aiQuestionsEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable AI-powered question generation. When enabled, the quiz will use artificial intelligence to generate smart, context-aware questions based on available products and user answers. Fallback questions will be used if AI is unavailable.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 6,
                'category' => 'Quiz',
                'key_name' => 'cacheEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable caching for quiz sessions and decision tree data. This improves performance by storing quiz state and frequently accessed data in cache. Disable only for debugging purposes.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 7,
                'category' => 'Quiz',
                'key_name' => 'sessionTtl',
                'value' => '3600',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Session timeout in seconds for quiz sessions. After this time, quiz sessions will expire and users will need to start over. Default is 1 hour (3600 seconds). Range: 300-7200.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 8,
                'category' => 'Quiz',
                'key_name' => 'analyticsEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable detailed analytics collection for quiz sessions. This tracks user behavior, popular answers, completion rates, and performance metrics to help improve the quiz experience.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 9,
                'category' => 'Quiz',
                'key_name' => 'resultLimit',
                'value' => '5',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Maximum number of product recommendations to show in quiz results. Users will see this many top-matched products ranked by confidence score. Recommended: 3-10 products.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 10,
                'category' => 'Quiz',
                'key_name' => 'fallbackQuestionsEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable fallback questions when AI question generation fails or is disabled. This ensures the quiz can always continue even without AI assistance by using a predefined set of effective questions.',
                'data' => null,
                'column_width' => 2,
            ])
            ->saveData();
    }
}