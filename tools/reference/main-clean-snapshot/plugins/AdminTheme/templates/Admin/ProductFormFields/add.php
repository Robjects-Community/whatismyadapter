<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ProductFormField $productFormField
 */
$this->assign('title', __('Add Product Form Field'));
?>
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><?php echo h($this->fetch('title')) ?></h1>
        <div>
            <?= $this->Html->link(__('Back to list'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
    <?= $this->element('AdminTheme.Admin/ProductFormFields/form', compact('productFormField')) ?>
</div>
