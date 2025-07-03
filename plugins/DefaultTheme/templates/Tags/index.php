<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Tag> $tags
 */
?>
<div class="tags">
    <h2 class="mb-4 text-primary"><?= __('Tags') ?></h2>
    <?php if (!empty($tags->toArray())) : ?>
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($tags as $tag): ?>
                    <?= $this->Html->link(
                        htmlspecialchars_decode($tag->title),
                        ['action' => 'view-by-slug', $tag->slug],
                        ['class' => 'btn btn-outline-primary']
                    ) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>