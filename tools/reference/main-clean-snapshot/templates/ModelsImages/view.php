<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ModelsImage $modelsImage
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Models Image'), ['action' => 'edit', $modelsImage->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Models Image'), ['action' => 'delete', $modelsImage->id], ['confirm' => __('Are you sure you want to delete # {0}?', $modelsImage->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Models Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Models Image'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="modelsImages view content">
            <h3><?= h($modelsImage->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($modelsImage->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Model') ?></th>
                    <td><?= h($modelsImage->model) ?></td>
                </tr>
                <tr>
                    <th><?= __('Article') ?></th>
                    <td><?= $modelsImage->hasValue('article') ? $this->Html->link($modelsImage->article->title, ['controller' => 'Articles', 'action' => 'view', $modelsImage->article->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Image') ?></th>
                    <td><?= $modelsImage->hasValue('image') ? $this->Html->link($modelsImage->image->name, ['controller' => 'Images', 'action' => 'view', $modelsImage->image->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($modelsImage->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($modelsImage->modified) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>