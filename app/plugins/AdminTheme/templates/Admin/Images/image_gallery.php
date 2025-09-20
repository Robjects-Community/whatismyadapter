<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<?php use App\Utility\SettingsManager; ?>
<div class="album bg-body-tertiary mb-3">
    <div class="container">
        <div id="imageResults" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <?php if (!empty($images)): ?>
            <?php foreach ($images as $image): ?>
                <div class="col">
                    <div class="card shadow-sm">
                        <?= $this->Html->image(SettingsManager::read('ImageSizes.large') . '/' . $image->image, 
                            [
                                'pathPrefix' => 'files/Images/image/',
                                'alt' => $image->alt_text,
                                'class' => 'card-img-top insert-image',
                                'data-src' => $image->image,
                                'data-name' => $image->name,
                                'data-id' => $image->id,
                                'data-alt' => $image->alt_text
                            ]
                        ) ?>
                        <div class="card-body">
                        <p class="card-text"><?= h($image->name) ?></p>
                            <?php
                            $imageSizes = SettingsManager::read('ImageSizes');
                            
                            // Create proper options with labels
                            $sizeOptions = [];
                            if (is_array($imageSizes) && !empty($imageSizes)) {
                                foreach ($imageSizes as $name => $width) {
                                    $sizeOptions[$width] = ucfirst($name) . ' (' . $width . 'px)';
                                }
                                // Sort by size (key) ascending - smallest to biggest
                                ksort($sizeOptions);
                            } else {
                                // Fallback if ImageSizes is not available (ordered smallest to biggest)
                                $sizeOptions = [
                                    '10' => 'Micro (10px)',
                                    '50' => 'Teeny (50px)',
                                    '100' => 'Tiny (100px)',
                                    '200' => 'Small (200px)',
                                    '300' => 'Medium (300px)',
                                    '400' => 'Large (400px)',
                                    '500' => 'Extra Large (500px)',
                                    '800' => 'Massive (800px)'
                                ];
                            }
                            
                            echo $this->Form->select(
                                'size',
                                $sizeOptions,
                                [
                                    'hiddenField' => false,
                                    'id' => $image->id . '_size',
                                    'class' => 'form-select',
                                    'value' => SettingsManager::read('ImageSizes.large') ?: '400'
                                ]
                            );
                            ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-images fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted"><?= __('No images found') ?></h5>
                        <?php if (!empty($search ?? '')): ?>
                            <p class="text-muted">
                                <?= __('No images match your search for "{0}"', h($search)) ?>
                            </p>
                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('imageSearch').value = ''; document.getElementById('imageSearch').dispatchEvent(new Event('input'));">
                                <?= __('Clear Search') ?>
                            </button>
                        <?php else: ?>
                            <p class="text-muted">
                                <?= __('Upload some images first to insert them into your content.') ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->element('pagination', ['recordCount' => count($images), 'search' => $search ?? '']) ?>
