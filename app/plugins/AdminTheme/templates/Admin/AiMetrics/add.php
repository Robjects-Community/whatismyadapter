<?php
/**
 * AI Metric Add Template
 * 
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AiMetric $aiMetric
 */
$this->assign('title', __('Add AI Metric'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('Add AI Metric') ?></h3>
            <div class="actions">
                <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?= $this->Form->create($aiMetric) ?>
                <fieldset>
                    <?php
                        echo $this->Form->control('task_type', ['class' => 'form-control']);
                        echo $this->Form->control('execution_time_ms', ['class' => 'form-control']);
                        echo $this->Form->control('tokens_used', ['class' => 'form-control']);
                        echo $this->Form->control('cost_usd', ['class' => 'form-control']);
                        echo $this->Form->control('success', ['class' => 'form-check-input']);
                        echo $this->Form->control('error_message', ['class' => 'form-control']);
                        echo $this->Form->control('model_used', ['class' => 'form-control']);
                    ?>
                </fieldset>
                <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
