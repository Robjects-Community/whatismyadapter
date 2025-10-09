<?php
/**
 * @var \App\Model\Entity\ImageGallery $gallery
 */
?>
<div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
    <div class="dropdown">
        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <?= __('Actions') ?>
        </button>
        <ul class="dropdown-menu">
            <li>
                <?= $this->Html->link(
                    '<i class="fas fa-images me-2"></i>' . __('Manage Images'),
                    ['action' => 'manageImages', $gallery->id],
                    ['class' => 'dropdown-item', 'escape' => false],
                ) ?>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <?= $this->Html->link(
                    '<i class="fas fa-edit me-2"></i>' . __('Edit'),
                    ['action' => 'edit', $gallery->id],
                    ['class' => 'dropdown-item', 'escape' => false],
                ) ?>
            </li>
            <li>
                <?= $this->Html->link(
                    '<i class="fas fa-eye me-2"></i>' . __('View'),
                    ['action' => 'view', $gallery->id],
                    ['class' => 'dropdown-item', 'escape' => false],
                ) ?>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <?= $this->Form->postLink(
                    '<i class="fas fa-trash me-2"></i>' . __('Delete'),
                    ['action' => 'delete', $gallery->id],
                    [
                        'confirm' => __('Are you sure you want to delete the gallery "{0}"?', $gallery->name),
                        'class' => 'dropdown-item text-danger',
                        'escape' => false,
                    ],
                ) ?>
            </li>
        </ul>
    </div>
</div>