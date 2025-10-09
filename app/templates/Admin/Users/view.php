<?php
$this->assign('title', __('user Details'));
?>
<div class="user view content">
    <h3><?= __('user') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($user->id ?? '') ?></td>
        </tr>
    </table>
</div>
