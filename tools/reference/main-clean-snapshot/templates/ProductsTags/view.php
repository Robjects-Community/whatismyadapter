<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $productsTag
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Products Tag'), ['action' => 'edit', $productsTag->product_id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Products Tag'), ['action' => 'delete', $productsTag->product_id], ['confirm' => __('Are you sure you want to delete # {0}?', $productsTag->product_id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Products Tags'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Products Tag'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="productsTags view content">
            <h3><?= h($productsTag->Array) ?></h3>
            <table>
                <tr>
                    <th><?= __('Product Id') ?></th>
                    <td><?= h($productsTag->product_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Tag Id') ?></th>
                    <td><?= h($productsTag->tag_id) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>