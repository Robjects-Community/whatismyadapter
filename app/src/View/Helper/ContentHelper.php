<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * Content Helper
 *
 * Handles content enhancement and formatting:
 * - Content alignment helpers
 * - Responsive image processing
 * - Markdown processing with custom syntax
 *
 * Note: Video and gallery placeholder processing is handled by VideoHelper and GalleryHelper
 */
class ContentHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array<string>
     */
    protected array $helpers = ['Html'];

    /**
     * Enhance content formatting without processing placeholders
     * Note: Video and gallery placeholders should be processed by their respective helpers first
     *
     * @param string $content Raw content (placeholders should already be processed)
     * @param array $options Processing options
     * @return string Enhanced content
     */
    public function enhanceContent(string $content, array $options = []): string
    {
        // Content enhancement pipeline (no placeholder processing)
        $content = $this->enhanceContentAlignment($content, $options);

        // Only process responsive images if explicitly requested
        if ($options['processResponsiveImages'] ?? false) {
            $content = $this->processResponsiveImages($content, $options);
        }

        return $content;
    }

    /**
     * Enhance content alignment by adding CSS classes and data attributes
     *
     * @param string $content HTML content
     * @param array $options Processing options
     * @return string Enhanced content
     */
    public function enhanceContentAlignment(string $content, array $options = []): string
    {
        // Add alignment helper classes to paragraphs with inline styles
        $alignmentResult = preg_replace(
            '/<p([^>]*style[^>]*text-align:\s*(center|left|right|justify)[^>]*)>/i',
            '<p$1 class="content-align-$2">',
            $content,
        );
        if ($alignmentResult !== null) {
            $content = $alignmentResult;
        }

        // Add content-image class to all images for consistent styling
        $imageClassResult = preg_replace(
            '/<img([^>]*)>/i',
            '<img$1 class="content-image">',
            $content,
        );
        if ($imageClassResult !== null) {
            $content = $imageClassResult;
        }

        // Preserve line breaks within aligned content by converting double BR to paragraph breaks
        $lineBreakResult = preg_replace(
            '/<br\s*\/?>\s*<br\s*\/?>/i',
            '</p><p>',
            $content,
        );
        if ($lineBreakResult !== null) {
            $content = $lineBreakResult;
        }

        // Ensure empty paragraphs with alignment are preserved for spacing
        $emptyParagraphResult = preg_replace(
            '/<p([^>]*style[^>]*text-align[^>]*)>\s*<\/p>/i',
            '<p$1>&nbsp;</p>',
            $content,
        );
        if ($emptyParagraphResult !== null) {
            $content = $emptyParagraphResult;
        }

        return $content;
    }

    /**
     * Process images to make them responsive and add proper styling
     *
     * @param string $content HTML content with images
     * @param array $options Processing options
     * @return string Content with responsive images
     */
    public function processResponsiveImages(string $content, array $options = []): string
    {
        // Only add loading="lazy" and avoid adding classes that might break existing functionality
        $content = preg_replace_callback(
            '/<img([^>]*)>/i',
            function ($matches) use ($options) {
                $attributes = $matches[1];

                // Add loading="lazy" if not present
                if (!preg_match('/loading\s*=/i', $attributes)) {
                    $attributes .= ' loading="lazy"';
                }

                // Don't add img-responsive class automatically - it was breaking galleries

                return '<img' . $attributes . '>';
            },
            $content,
        );

        return $content;
    }

    /**
     * Process Markdown content with enhanced alignment support
     * Note: Video and gallery placeholders should be processed by helpers after markdown conversion
     *
     * @param string $markdown Markdown content
     * @param array $options Processing options
     * @return string Processed markdown with custom syntax converted
     */
    public function processMarkdown(string $markdown, array $options = []): string
    {
        // Enhanced Markdown processing with alignment helpers
        return $this->processMarkdownAlignmentSyntax($markdown, $options);
    }

    /**
     * Process custom Markdown alignment syntax
     *
     * @param string $markdown Markdown content
     * @param array $options Processing options
     * @return string Enhanced Markdown
     */
    private function processMarkdownAlignmentSyntax(string $markdown, array $options = []): string
    {
        // Convert alignment markers to HTML
        $alignmentPatterns = [
            '/^->(.+)<-$/m' => '<p style="text-align: center;">$1</p>',
            '/^->(.+)$/m' => '<p style="text-align: right;">$1</p>',
            '/^<-(.+)->$/m' => '<p style="text-align: justify;">$1</p>',
        ];

        foreach ($alignmentPatterns as $pattern => $replacement) {
            $markdown = preg_replace($pattern, $replacement, $markdown);
        }

        return $markdown;
    }
}
