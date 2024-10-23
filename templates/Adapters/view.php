<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Adapter $adapter
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Adapter'), ['action' => 'edit', $adapter->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Adapter'), ['action' => 'delete', $adapter->id], ['confirm' => __('Are you sure you want to delete # {0}?', $adapter->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Adapters'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Adapter'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="adapters view content">
            <h3><?= h($adapter->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($adapter->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Type') ?></th>
                    <td><?= h($adapter->type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Gender') ?></th>
                    <td><?= h($adapter->gender) ?></td>
                </tr>
                <tr>
                    <th><?= __('Additional Params') ?></th>
                    <td><?= h($adapter->additional_params) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($adapter->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($adapter->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($adapter->modified) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>