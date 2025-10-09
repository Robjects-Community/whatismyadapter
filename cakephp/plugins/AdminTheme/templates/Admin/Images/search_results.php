<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 * @var string $viewType
 */
?>

<?php if ($viewType === 'grid'): ?>
    <div class="album py-5 bg-body-tertiary">
        <div class="container">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                <?php foreach ($images as $image): ?>
                        <div class="col">
                            <div class="card shadow-sm">
                                <?= $this->Html->image(
                                    SettingsManager::read('ImageSizes.large') . '/' . $image->image, [
                                        'pathPrefix' => 'files/Images/image/',
                                        'alt' => $image->alt_text,
                                        'class' => 'card-img-top'
                                ]) ?>
                                
                                <div class="card-body">
                                    <p class="card-text"><?= h($image->name) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="btn-group">
                                            <?= $this->Html->link(__('View'), ['action' => 'view', $image->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $image->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete {0}?', $image->name), 'class' => 'btn btn-sm btn-outline-secondary text-danger']) ?>
                                        </div>
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
          <th scope="col"><?= __('Image') ?></th>
          <th scope="col"><?= $this->Paginator->sort('name') ?></th>
          <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
          <th scope="col"><?= $this->Paginator->sort('created') ?></th>
          <th scope="col"><?= __('Actions') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($images as $image): ?>
    <tr>
            <td>
                <div class="position-relative">
                    <?= $this->element('image/icon',  ['model' => $image, 'icon' => $image->teenyImageUrl, 'preview' => $image->extraLargeImageUrl]); ?>
                </div>
            </td>
            <td><?= h($image->name) ?></td>
            <td><?= h($image->created) ?></td>
            <td><?= h($image->modified) ?></td>
            <td>
            <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                <div class="dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= __('Actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <?= $this->Html->link(__('View'), ['action' => 'view', $image->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $image->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete {0}?', $image->name), 'class' => 'dropdown-item text-danger']) ?>
                    </li>
                </ul>
                </div>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?= $this->element('pagination', ['recordCount' => count($images), 'search' => $search ?? '']) ?>
<?php endif; ?>