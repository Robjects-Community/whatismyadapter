<?php $hideColumns = $hideColumns ?? []; ?>
<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title"><?= __('Related Products/Pages') ?></h4>
        <?php if (!empty($products)) : ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php if (!in_array('User', $hideColumns)) : ?>
                        <th><?= __('User') ?></th>
                        <?php endif; ?>
                        <th><?= __('Kind') ?></th>
                        <th><?= __('Title') ?></th>
                        <th><?= __('Published') ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product) : ?>
                    <tr>
                        <?php if (!in_array('User', $hideColumns)) : ?>
                        <td>
                            <?= $product->hasValue('user') ? $this->Html->link($product->user->username, ['controller' => 'Users', 'action' => 'view', $product->user->id], ['class' => 'btn btn-link']) : '' ?>
                        </td>
                        <?php endif; ?>
                        <td><?= h($product->kind) ?></td>
                        <td>
                            <?php $ruleName = $product->kind == 'product' ? 'product-by-slug' : 'page-by-slug';?>
                            <?php if ($product->is_published == true) : ?>
                                <?= $this->Html->link(
                                    $product->title,
                                    [
                                        'controller' => 'Products',
                                        'action' => 'view-by-slug',
                                        'slug' => $product->slug,
                                        '_name' => $ruleName,
                                    ],
                                    ['escape' => false],
                                );
                                ?>
                            <?php else : ?>
                                <?= $this->Html->link(
                                    $product->title,
                                    [
                                        'prefix' => 'Admin',
                                        'controller' => 'Products',
                                        'action' => 'view',
                                        $product->id,
                                    ],
                                    ['escape' => false],
                                ) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $product->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?>
                        </td>
                        <td class="actions">
                            <?= $this->element('evd_dropdown', ['controller' => 'Products', 'model' => $product, 'display' => 'title']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>