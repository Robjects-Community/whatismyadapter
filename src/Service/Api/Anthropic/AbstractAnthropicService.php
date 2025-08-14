<?php
declare(strict_types=1);

namespace App\Service\Api\Anthropic;

use Cake\Log\LogTrait;

abstract class AbstractAnthropicService
{
    use LogTrait;

    protected function validateAndFallback(array $result, array $expectedKeys): array
    {
        foreach ($expectedKeys as $key => $constraints) {
            if (!isset($result[$key]) || !$this->validateField($key, $result[$key], $constraints)) {
                $this->log("AI response validation failed for {$key}, using fallback", 'warning');
                $result[$key] = $this->getSmartFallback($key, $result);
            }
        }

        return $result;
    }

    private function validateField(string $key, $value, array $constraints): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $validators = [
            'meta_title' => fn($v) => strlen($v) <= 255,
            'meta_description' => fn($v) => strlen($v) <= 300,
            'twitter_description' => fn($v) => strlen($v) <= 280,
            'meta_keywords' => fn($v) => str_word_count($v) <= 20,
            'alt_text' => fn($v) => strlen($v) <= 200,
        ];

        return isset($validators[$key]) ? $validators[$key]($value) : true;
    }

    private function getSmartFallback(string $key, array $context): string
    {
        $fallbacks = [
            'meta_title' => $context['title'] ?? 'Untitled Content',
            'meta_description' => substr(strip_tags($context['content'] ?? ''), 0, 160),
            'alt_text' => 'Image content',
            'meta_keywords' => '',
            'twitter_description' => substr(strip_tags($context['content'] ?? ''), 0, 280),
            'facebook_description' => substr(strip_tags($context['content'] ?? ''), 0, 300),
            'linkedin_description' => substr(strip_tags($context['content'] ?? ''), 0, 700),
            'instagram_description' => substr(strip_tags($context['content'] ?? ''), 0, 1500),
        ];

        return $fallbacks[$key] ?? '';
    }
}
