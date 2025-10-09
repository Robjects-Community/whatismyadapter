<?php
use Cake\Core\Configure;

$this->layout = 'error';

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error429.php');

    $this->start('file');
?>
<h2><?= __d('cake', 'Too Many Requests') ?></h2>
<p class="error">
    <strong><?= __d('cake', 'Error') ?>: </strong>
    <?= h($message) ?>
</p>
<?php
    if (!empty($error->queryString)) :
        echo "<p>" . __d('cake', 'SQL Query') . ": " . h($error->queryString) . "</p>";
    endif;
?>
<?php if (!empty($error->params)) : ?>
        <strong><?= __d('cake', 'SQL Query Params') ?>: </strong>
        <?php Debugger::dump($error->params) ?>
<?php endif;
    echo $this->element('auto_table_warning');

    $this->end();
endif;
?>
<h2><?= __d('cake', 'Too Many Requests') ?></h2>
<p class="error">
    <?= __d('cake', 'You have exceeded the rate limit. Please try again later.') ?>
</p>