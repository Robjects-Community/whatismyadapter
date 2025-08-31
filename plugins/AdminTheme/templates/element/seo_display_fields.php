<div class="accordion mb-3" id="seoAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingSeoFields">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#seoFields" aria-expanded="false" aria-controls="seoFields">
                <?= __('SEO Fields') ?>
            </button>
        </h2>
        <div id="seoFields" class="accordion-collapse collapse" aria-labelledby="headingSeoFields" data-bs-parent="#seoAccordion">
            <div class="accordion-body">
            <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Meta Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($model->meta_description)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Meta Keywords') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($model->meta_keywords)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Facebook Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($model->facebook_description)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Linkedin Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($model->linkedin_description)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Instagram Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($model->instagram_description)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Twitter Description') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($model->twitter_description)); ?></p>
                        </div>
                    </div>
                <?php if (!isset($hideWordCount)) : ?>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Word Count') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($model->word_count)); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>