<?php
$this->assign('title', __('setting Details'));
?>
<div class="setting view content">
    <h3><?= __('setting') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($setting->id ?? '') ?></td>
        </tr>
    </table>
</div>
