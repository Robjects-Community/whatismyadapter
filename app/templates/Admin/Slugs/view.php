<?php
$this->assign('title', __('slug Details'));
?>
<div class="slug view content">
    <h3><?= __('slug') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($slug->id ?? '') ?></td>
        </tr>
    </table>
</div>
