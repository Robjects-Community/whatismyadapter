<?php use Cake\Routing\Router; ?>
<div class="p-4">
    <h4 class="fst-italic"><?= __('Feeds') ?></h4>
    <ol class="list-unstyled">
        <li>
            <?= $this->Html->link(
                '<i class="bi bi-rss"></i> ' . __('RSS Feed'),
                Router::url(['_name' => 'rss', 'lang' => $this->request->getParam('lang', 'en')], true),
                [
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                    'title' => __('Latest Articles RSS Feed'),
                    'escape' => false
                ]
            ); ?>
        </li>
    </ol>
</div>