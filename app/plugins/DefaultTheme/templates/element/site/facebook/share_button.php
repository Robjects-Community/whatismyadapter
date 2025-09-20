<?php if (!empty($consentData) && $consentData['marketing_consent']) :?>
    <?php 
    $url = $this->Url->build(
        ['_name' => $article->kind . '-by-slug', 'slug' => $article->slug],
        ['fullBase' => true]
    );
    $encodedUrl = urlencode($url) . ';src=sdkpreparse';
    ?>
    <div class="fb-share-button" data-href="<?= $url ?>" data-layout="" data-size="">
        <a target="_blank" 
        href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>" 
        class="fb-xfbml-parse-ignore">
            <?= __('Share') ?>
        </a>
    </div>
<?php endif; ?>