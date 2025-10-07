<?php
$this->assign('title', __('homepageFeed Details'));
?>
<div class="homepageFeed view content">
    <h3><?= __('homepageFeed') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($homepageFeed->id ?? '') ?></td>
        </tr>
    </table>
</div>
