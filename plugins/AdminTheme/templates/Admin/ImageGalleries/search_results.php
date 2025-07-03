<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ImageGallery> $imageGalleries
 * @var string $viewType
 * @var string|null $search
 * @var string|null $statusFilter
 */

if ($viewType === 'grid'): ?>
    <!-- Grid View Results -->
    <?php if (empty($imageGalleries)): ?>
        <?= $this->element('empty_state', [
            'type' => 'search',
            'title' => __('No galleries found'),
            'message' => __('Try adjusting your search terms or filters.')
        ]) ?>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php foreach ($imageGalleries as $gallery): ?>
            <div class="col">
                <?= $this->element('ImageGalleries/gallery_card', ['gallery' => $gallery]) ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?= $this->element('pagination') ?>
    <?php endif; ?>

<?php else: ?>
    <!-- List View Results -->
    <?php if (empty($imageGalleries)): ?>
        <?= $this->element('empty_state', [
            'type' => 'search',
            'title' => __('No galleries found'),
            'message' => __('Try adjusting your search terms or filters.')
        ]) ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?= __('Preview') ?></th>
                        <th><?= $this->Paginator->sort('name') ?></th>
                        <th><?= $this->Paginator->sort('slug') ?></th>
                        <th><?= __('Status') ?></th>
                        <th><?= __('Images') ?></th>
                        <th><?= $this->Paginator->sort('created') ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($imageGalleries as $gallery): ?>
                    <tr>
                        <td>
                            <?php if ($gallery->hasPreviewImage()): ?>
                                <img src="<?= h($gallery->getPreviewImageUrl()) ?>"
                                     alt="<?= h($gallery->name) ?>"
                                     class="img-thumbnail gallery-preview-thumb"
                                     style="width: 60px; height: 45px; object-fit: cover;"
                                     data-bs-toggle="popover"
                                     data-bs-trigger="hover"
                                     data-bs-content="<img src='<?= h($gallery->getPreviewImageUrl()) ?>' style='max-width: 300px; max-height: 200px;' alt='<?= h($gallery->name) ?>'>"
                                     data-bs-html="true"
                                     data-bs-placement="right">
                            <?php elseif (!empty($gallery->images)): ?>
                                <?= $this->element('image/icon', [
                                    'model' => $gallery->images[0],
                                    'icon' => $gallery->images[0]->tinyImageUrl,
                                    'preview' => $gallery->images[0]->mediumImageUrl,
                                    'class' => 'img-thumbnail gallery-preview-thumb'
                                ]) ?>
                            <?php else: ?>
                                <div class="text-center text-muted d-flex align-items-center justify-content-center img-thumbnail"
                                     style="width: 60px; height: 45px; border: 1px solid #ddd; border-radius: 4px;">
                                    <i class="fas fa-images"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= h($gallery->name) ?></strong>
                            <?php if ($gallery->description): ?>
                                <br><small class="text-muted"><?= $this->Text->truncate(h($gallery->description), 50) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code><?= h($gallery->slug) ?></code></td>
                        <td>
                            <?php if ($gallery->is_published): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-eye"></i> <?= __('Published') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-eye-slash"></i> <?= __('Draft') ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?= $gallery->getImageCount() ?> <?= __('images') ?>
                            </span>
                        </td>
                        <td><?= $gallery->created->format('M j, Y') ?></td>
                        <td>
                            <?= $this->element('evd_dropdown', [
                                'model' => $gallery,
                                'display' => 'name',
                                'controller' => 'ImageGalleries'
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?= $this->element('pagination') ?>
    <?php endif; ?>
<?php endif; ?>