<div class="accordion mb-3" id="seoAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingSeoFields">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#seoFields" aria-expanded="false" aria-controls="seoFields">
                <?= __('SEO Fields') ?>
            </button>
        </h2>
        <div id="seoFields" class="accordion-collapse collapse" aria-labelledby="headingSeoFields" data-bs-parent="#seoAccordion">
            <div class="accordion-body">
                <div class="mb-3">
                    <?php echo $this->Form->control('meta_title', ['class' => 'form-control' . ($this->Form->isFieldError('meta_title') ? ' is-invalid' : '')]); ?>
                        <?php if ($this->Form->isFieldError('meta_title')): ?>
                        <div class="invalid-feedback">
                            <?= $this->Form->error('meta_title') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->Form->control('meta_description', ['class' => 'form-control' . ($this->Form->isFieldError('meta_description') ? ' is-invalid' : '')]); ?>
                        <?php if ($this->Form->isFieldError('meta_description')): ?>
                        <div class="invalid-feedback">
                            <?= $this->Form->error('meta_description') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->Form->control('meta_keywords', ['class' => 'form-control' . ($this->Form->isFieldError('meta_keywords') ? ' is-invalid' : '')]); ?>
                        <?php if ($this->Form->isFieldError('meta_keywords')): ?>
                        <div class="invalid-feedback">
                            <?= $this->Form->error('meta_keywords') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->Form->control('facebook_description', ['class' => 'form-control' . ($this->Form->isFieldError('facebook_description') ? ' is-invalid' : '')]); ?>
                        <?php if ($this->Form->isFieldError('facebook_description')): ?>
                        <div class="invalid-feedback">
                            <?= $this->Form->error('facebook_description') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->Form->control('linkedin_description', ['class' => 'form-control' . ($this->Form->isFieldError('linkedin_description') ? ' is-invalid' : '')]); ?>
                        <?php if ($this->Form->isFieldError('linkedin_description')): ?>
                        <div class="invalid-feedback">
                            <?= $this->Form->error('linkedin_description') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->Form->control('instagram_description', ['class' => 'form-control' . ($this->Form->isFieldError('instagram_description') ? ' is-invalid' : '')]); ?>
                        <?php if ($this->Form->isFieldError('instagram_description')): ?>
                        <div class="invalid-feedback">
                            <?= $this->Form->error('instagram_description') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->Form->control('twitter_description', ['class' => 'form-control' . ($this->Form->isFieldError('twitter_description') ? ' is-invalid' : '')]); ?>
                        <?php if ($this->Form->isFieldError('twitter_description')): ?>
                        <div class="invalid-feedback">
                            <?= $this->Form->error('twitter_description') ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!isset($hideWordCount)) : ?>
                <div class="mb-3">
                    <?php echo $this->Form->control('word_count', ['type' => 'number', 'class' => 'form-control' . ($this->Form->isFieldError('word_count') ? ' is-invalid' : '')]); ?>
                    <?php if ($this->Form->isFieldError('word_count')): ?>
                    <div class="invalid-feedback">
                        <?= $this->Form->error('word_count') ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>