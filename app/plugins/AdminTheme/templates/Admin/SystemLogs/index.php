<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\SystemLog> $systemLogs
 * @var array $levels
 * @var array $groupNames
 * @var string|null $selectedLevel
 * @var string|null $selectedGroup
 */
?>
<div class="systemLogs index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= __('System Logs') ?></h3>
    </div>
    <div class="mb-3">
        <input type="text" id="logSearch" class="form-control" placeholder="<?= __('Search logs...') ?>">
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="btn-group" role="group" aria-label="Level filters">
                <?= $this->Html->link(__('All Levels'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary' . (!$selectedLevel ? ' active' : '')]) ?>
                <?php foreach ($levels as $level): ?>
                    <?= $this->Html->link(
                        h($level),
                        ['action' => 'index', '?' => ['level' => $level] + ($selectedGroup ? ['group' => $selectedGroup] : [])],
                        ['class' => 'btn btn-outline-secondary' . ($selectedLevel === $level ? ' active' : '')]
                    ) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-6 text-end">
            <?php if (count($systemLogs) > 0): ?>
                <?= $this->Form->postLink(__('Delete All Logs'), ['action' => 'delete', 'all'], ['confirm' => __('Are you sure you want to delete all logs?'), 'class' => 'btn btn-danger']) ?>
                <?php if ($selectedLevel): ?>
                    <?= $this->Form->postLink(__('Delete {0}', h($selectedLevel)), ['action' => 'delete', 'level', $selectedLevel], ['confirm' => __('Are you sure you want to delete all {0} logs?', $selectedLevel), 'class' => 'btn btn-outline-danger']) ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="d-flex flex-wrap gap-2" role="group" aria-label="Group filters">
                <?= $this->Html->link(__('All Groups'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary' . (!$selectedGroup ? ' active' : '')]) ?>
                <?php foreach ($groupNames as $group): ?>
                    <?= $this->Html->link(
                        h($group),
                        ['action' => 'index', '?' => ['group' => $group] + ($selectedLevel ? ['level' => $selectedLevel] : [])],
                        ['class' => 'btn btn-outline-secondary' . ($selectedGroup === $group ? ' active' : '')]
                    ) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-6 text-end">
            <?php if (count($systemLogs) > 0 && $selectedGroup): ?>
                <?= $this->Form->postLink(__('Delete {0}', h($selectedGroup)), ['action' => 'delete', 'group', $selectedGroup], ['confirm' => __('Are you sure you want to delete all logs in group {0}?', $selectedGroup), 'class' => 'btn btn-outline-danger']) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?= __('Level') ?></th>
                    <th><?= __('Group Name') ?></th>
                    <th><?= __('Message') ?></th>
                    <th><?= __('Created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($systemLogs as $systemLog): ?>
                <tr>
                    <td><?= h($systemLog->level) ?></td>
                    <td><?= h($systemLog->group_name) ?></td>
                    <td>
                        <?php
                        $truncatedMessage = substr($systemLog->message, 0, 50) . '...';
                        $fullMessage = h($systemLog->message);
                        $prettyMessage = $this->element('pretty_message', ['message' => $fullMessage]);
                        ?>
                        <span tabindex="0" class="d-inline-block" data-bs-toggle="popover" data-bs-trigger="focus" title="Full Message" data-bs-content="<?= $prettyMessage ?>" data-bs-html="true">
                            <?= h($truncatedMessage) ?>
                        </span>
                    </td>
                    <td><?= h($systemLog->created->format('Y-m-d H:i')) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $systemLog->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $systemLog->id], ['confirm' => __('Are you sure you want to delete # {0}?', $systemLog->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= $this->element('pagination') ?>
</div>

<script>
   document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('logSearch');
    const resultsContainer = document.querySelector('tbody');
    const currentUrl = new URL(window.location.href);

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();
            
            // Update the URL with the search term
            if (searchTerm) {
                currentUrl.searchParams.set('search', searchTerm);
            } else {
                currentUrl.searchParams.delete('search');
            }

            // Preserve existing level and group filters
            const level = currentUrl.searchParams.get('level');
            const group = currentUrl.searchParams.get('group');
            if (level) currentUrl.searchParams.set('level', level);
            if (group) currentUrl.searchParams.set('group', group);

            fetch(currentUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                resultsContainer.innerHTML = html;
                initPopovers();
            })
            .catch(error => console.error('Error:', error));
        }, 300);
    });

    initPopovers();
});

function initPopovers() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            container: 'body'
        })
    })
}
</script>