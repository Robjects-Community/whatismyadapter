<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
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
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($tag->title) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Title') ?></th>
                            <td><?= h($tag->title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td><?= h($tag->slug) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($tag->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($tag->modified) ?></td>
                        </tr>
                    </table>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($tag->description)); ?></p>
                        </div>
                    </div>

                    <div class="mt-4">
                    <?= $this->element('seo_display_fields', ['model' => $tag, 'hideWordCount' => true]); ?>
                    </div>

                    <?= $this->element('related/articles', ['articles' => $tag->articles]) ?>
                </div>
            </div>
        </div>
    </div>
</div>