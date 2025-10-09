<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Quiz\AiProductMatcherService;
use App\Service\Quiz\DecisionTreeService;

/**
 * MockAiServiceTrait - Provides mocks for AI services in API controller tests
 */
trait MockAiServiceTrait
{
    /**
     * Mock DecisionTreeService to avoid AI API calls
     *
     * @return object Mock service
     */
    protected function mockDecisionTreeService(): object
    {
        $mock = $this->getMockBuilder(DecisionTreeService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['start', 'next', 'getStoredState'])
            ->getMock();

        // Mock start() to return a basic session structure
        $mock->method('start')->willReturn([
            'session_id' => 'test-session-' . uniqid(),
            'questions' => [
                [
                    'id' => 'q1',
                    'text' => 'What type of device do you need an adapter for?',
                    'type' => 'single_choice',
                    'options' => ['laptop', 'phone', 'tablet'],
                ],
            ],
            'current_question_index' => 0,
            'total_questions' => 5,
            'confidence' => 0.0,
        ]);

        // Mock next() to return a follow-up question or result
        $mock->method('next')->willReturn([
            'session_id' => 'test-session-123',
            'completed' => false,
            'question' => [
                'id' => 'q2',
                'text' => 'What port type do you need?',
                'type' => 'single_choice',
                'options' => ['USB-C', 'USB-A', 'Lightning'],
            ],
            'confidence' => 0.5,
        ]);

        // Mock getStoredState() to return empty state
        $mock->method('getStoredState')->willReturn(null);

        return $mock;
    }

    /**
     * Mock AiProductMatcherService to avoid AI API calls
     *
     * @return object Mock service
     */
    protected function mockAiProductMatcherService(): object
    {
        $mock = $this->getMockBuilder(AiProductMatcherService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['match'])
            ->getMock();

        // Mock match() to return sample product recommendations
        $mock->method('match')->willReturn([
            'total_matches' => 2,
            'overall_confidence' => 0.85,
            'processing_time' => 0.123,
            'method' => 'rule_based',
            'products' => [
                [
                    'product' => (object)[
                        'id' => '11111111-1111-1111-1111-111111111111',
                        'title' => 'Test USB-C Adapter',
                        'manufacturer' => 'Test Brand',
                        'price' => 29.99,
                    ],
                    'confidence' => 0.90,
                    'explanation' => 'Perfect match for your needs',
                ],
                [
                    'product' => (object)[
                        'id' => '22222222-2222-2222-2222-222222222222',
                        'title' => 'Premium USB-C Hub',
                        'manufacturer' => 'Premium Brand',
                        'price' => 49.99,
                    ],
                    'confidence' => 0.80,
                    'explanation' => 'Good alternative option',
                ],
            ],
        ]);

        return $mock;
    }

    /**
     * Mock AnthropicApiService to avoid external API calls
     *
     * @return object Mock service
     */
    protected function mockAnthropicApiService(): object
    {
        $mock = $this->getMockBuilder(AnthropicApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateResponse'])
            ->getMock();

        // Mock generateResponse() to return sample AI text
        $mock->method('generateResponse')->willReturn(
            'This is a mock AI response for testing purposes.'
        );

        return $mock;
    }
}
