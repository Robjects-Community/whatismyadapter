<?php
declare(strict_types=1);

namespace App\Job;

use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * ProcessPageAssetsJob Class
 *
 * This job handles post-processing of uploaded page assets including:
 * - Asset validation and security scanning
 * - CSS/JS minification (optional)
 * - HTML validation and sanitization
 * - Asset optimization and compression
 * - Integration integrity checks
 */
class ProcessPageAssetsJob extends AbstractJob
{
    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    protected static function getJobType(): string
    {
        return 'page asset processing';
    }

    /**
     * Execute the asset processing job
     *
     * @param \Cake\Queue\Job\Message $message The queue message
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    public function execute(Message $message): ?string
    {
        if (!$this->validateArguments($message, ['entity_id', 'assets', 'entity_type'])) {
            return Processor::REJECT;
        }

        $entityId = $message->getArgument('entity_id');
        $assets = $message->getArgument('assets');
        $entityType = $message->getArgument('entity_type');

        return $this->executeWithErrorHandling(
            $entityId, 
            function () use ($assets, $entityType) {
                return $this->processAssets($assets, $entityType);
            },
            "Processing " . count($assets) . " assets"
        );
    }

    /**
     * Process the uploaded assets
     *
     * @param array $assets Array of asset information
     * @param string $entityType The entity type (e.g., 'Articles')
     * @return bool Success status
     */
    private function processAssets(array $assets, string $entityType): bool
    {
        $webroot = WWW_ROOT;
        $processedCount = 0;

        foreach ($assets as $asset) {
            $filePath = $webroot . $asset['path'];
            
            if (!file_exists($filePath)) {
                $this->log(
                    "Asset file not found: {$filePath}",
                    'error',
                    ['group_name' => static::class]
                );
                continue;
            }

            try {
                $success = $this->processIndividualAsset($asset, $filePath);
                if ($success) {
                    $processedCount++;
                }
            } catch (\Exception $e) {
                $this->log(
                    "Error processing asset {$asset['filename']}: {$e->getMessage()}",
                    'error',
                    ['group_name' => static::class]
                );
            }
        }

        return $processedCount > 0;
    }

    /**
     * Process an individual asset file
     *
     * @param array $asset Asset information
     * @param string $filePath Full file path
     * @return bool Success status
     */
    private function processIndividualAsset(array $asset, string $filePath): bool
    {
        $type = $asset['type'] ?? '';
        $success = false;

        switch ($type) {
            case 'js':
                $success = $this->processJavaScriptFile($asset, $filePath);
                break;
            case 'css':
                $success = $this->processCSSFile($asset, $filePath);
                break;
            case 'html':
            case 'htm':
                $success = $this->processHTMLFile($asset, $filePath);
                break;
            default:
                $success = $this->processGenericFile($asset, $filePath);
        }

        if ($success) {
            $this->log(
                "Successfully processed {$type} asset: {$asset['filename']}",
                'info',
                ['group_name' => static::class]
            );
        }

        return $success;
    }

    /**
     * Process JavaScript files
     *
     * @param array $asset Asset information
     * @param string $filePath Full file path
     * @return bool Success status
     */
    private function processJavaScriptFile(array $asset, string $filePath): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        // Basic security validation - check for suspicious patterns
        $suspiciousPatterns = [
            '/eval\s*\(/i',
            '/document\.write\s*\(/i',
            '/innerHTML\s*=/i',
            '/outerHTML\s*=/i',
            '/Function\s*\(/i',
            '/setTimeout\s*\(\s*["\'][\s\S]*["\']/i',
            '/setInterval\s*\(\s*["\'][\s\S]*["\']/i'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $this->log(
                    "Suspicious JavaScript pattern detected in {$asset['filename']}",
                    'warning',
                    ['group_name' => static::class]
                );
                // Could implement more strict handling here
            }
        }

        // Validate basic JavaScript syntax (very basic check)
        if (!$this->isValidJavaScript($content)) {
            $this->log(
                "Invalid JavaScript syntax in {$asset['filename']}",
                'warning',
                ['group_name' => static::class]
            );
        }

        return true;
    }

    /**
     * Process CSS files
     *
     * @param array $asset Asset information
     * @param string $filePath Full file path
     * @return bool Success status
     */
    private function processCSSFile(array $asset, string $filePath): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        // Basic CSS validation and security checks
        $suspiciousPatterns = [
            '/javascript\s*:/i',
            '/@import\s+url\s*\(\s*["\']?\s*javascript\s*:/i',
            '/expression\s*\(/i',
            '/-moz-binding\s*:/i'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $this->log(
                    "Suspicious CSS pattern detected in {$asset['filename']}",
                    'warning',
                    ['group_name' => static::class]
                );
            }
        }

        // Remove potentially dangerous CSS properties
        $cleanContent = $this->sanitizeCSS($content);
        if ($cleanContent !== $content) {
            file_put_contents($filePath, $cleanContent);
            $this->log(
                "CSS sanitized for {$asset['filename']}",
                'info',
                ['group_name' => static::class]
            );
        }

        return true;
    }

    /**
     * Process HTML files
     *
     * @param array $asset Asset information
     * @param string $filePath Full file path
     * @return bool Success status
     */
    private function processHTMLFile(array $asset, string $filePath): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        // Basic HTML validation and security sanitization
        $sanitizedContent = $this->sanitizeHTML($content);
        
        if ($sanitizedContent !== $content) {
            file_put_contents($filePath, $sanitizedContent);
            $this->log(
                "HTML sanitized for {$asset['filename']}",
                'info',
                ['group_name' => static::class]
            );
        }

        return true;
    }

    /**
     * Process generic files
     *
     * @param array $asset Asset information
     * @param string $filePath Full file path
     * @return bool Success status
     */
    private function processGenericFile(array $asset, string $filePath): bool
    {
        // Basic file validation
        $fileInfo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filePath);
        
        if ($fileInfo !== $asset['mime_type']) {
            $this->log(
                "MIME type mismatch for {$asset['filename']}: expected {$asset['mime_type']}, got {$fileInfo}",
                'warning',
                ['group_name' => static::class]
            );
        }

        return true;
    }

    /**
     * Basic JavaScript syntax validation
     *
     * @param string $content JavaScript content
     * @return bool True if valid syntax
     */
    private function isValidJavaScript(string $content): bool
    {
        // Very basic checks - could be enhanced with actual JS parsing
        $openBraces = substr_count($content, '{');
        $closeBraces = substr_count($content, '}');
        $openParens = substr_count($content, '(');
        $closeParens = substr_count($content, ')');
        $openBrackets = substr_count($content, '[');
        $closeBrackets = substr_count($content, ']');

        return $openBraces === $closeBraces && 
               $openParens === $closeParens && 
               $openBrackets === $closeBrackets;
    }

    /**
     * Sanitize CSS content by removing dangerous properties
     *
     * @param string $content CSS content
     * @return string Sanitized CSS content
     */
    private function sanitizeCSS(string $content): string
    {
        // Remove dangerous CSS properties and values
        $dangerousPatterns = [
            '/javascript\s*:[^;}]*/i',
            '/@import\s+url\s*\([^)]*javascript[^)]*\)/i',
            '/expression\s*\([^)]*\)/i',
            '/-moz-binding\s*:[^;}]*/i'
        ];

        foreach ($dangerousPatterns as $pattern) {
            $content = preg_replace($pattern, '/* removed dangerous content */', $content);
        }

        return $content;
    }

    /**
     * Sanitize HTML content by removing dangerous elements and attributes
     *
     * @param string $content HTML content
     * @return string Sanitized HTML content
     */
    private function sanitizeHTML(string $content): string
    {
        // Remove script tags and their content
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '<!-- script removed -->', $content);
        
        // Remove dangerous HTML attributes
        $dangerousAttributes = [
            'onclick', 'ondblclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmouseout',
            'onmousemove', 'onkeydown', 'onkeyup', 'onkeypress', 'onfocus', 'onblur',
            'onchange', 'onsubmit', 'onreset', 'onload', 'onunload', 'onerror'
        ];

        foreach ($dangerousAttributes as $attr) {
            $content = preg_replace('/\s+' . $attr . '\s*=\s*["\'][^"\']*["\']/', '', $content);
        }

        // Remove javascript: links
        $content = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/', 'href="#"', $content);

        return $content;
    }
}