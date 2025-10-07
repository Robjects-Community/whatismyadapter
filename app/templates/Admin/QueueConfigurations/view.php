<?php
$this->assign('title', __('queueConfiguration Details'));
?>
<div class="queueConfiguration view content">
    <h3><?= __('queueConfiguration') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($queueConfiguration->id ?? '') ?></td>
        </tr>
    </table>
</div>
