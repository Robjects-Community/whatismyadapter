<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>
<h4 class="fst-italic"><?= __('Tags') ?></h4>
<?php if (!empty($article->tags)) : ?>
    <?php foreach ($article->tags as $tag) : ?>
        <?= $this->Html->link(
            htmlspecialchars_decode($tag->title),
            [
                '_name' => 'home',
                '?' => ['tag' => $tag->id],
            ],
            ['class' => 'btn btn-outline btn-sm'],
        ) ?>
    <?php endforeach; ?>
<?php endif; ?>