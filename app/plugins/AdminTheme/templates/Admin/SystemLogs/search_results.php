<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\SystemLog> $systemLogs
 */
?>
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