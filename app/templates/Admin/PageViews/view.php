<?php
$this->assign('title', __('pageView Details'));
?>
<div class="pageView view content">
    <h3><?= __('pageView') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($pageView->id ?? '') ?></td>
        </tr>
    </table>
</div>
