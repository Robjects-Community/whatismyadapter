<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGallery $imageGallery
 */
?>
<style>
/* Fix dropdown z-index issue in tables */
.table-responsive {
    overflow: visible !important;
}

.table .dropdown-menu {
    z-index: 1050 !important;
    position: absolute !important;
}

.table .dropdown {
    position: static;
}

.table .btn-group {
    position: relative;
}
</style>
<?php
echo $this->element('actions_card', [
    'modelName' => 'Image Gallery',
    'controllerName' => 'Image Galleries',
    'entity' => $imageGallery,
    'entityDisplayName' => $imageGallery->name
]);
?>
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($imageGallery->name) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($imageGallery->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td><?= h($imageGallery->slug) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($imageGallery->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($imageGallery->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Published') ?></th>
                            <td><?= $imageGallery->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($imageGallery->description); ?></p>
                        </div>
                    </div>
                    
                    <?= $this->element('seo_display_fields', ['model' => $imageGallery, 'hideWordCount' => true]); ?>
                    
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Gallery Images') ?></h5>
                            <?php
                            // Use GalleryCell for consistent rendering
                            echo $this->cell('Gallery::display', [
                                $imageGallery->id,
                                'admin',
                                ''
                            ]);
                            ?>
                            
                            <?php if (!empty($imageGallery->images)): ?>
                                <!-- Admin actions for gallery management -->
                                <div class="mt-4 pt-4 border-top">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="btn-group gap-2" role="group">
                                                <?= $this->Html->link(
                                                    '<i class="fas fa-edit me-2"></i>' . __('Manage Images'),
                                                    ['action' => 'manageImages', $imageGallery->id],
                                                    ['class' => 'btn btn-primary btn-lg', 'escape' => false]
                                                ) ?>
                                                
                                                <?= $this->Html->link(
                                                    '<i class="fas fa-plus me-2"></i>' . __('Add More Images'),
                                                    ['action' => 'edit', $imageGallery->id],
                                                    ['class' => 'btn btn-outline-secondary btn-lg ms-2', 'escape' => false]
                                                ) ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="text-muted small text-end">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <?= __('Click any image to view slideshow') ?><br>
                                                <small class="text-muted"><?= __('Press spacebar or use controls to play automatically') ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Slugs') ?></h4>
                            <?php if (!empty($imageGallery->slugs)) : ?>
                            <div class="table-responsive" style="overflow: visible;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($imageGallery->slugs as $slug) : ?>
                                        <tr>
                                            <td><?= h($slug->slug) ?></td>
                                            <td><?= h($slug->created) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'Slugs', 'model' => $slug, 'display' => 'slug']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


