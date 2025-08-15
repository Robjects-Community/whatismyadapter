<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var array $images
 * @var string $carouselId
 */
?>
<?php if (!empty($images)) : ?>
<div class="col-md-6 mb-4">
<h4><?= __('Images') ?></h4>
<div id="<?= $carouselId ?>" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($images as $index => $image) : ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                <?= $this->Html->image(
                    SettingsManager::read('ImageSizes.medium', '200') . '/' . $image->image,
                    [
                    'pathPrefix' => 'files/Images/image/',
                    'class' => 'd-block w-100',
                    'alt' => $image->alt_text,
                    ],
                ) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#<?= $carouselId ?>" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
</div>
<?php endif; ?>