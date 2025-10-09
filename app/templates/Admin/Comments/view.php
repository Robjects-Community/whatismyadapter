<?php
$this->assign('title', __('comment Details'));
?>
<div class="comment view content">
    <h3><?= __('comment') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($comment->id ?? '') ?></td>
        </tr>
    </table>
</div>
