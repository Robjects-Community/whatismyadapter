<?php
$this->assign('title', __('internationalisation Details'));
?>
<div class="internationalisation view content">
    <h3><?= __('internationalisation') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($internationalisation->id ?? '') ?></td>
        </tr>
    </table>
</div>
