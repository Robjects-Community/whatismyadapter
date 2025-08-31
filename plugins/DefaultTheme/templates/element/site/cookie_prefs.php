<?php use Cake\Routing\Router; ?>
<?php if (!isset($consentData)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    WillowModal.show('<?= Router::url(['_name' => 'cookie-consent']) ?>', {
        title: '<?= __('Cookie Preferences') ?>',
        static: true,
        closeable: true,
        reload: true,
        dialogClass: 'modal-dialog-scrollable modal-lg'
    });
});
</script>
<?php endif; ?>