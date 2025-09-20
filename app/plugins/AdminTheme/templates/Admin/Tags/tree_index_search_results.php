<?php
    if (!empty($tags)) {
        echo $this->element('tree/tag_tree', ['tags' => $tags, 'level' => 0]);
    } else {
        echo $this->Html->tag('p', __('No tags found.'));
    }
?>