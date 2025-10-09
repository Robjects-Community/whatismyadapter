<?php
    echo $this->element('actions_card', [
        'modelName' => 'SystemLog',
        'controllerName' => 'SystemLogs',
        'entity' => $systemLog,
        'entityDisplayName' => $systemLog->group_name . ':' . $systemLog->level . ':' . $systemLog->created->format('Y-m-d H:i:s'),
        'hideNew' => true,
        'hideEdit' => true,
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-<?= strtolower($systemLog->level) ?>">
                    <h3 class="card-title text-white mb-0"><?= h($systemLog->level) ?></h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Level') ?></th>
                            <td><span class="badge bg-<?= strtolower($systemLog->level) ?>"><?= h($systemLog->level) ?></span></td>
                        </tr>
                        <tr>
                            <th><?= __('Group Name') ?></th>
                            <td><?= h($systemLog->group_name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= $systemLog->created->format('Y-m-d H:i:s') ?></td>
                        </tr>
                    </table>
                    <div class="mt-4">
                        <h5><?= __('Message') ?></h5>
                        <div class="p-3 rounded">
                            <?php
                            $lines = explode("\n", h($systemLog->message));
                            foreach ($lines as $line) {
                                if (strpos($line, 'Stack Trace') !== false) {
                                    echo "<strong class='text-danger'>$line</strong><br>";
                                } elseif (preg_match('/^#\d+/', $line)) {
                                    echo "<code class='text-muted'>$line</code><br>";
                                } else {
                                    echo "$line<br>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <?php if (!empty($systemLog->context)): ?>
                    <div class="mt-4">
                    <div class="p-3 rounded">
                        <h5><?= __('Context') ?></h5>
                        <pre class="bg-light text-dark"><?= json_encode(json_decode($systemLog->context), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></pre>
                    </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>