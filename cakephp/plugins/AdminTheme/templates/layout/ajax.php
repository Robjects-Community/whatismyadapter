<?php
/**
 * AJAX Layout for modal and partial content
 * 
 * This layout is used for content that will be loaded via AJAX
 * into modals or other dynamic containers. It only renders the content
 * without the full page structure.
 * 
 * @var \App\View\AppView $this
 */
?>
<?= $this->fetch('content') ?>
<?= $this->fetch('scriptBottom') ?>