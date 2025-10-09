<?php
// src/View/Helper/VideoHelper.php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * Video helper
 *
 * Handles processing of YouTube videos in article content
 */
class VideoHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array<string>
     */
    protected array $helpers = ['Html'];

    /**
     * Process video placeholders in content
     *
     * @param string $content The content containing video placeholders
     * @param array $options Processing options
     * @return string The processed content
     */
    public function processVideoPlaceholders(string $content, array $options = []): string
    {
        return $this->processYouTubePlaceholders($content);
    }

    /**
     * Replace YouTube video placeholders with GDPR-compliant embed code
     *
     * @param string $content The content containing video placeholders
     * @return string The processed content
     */
    public function processYouTubePlaceholders(string $content): string
    {
        return preg_replace_callback(
            '/\[youtube:([a-zA-Z0-9_-]+):(\d+):(\d+):([^\]]*)\]/',
            function ($matches) {
                $videoId = $matches[1];
                $width = $matches[2];
                $height = $matches[3];
                $title = $matches[4];

                return $this->generateGdprCompliantEmbed($videoId, $width, $height, $title);
            },
            $content,
        );
    }

    /**
     * Generate GDPR-compliant embed code for YouTube videos
     *
     * @param string $videoId YouTube video ID
     * @param string $width Video width
     * @param string $height Video height
     * @param string $title Video title
     * @return string HTML for the video embed
     */
    protected function generateGdprCompliantEmbed(
        string $videoId,
        string $width,
        string $height,
        string $title,
    ): string {
        $thumbnailUrl = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";

        return $this->getView()->element('site/youtube_embed', [
            'videoId' => $videoId,
            'width' => $width,
            'height' => $height,
            'title' => $title,
            'thumbnailUrl' => $thumbnailUrl,
        ]);
    }
}
