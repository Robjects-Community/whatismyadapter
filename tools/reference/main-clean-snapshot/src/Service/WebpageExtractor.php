<?php
declare(strict_types=1);

namespace App\Service;

use DOMDocument;
use DOMXPath;
use Exception;

/**
 * WebpageExtractor Service
 * 
 * Extracts content, metadata, and other information from web pages
 */
class WebpageExtractor
{
    private const USER_AGENT = 'Mozilla/5.0 (compatible; WillowCMS Content Extractor)';
    private const TIMEOUT = 30;
    private const MAX_REDIRECTS = 5;

    /**
     * Extract content from a webpage
     *
     * @param string $url The URL to extract content from
     * @return array Array containing success status and extracted data
     */
    public function extractContent(string $url): array
    {
        try {
            // Validate URL
            if (!$this->isValidUrl($url)) {
                return [
                    'success' => false,
                    'message' => 'Invalid URL provided',
                    'data' => []
                ];
            }

            // Fetch the webpage content
            $html = $this->fetchWebpage($url);
            if (!$html) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch webpage content',
                    'data' => []
                ];
            }

            // Parse the HTML
            $dom = $this->parseHtml($html);
            if (!$dom) {
                return [
                    'success' => false,
                    'message' => 'Failed to parse HTML content',
                    'data' => []
                ];
            }

            // Extract content
            $extractedData = $this->extractFromDom($dom);

            return [
                'success' => true,
                'message' => 'Content extracted successfully',
                'data' => $extractedData
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error extracting content: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Validate if the provided string is a valid URL
     *
     * @param string $url
     * @return bool
     */
    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false &&
               (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0);
    }

    /**
     * Fetch webpage content using cURL
     *
     * @param string $url
     * @return string|false
     */
    private function fetchWebpage(string $url)
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => self::MAX_REDIRECTS,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_ENCODING => '', // Enable compression
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Cache-Control: no-cache'
            ]
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);

        // Check if request was successful
        if ($html === false || $httpCode >= 400) {
            return false;
        }

        return $html;
    }

    /**
     * Parse HTML content into a DOMDocument
     *
     * @param string $html
     * @return DOMDocument|false
     */
    private function parseHtml(string $html)
    {
        $dom = new DOMDocument();
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        
        // Load HTML with UTF-8 encoding
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        // Clear libxml errors
        libxml_clear_errors();

        return $dom;
    }

    /**
     * Extract content and metadata from DOMDocument
     *
     * @param DOMDocument $dom
     * @return array
     */
    private function extractFromDom(DOMDocument $dom): array
    {
        $xpath = new DOMXPath($dom);
        
        return [
            'title' => $this->extractTitle($xpath),
            'content' => $this->extractMainContent($xpath),
            'meta_title' => $this->extractMetaTitle($xpath),
            'meta_description' => $this->extractMetaDescription($xpath),
            'meta_keywords' => $this->extractMetaKeywords($xpath)
        ];
    }

    /**
     * Extract page title
     *
     * @param DOMXPath $xpath
     * @return string
     */
    private function extractTitle(DOMXPath $xpath): string
    {
        // Try different methods to get the title
        $titleSources = [
            '//title',
            '//h1',
            '//meta[@property="og:title"]/@content',
            '//meta[@name="twitter:title"]/@content'
        ];

        foreach ($titleSources as $source) {
            $nodes = $xpath->query($source);
            if ($nodes->length > 0) {
                $title = trim($nodes->item(0)->textContent ?? $nodes->item(0)->nodeValue ?? '');
                if (!empty($title)) {
                    return $this->cleanText($title);
                }
            }
        }

        return '';
    }

    /**
     * Extract main content from the page
     *
     * @param DOMXPath $xpath
     * @return string
     */
    private function extractMainContent(DOMXPath $xpath): string
    {
        // Try to find main content using common selectors
        $contentSelectors = [
            '//main',
            '//article',
            '//*[@id="content"]',
            '//*[@id="main"]',
            '//*[@class="content"]',
            '//*[@class="main-content"]',
            '//*[@class="post-content"]',
            '//*[@class="entry-content"]',
            '//div[contains(@class, "content")]'
        ];

        foreach ($contentSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $content = $this->extractTextContent($nodes->item(0));
                if (strlen($content) > 100) { // Only use if substantial content
                    return $content;
                }
            }
        }

        // Fallback: extract from body, excluding common non-content elements
        $bodyNodes = $xpath->query('//body');
        if ($bodyNodes->length > 0) {
            return $this->extractTextContent($bodyNodes->item(0), true);
        }

        return '';
    }

    /**
     * Extract meta title
     *
     * @param DOMXPath $xpath
     * @return string
     */
    private function extractMetaTitle(DOMXPath $xpath): string
    {
        $sources = [
            '//meta[@property="og:title"]/@content',
            '//meta[@name="twitter:title"]/@content',
            '//title'
        ];

        foreach ($sources as $source) {
            $nodes = $xpath->query($source);
            if ($nodes->length > 0) {
                $title = trim($nodes->item(0)->textContent ?? $nodes->item(0)->nodeValue ?? '');
                if (!empty($title)) {
                    return $this->cleanText($title);
                }
            }
        }

        return '';
    }

    /**
     * Extract meta description
     *
     * @param DOMXPath $xpath
     * @return string
     */
    private function extractMetaDescription(DOMXPath $xpath): string
    {
        $sources = [
            '//meta[@name="description"]/@content',
            '//meta[@property="og:description"]/@content',
            '//meta[@name="twitter:description"]/@content'
        ];

        foreach ($sources as $source) {
            $nodes = $xpath->query($source);
            if ($nodes->length > 0) {
                $description = trim($nodes->item(0)->nodeValue ?? '');
                if (!empty($description)) {
                    return $this->cleanText($description);
                }
            }
        }

        return '';
    }

    /**
     * Extract meta keywords
     *
     * @param DOMXPath $xpath
     * @return string
     */
    private function extractMetaKeywords(DOMXPath $xpath): string
    {
        $keywordSources = [
            '//meta[@name="keywords"]/@content',
            '//meta[@property="article:tag"]/@content'
        ];

        $allKeywords = [];

        foreach ($keywordSources as $source) {
            $nodes = $xpath->query($source);
            for ($i = 0; $i < $nodes->length; $i++) {
                $keywords = trim($nodes->item($i)->nodeValue ?? '');
                if (!empty($keywords)) {
                    $keywordArray = array_map('trim', explode(',', $keywords));
                    $allKeywords = array_merge($allKeywords, $keywordArray);
                }
            }
        }

        // Remove duplicates and empty values
        $allKeywords = array_filter(array_unique($allKeywords));
        
        return implode(', ', array_slice($allKeywords, 0, 10)); // Limit to 10 keywords
    }

    /**
     * Extract text content from a DOM node
     *
     * @param \DOMNode $node
     * @param bool $excludeCommonElements Whether to exclude common non-content elements
     * @return string
     */
    private function extractTextContent(\DOMNode $node, bool $excludeCommonElements = false): string
    {
        if ($excludeCommonElements) {
            // Remove common non-content elements
            $elementsToRemove = ['script', 'style', 'nav', 'header', 'footer', 'aside', 'form'];
            
            $xpath = new DOMXPath($node->ownerDocument);
            foreach ($elementsToRemove as $tagName) {
                $elements = $xpath->query('.//' . $tagName, $node);
                for ($i = $elements->length - 1; $i >= 0; $i--) {
                    $element = $elements->item($i);
                    $element->parentNode->removeChild($element);
                }
            }
        }

        $content = $node->textContent ?? '';
        return $this->cleanText($content);
    }

    /**
     * Clean and normalize text content
     *
     * @param string $text
     * @return string
     */
    private function cleanText(string $text): string
    {
        // Remove extra whitespace and normalize line breaks
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $text;
    }
}