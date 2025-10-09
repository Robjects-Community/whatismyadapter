<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QuizSubmissionsFixture
 */
class QuizSubmissionsFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'quiz_submissions';

    /**
     * Fields
     *
     * @var array<string, mixed>
     */
    public array $fields = [
        'id' => ['type' => 'uuid', 'length' => null, 'null' => false, 'default' => null],
        'user_id' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null],
        'session_id' => ['type' => 'string', 'length' => 64, 'null' => false, 'default' => null],
        'quiz_type' => ['type' => 'string', 'length' => 20, 'null' => false, 'default' => 'comprehensive'],
        'answers' => ['type' => 'json', 'null' => false, 'default' => null],
        'matched_product_ids' => ['type' => 'json', 'null' => true, 'default' => null],
        'confidence_scores' => ['type' => 'json', 'null' => true, 'default' => null],
        'result_summary' => ['type' => 'text', 'null' => true, 'default' => null],
        'analytics' => ['type' => 'json', 'null' => true, 'default' => null],
        'created' => ['type' => 'datetime', 'null' => false, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => false, 'default' => null],
        'created_by' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null],
        'modified_by' => ['type' => 'uuid', 'length' => null, 'null' => true, 'default' => null],
        '_indexes' => [
            'user_id' => ['type' => 'index', 'columns' => ['user_id']],
            'session_id' => ['type' => 'index', 'columns' => ['session_id']],
            'quiz_type' => ['type' => 'index', 'columns' => ['quiz_type']],
            'created' => ['type' => 'index', 'columns' => ['created']],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    /**
     * Records
     *
     * @var array<array<string, mixed>>
     */
    public array $records = [];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'quiz-sub-0001-0000-0000-000000000001',
                'user_id' => 'user-0001-0000-0000-000000000001', // admin user
                'session_id' => 'session-123-abc',
                'quiz_type' => 'comprehensive',
                'answers' => json_encode([
                    'question_1' => 'answer_a',
                    'question_2' => 'answer_b',
                ]),
                'matched_product_ids' => json_encode(['prod-0001-0000-0000-000000000001']),
                'confidence_scores' => json_encode(['prod-0001-0000-0000-000000000001' => 0.85]),
                'result_summary' => 'Based on your answers, we recommend Product 1.',
                'analytics' => json_encode([
                    'time_taken' => 120,
                    'completed' => true,
                ]),
                'created' => '2024-01-01 10:00:00',
                'modified' => '2024-01-01 10:00:00',
                'created_by' => 'user-0001-0000-0000-000000000001',
                'modified_by' => 'user-0001-0000-0000-000000000001',
            ],
            [
                'id' => 'quiz-sub-0002-0000-0000-000000000002',
                'user_id' => 'user-0002-0000-0000-000000000002', // regular user
                'session_id' => 'session-456-def',
                'quiz_type' => 'quick',
                'answers' => json_encode([
                    'question_1' => 'answer_c',
                ]),
                'matched_product_ids' => json_encode(['prod-0002-0000-0000-000000000002']),
                'confidence_scores' => json_encode(['prod-0002-0000-0000-000000000002' => 0.72]),
                'result_summary' => 'Based on your answers, we recommend Product 2.',
                'analytics' => json_encode([
                    'time_taken' => 60,
                    'completed' => true,
                ]),
                'created' => '2024-01-02 11:00:00',
                'modified' => '2024-01-02 11:00:00',
                'created_by' => 'user-0002-0000-0000-000000000002',
                'modified_by' => 'user-0002-0000-0000-000000000002',
            ],
            [
                'id' => 'quiz-sub-0003-0000-0000-000000000003',
                'user_id' => null, // Anonymous submission
                'session_id' => 'session-789-ghi',
                'quiz_type' => 'comprehensive',
                'answers' => json_encode([
                    'question_1' => 'answer_a',
                    'question_2' => 'answer_d',
                    'question_3' => 'answer_b',
                ]),
                'matched_product_ids' => json_encode(['prod-0001-0000-0000-000000000001', 'prod-0002-0000-0000-000000000002']),
                'confidence_scores' => json_encode([
                    'prod-0001-0000-0000-000000000001' => 0.65,
                    'prod-0002-0000-0000-000000000002' => 0.78,
                ]),
                'result_summary' => 'Based on your answers, we recommend Product 1 and Product 2.',
                'analytics' => json_encode([
                    'time_taken' => 180,
                    'completed' => true,
                ]),
                'created' => '2024-01-03 12:00:00',
                'modified' => '2024-01-03 12:00:00',
                'created_by' => null,
                'modified_by' => null,
            ],
        ];

        parent::init();
    }
}
