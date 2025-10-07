<?php
$this->assign('title', __('video Details'));
?>
<div class="video view content">
    <h3><?= __('video') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($video->id ?? '') ?></td>
        </tr>
    </table>
</div>
