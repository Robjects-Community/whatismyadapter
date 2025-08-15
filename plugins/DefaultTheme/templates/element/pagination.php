<?php
$this->Paginator->setTemplates([
    'nextActive' => '<li class="page-item"><a class="page-link" rel="next" aria-label="Next" href="{{url}}">&raquo;</a></li>',
    'nextDisabled' => '<li class="page-item disabled"><a class="page-link" aria-label="Next" href="" onclick="return false;">&raquo;</a></li>',
    'prevActive' => '<li class="page-item"><a class="page-link" rel="prev" aria-label="Previous" href="{{url}}">&laquo;</a></li>',
    'prevDisabled' => '<li class="page-item disabled"><a class="page-link" aria-label="Previous" href="" onclick="return false;">&laquo;</a></li>',
    'number' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
    'current' => '<li class="page-item active"><a class="page-link" href="">{{text}}</a></li>',
    'counterRange' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total'),
    'counterPages' => __('Page {{page}} of {{pages}}'),
]);

$this->Paginator->options([
    'url' => [
        'lang' => $this->request->getParam('lang'),
        '_name' => 'home',
        '?' => array_filter([
            'tag' => $this->request->getQuery('tag'),
            'year' => $this->request->getQuery('year'),
            'month' => $this->request->getQuery('month'),
        ]),
    ],
]);
?>
<?php if ($this->Paginator->total() > 1) : ?>
<div class="pagination-wrapper my-5">
    <nav aria-label="<?= __('Article pagination') ?>" class="d-flex justify-content-center">
        <ul class="pagination pagination-lg shadow-sm">
            <?= $this->Paginator->prev('&laquo;', [
                'escape' => false,
                'class' => 'page-link',
                'title' => __('Previous page'),
            ]) ?>
            <?= $this->Paginator->numbers([
                'class' => 'page-link',
            ]) ?>
            <?= $this->Paginator->next('&raquo;', [
                'escape' => false,
                'class' => 'page-link',
                'title' => __('Next page'),
            ]) ?>
        </ul>
    </nav>
    
    <?php if ($this->Paginator->total() > 0) : ?>
    <div class="pagination-info text-center mt-3">
        <small class="text-muted">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </small>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>