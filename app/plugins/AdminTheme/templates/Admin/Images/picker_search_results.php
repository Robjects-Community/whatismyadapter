<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 * @var string|null $galleryId
 * @var string $viewType
 */
?>
<?php use App\Utility\SettingsManager; ?>

<?php if ($viewType === 'grid'): ?>
    <div class="album py-5 bg-body-tertiary">
        <div class="container">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                <?php foreach ($images as $image): ?>
                        <div class="col">
                            <div class="card shadow-sm position-relative">
                                <div class="form-check position-absolute top-0 start-0 m-2" style="z-index: 10;">
                                    <input type="checkbox" name="image_ids[]" value="<?= h($image->id) ?>" class="image-checkbox form-check-input" id="img-<?= h($image->id) ?>">
                                    <label class="form-check-label visually-hidden" for="img-<?= h($image->id) ?>">
                                        <?= __('Select {0}', h($image->name)) ?>
                                    </label>
                                </div>
                                
                                <?= $this->Html->image(
                                    SettingsManager::read('ImageSizes.large') . '/' . $image->image, [
                                        'pathPrefix' => 'files/Images/image/',
                                        'alt' => $image->alt_text,
                                        'class' => 'card-img-top'
                                ]) ?>
                                
                                <div class="card-body">
                                    <p class="card-text"><?= h($image->name) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?= h($image->created->format('M j, Y')) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?= $this->element('pagination', ['recordCount' => count($images)]) ?>
<?php else: ?>
    <table class="table table-striped">
      <thead>
        <tr>
              <th scope="col" width="50"><?= __('Select') ?></th>
              <th scope="col"><?= __('Image') ?></th>
              <th scope="col"><?= $this->Paginator->sort('name') ?></th>
              <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
              <th scope="col"><?= $this->Paginator->sort('created') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($images as $image): ?>
        <tr>
                <td>
                    <input type="checkbox" name="image_ids[]" value="<?= h($image->id) ?>" class="image-checkbox form-check-input">
                </td>
                <td>
                    <div class="position-relative">
                        <?= $this->element('image/icon', ['model' => $image, 'icon' => $image->teenyImageUrl, 'preview' => $image->extraLargeImageUrl]); ?>
                    </div>
                </td>
                <td><?= h($image->name) ?></td>
                <td><?= h($image->created) ?></td>
                <td><?= h($image->modified) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?= $this->element('pagination', ['recordCount' => count($images), 'search' => $search ?? '']) ?>
<?php endif; ?>