<?php
$this->assign('title', __('imageGallery Details'));
?>
<div class="imageGallery view content">
    <h3><?= __('imageGallery') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($imageGallery->id ?? '') ?></td>
        </tr>
    </table>
</div>
