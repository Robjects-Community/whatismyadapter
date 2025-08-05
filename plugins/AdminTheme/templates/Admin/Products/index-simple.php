<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 * @var \Cake\Collection\CollectionInterface $viewsOverTime
 */
?>
<div class="products index">
    <h3>Products</h3>
    <?= $this->Html->link('Add Product', ['action' => 'add'], ['class' => 'button']) ?>
    
    <table>
        <tr>
            <th>Title</th>
            <th>User</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($products as $product): ?>
        <tr>
            <td><?= h($product->title) ?></td>
            <td><?= $product->user ? h($product->user->username) : 'N/A' ?></td>
            <td><?= h($product->verification_status) ?></td>
            <td>
                <?= $this->Html->link('Edit', ['action' => 'edit', $product->id]) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
