<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 * @var string[]|\Cake\Collection\CollectionInterface $articles
 */
?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Tag',
        'controllerName' => 'Tags',
        'entity' => $tag,
        'entityDisplayName' => $tag->title
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Edit Tag') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($tag, ['type' => 'file', 'class' => 'needs-validation', 'novalidate' => true, 'enctype' => 'multipart/form-data']) ?>
                    <fieldset>
                        <?= $this->element('form/tag') ?>
                        <?= $this->element('form/seo', ['hideWordCount' => true]) ?>                                              
                    </fieldset>
                    <div class="form-group">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->element('js/semanticui/dropdown', ['selector' => '#articles-select']); ?>