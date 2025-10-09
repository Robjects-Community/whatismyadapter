<ul class="nav nav-tabs" id="editorTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="editor-tab" data-bs-toggle="tab" href="#editor" role="tab" 
            aria-controls="editor" aria-selected="true">
            <?= __('Body Editor') ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="preview-tab" data-bs-toggle="tab" href="#preview" role="tab" 
            aria-controls="preview" aria-selected="false">
            <?= __('Body Preview') ?>
        </a>
    </li>
</ul>
<div class="tab-content" id="editorTabContent">
    <div class="tab-pane show active" id="editor" role="tabpanel" aria-labelledby="editor-tab">
        <div class="">
            <button type="button" class="btn my-1 btn-secondary" id="insertImageBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-images" viewBox="0 0 16 16">
                    <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                    <path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/>
                </svg>
                <?= __('Insert Image') ?>
            </button>
            <button type="button" class="btn my-1 btn-secondary" id="insertVideoBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-play-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M6.271 5.055a.5.5 0 0 1 .52.08L11 7.055a.5.5 0 0 1 0 .89L6.791 9.865a.5.5 0 0 1-.791-.39V5.525a.5.5 0 0 1 .271-.47z"/>
                </svg>
                <?= __('Insert Video') ?>
            </button>
            <button type="button" class="btn my-1 btn-secondary" id="insertGalleryBtn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-collection" viewBox="0 0 16 16">
                    <path d="M2.5 3.5a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-11zm2-2a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1h-7zM0 13a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 16 13V6a1.5 1.5 0 0 0-1.5-1.5h-13A1.5 1.5 0 0 0 0 6v7zm1.5.5A.5.5 0 0 1 1 13V6a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-13z"/>
                </svg>
                <?= __('Insert Gallery') ?>
            </button>
        </div>
        <div class="mb-3">
            <?php echo $this->Form->control(
                'markdown',
                [
                    'id' => 'product-markdown',
                    'rows' => '30',
                    'class' => 'form-control' . ($this->Form->isFieldError('markdown') ? ' is-invalid' : ''),
                    'label' => false,
                ],
            ); ?>
                <?php if ($this->Form->isFieldError('markdown')) : ?>
                <div class="invalid-feedback">
                    <?= $this->Form->error('markdown') ?>
                </div>
                <?php endif; ?>
        </div>
        <?php echo $this->Form->control('body', [
            'type' => 'textarea',
            'id' => 'product-body',
            'style' => 'display: none;',
        ]); ?>
    </div>
    <div class="tab-pane" id="preview" role="tabpanel" aria-labelledby="preview-tab">
        <div class="">
            <div class="card-body border rounded my-3" id="markdown-preview">

            </div>
        </div>
    </div>
</div>
<?= $this->Html->script('AdminTheme.markdown-it-media-insert') ?>