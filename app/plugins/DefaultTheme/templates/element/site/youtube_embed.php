<?php
/**
 * @var \App\View\AppView $this
 * @var string $videoId
 * @var string $width
 * @var string $height
 * @var string $title
 * @var string $thumbnailUrl
 */
?>
<div class="youtube-embed" data-video-id="<?= h($videoId) ?>" data-title="<?= h($title) ?>">
    <div class="youtube-placeholder">
        <img src="<?= h($thumbnailUrl) ?>" 
             alt="<?= h($title) ?>"
             class="youtube-thumbnail">
        <div class="youtube-consent-overlay">
            <p class="mb-3"><?= __('This content is hosted by YouTube. By showing the external content you accept the privacy policy of YouTube.') ?></p>
            <button class="btn btn-primary youtube-consent-btn" 
                    data-video-id="<?= h($videoId) ?>"
                    onclick="loadYouTubeVideo(this)">
                <?= __('Load video') ?>
            </button>
        </div>
    </div>
</div>