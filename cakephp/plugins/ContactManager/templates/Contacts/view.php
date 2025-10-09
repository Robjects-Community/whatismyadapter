<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('Edit Contact'), ['action' => 'edit', $contact->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Contact'), ['action' => 'delete', $contact->id], ['confirm' => __('Are you sure you want to delete # {0}?', $contact->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Contacts'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Contact'), ['action' => 'add']) ?> </li>
    </ul>
</div>
<div class="contacts view large-10 medium-9 columns">
    <h2><?= h($contact->id) ?></h2>
    <div class="row">
        <div class="large-5 columns strings">
            <h6 class="subheader"><?= __('First Name') ?></h6>
            <p><?= h($contact->first_name) ?></p>
            <h6 class="subheader"><?= __('Last Name') ?></h6>
            <p><?= h($contact->last_name) ?></p>
            <h6 class="subheader"><?= __('Email') ?></h6>
            <p><?= h($contact->email) ?></p>
            <h6 class="subheader"><?= __('Address') ?></h6>
            <p><?= h($contact->address) ?></p>
            <h6 class="subheader"><?= __('City') ?></h6>
            <p><?= h($contact->city) ?></p>
            <h6 class="subheader"><?= __('State') ?></h6>
            <p><?= h($contact->state) ?></p>
            <h6 class="subheader"><?= __('Country') ?></h6>
            <p><?= h($contact->country) ?></p>
        </div>
        <div class="large-2 columns numbers end">
            <h6 class="subheader"><?= __('Id') ?></h6>
            <p><?= $this->Number->format($contact->id) ?></p>
            <h6 class="subheader"><?= __('Contact Num') ?></h6>
            <p><?= $this->Number->format($contact->contact_num) ?></p>
        </div>
        <div class="large-2 columns dates end">
            <h6 class="subheader"><?= __('Created') ?></h6>
            <p><?= h($contact->created) ?></p>
        </div>
    </div>
</div>
