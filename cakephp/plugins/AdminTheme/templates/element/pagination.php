<?php
/**
 * Enhanced Pagination Element
 * 
 * @var \App\View\AppView $this
 * @var array $options Configuration options
 */

$defaults = [
    'showCounter' => true,
    'showPageInfo' => true,
    'class' => 'd-flex justify-content-center',
    'counterClass' => 'd-flex justify-content-center mt-2',
    'preserveParams' => true,
];
$config = array_merge($defaults, $options ?? []);

$this->Paginator->setTemplates([
    'nextActive' => '<li class="page-item"><a class="page-link" rel="next" aria-label="Next" href="{{url}}">&raquo;</a></li>',
    'nextDisabled' => '<li class="page-item disabled"><a class="page-link" aria-label="Next" href="" onclick="return false;">&raquo;</a></li>',
    'prevActive' => '<li class="page-item"><a class="page-link" rel="prev" aria-label="Previous" href="{{url}}">&laquo;</a></li>',
    'prevDisabled' => '<li class="page-item disabled"><a class="page-link" aria-label="Previous" href="" onclick="return false;">&laquo;</a></li>',
    'number' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
    'current' => '<li class="page-item active"><a class="page-link" href="">{{text}}</a></li>',
    'counterRange' => 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total',
    'counterPages' => 'Page {{page}} of {{pages}}',
]);

// Build pagination options with query parameter preservation
$paginationOptions = [
    'url' => [
        'controller' => $this->request->getParam('controller'),
        'action' => $this->request->getParam('action'),
    ],
];

// Preserve all query parameters if requested
if ($config['preserveParams']) {
    $queryParams = $this->request->getQueryParams();
    // Remove 'page' parameter as it's handled by paginator
    unset($queryParams['page']);
    
    if (!empty($queryParams)) {
        $paginationOptions['url']['?'] = $queryParams;
    }
}
?>
<?php if ($this->Paginator->total() > 1): ?>
    <div class="<?= h($config['class']) ?>">
        <nav aria-label="<?= __('Pagination Navigation') ?>">
            <ul class="pagination">
                <?= $this->Paginator->prev('&laquo;', ['escape' => false], null, ['class' => 'page-link'], $paginationOptions) ?>
                <?= $this->Paginator->numbers([], ['class' => 'page-link'], $paginationOptions) ?>
                <?= $this->Paginator->next('&raquo;', ['escape' => false], null, ['class' => 'page-link'], $paginationOptions) ?>
            </ul>
        </nav>
    </div>
    
    <?php if ($config['showCounter'] && $this->Paginator->total() > 0): ?>
        <div class="<?= h($config['counterClass']) ?>">
            <small class="text-muted">
                <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
            </small>
        </div>
    <?php endif; ?>
<?php endif; ?>