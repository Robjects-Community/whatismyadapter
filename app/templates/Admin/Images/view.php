<?php
$this->assign('title', __('image Details'));
?>
<div class="image view content">
    <h3><?= __('image') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($image->id ?? '') ?></td>
        </tr>
    </table>
</div>
