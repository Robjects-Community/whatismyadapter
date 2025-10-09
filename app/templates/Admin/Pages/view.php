<?php
$this->assign('title', __('page Details'));
?>
<div class="page view content">
    <h3><?= __('page') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($page->id ?? '') ?></td>
        </tr>
    </table>
</div>
