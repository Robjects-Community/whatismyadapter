<?php
    if (!empty($products)) {
        echo $this->element('tree/page_tree', ['products' => $products, 'level' => 0]);
    } else {
        echo $this->Html->tag('p', __('No pages found.'));
    }
?>
