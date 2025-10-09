<?php
$this->assign('title', __('emailTemplate Details'));
?>
<div class="emailTemplate view content">
    <h3><?= __('emailTemplate') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($emailTemplate->id ?? '') ?></td>
        </tr>
    </table>
</div>
