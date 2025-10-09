<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * TestRateLimitCommand class
 *
 * This command is used to test rate limiting by making multiple requests to a specified URL.
 */
class TestRateLimitCommand extends Command
{
    /**
     * Build the option parser for the command.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser to be defined.
     * @return \Cake\Console\ConsoleOptionParser The option parser instance.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->addOption('url', [
                'short' => 'u',
                'help' => 'URL to test (default: /en/users/login)',
                'default' => '/en/users/login',
            ])
            ->addOption('attempts', [
                'short' => 'a',
                'help' => 'Number of attempts (default: 10)',
                'default' => '10',
            ]);

        return $parser;
    }

    /**
     * Execute the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io.
     * @return int|null The exit code or null for success.
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $url = $args->getOption('url');
        $attempts = (int)$args->getOption('attempts');

        $baseUrl = $this->getBaseUrl();
        $fullUrl = $baseUrl . $url;

        $io->out(sprintf('Testing rate limit on URL: %s', $fullUrl));
        $io->out(sprintf('Number of attempts: %d', $attempts));
        $io->hr();

        for ($i = 0; $i < $attempts; $i++) {
            $ch = curl_init($fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);

            // Set headers to simulate a browser request
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 ' .
                    '(KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'X-Forwarded-For: ' . $this->generateRandomIp(),
            ]);

            $response = curl_exec($ch);

            if ($response === false) {
                $error = curl_error($ch);
                $errno = curl_errno($ch);
                $io->error(sprintf('Attempt %d failed. Error (%d): %s', $i + 1, $errno, $error));
            } else {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $io->out(sprintf('Attempt %d: HTTP Code: %d', $i + 1, $httpCode));
                $io->out(sprintf('Response: %s', substr($response, 0, 100) . '...'));
            }

            curl_close($ch);
            $io->hr();

            usleep(100000); // 0.1 second delay
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Get the base URL for the requests.
     *
     * @return string The base URL.
     */
    private function getBaseUrl(): string
    {
        return 'http://willowcms:80';
    }

    /**
     * Generate a random IP address.
     *
     * @return string A randomly generated IP address.
     */
    private function generateRandomIp(): string
    {
        return mt_rand(1, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255);
    }
}
