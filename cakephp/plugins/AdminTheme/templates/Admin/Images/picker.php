<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 * @var string|null $galleryId
 * @var string $viewType
 */
?>
<?php use App\Utility\SettingsManager; ?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Image'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
            <?php if ($galleryId): ?>
                <?= $this->Html->link(__('Back to Gallery'), ['controller' => 'ImageGalleries', 'action' => 'manageImages', $galleryId], ['class' => 'side-nav-item']) ?>
            <?php endif; ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="images index content">
            <h3><?= __('Select Images for Gallery') ?></h3>
            
            <?= $this->Form->create(null, [
                'url' => ['controller' => 'ImageGalleries', 'action' => 'addImages', $galleryId],
                'id' => 'add-images-form'
            ]) ?>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <input id="imageSearch" type="search" class="form-control me-3" placeholder="<?= __('Search images...') ?>" style="width: 300px;">
                    <span id="selected-count" class="text-muted"><?= __('No images selected') ?></span>
                </div>
                <div>
                    <?= $this->Form->button(__('Add Selected Images'), [
                        'class' => 'btn btn-primary',
                        'id' => 'submit-button',
                        'disabled' => true
                    ]) ?>
                    <?= $this->Html->link(__('Cancel'), 
                        ['controller' => 'ImageGalleries', 'action' => 'manageImages', $galleryId], 
                        ['class' => 'btn btn-secondary']
                    ) ?>
                </div>
            </div>
            
            <div id="ajax-target">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="50"><?= __('Select') ?></th>
                            <th><?= __('Image') ?></th>
                            <th><?= $this->Paginator->sort('name') ?></th>
                            <th><?= $this->Paginator->sort('modified') ?></th>
                            <th><?= $this->Paginator->sort('created') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($images as $image): ?>
                        <tr>
                            <td>
                                <?= $this->Form->checkbox('image_ids[]', [
                                    'value' => $image->id,
                                    'class' => 'image-checkbox form-check-input',
                                    'hiddenField' => false
                                ]) ?>
                            </td>
                            <td>
                                <div class="position-relative">
                                    <?= $this->element('image/icon', ['model' => $image, 'icon' => $image->teenyImageUrl, 'preview' => $image->extraLargeImageUrl]); ?>
                                </div>
                            </td>
                            <td><?= h($image->name) ?></td>
                            <td><?= h($image->created) ?></td>
                            <td><?= h($image->modified) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?= $this->element('pagination', ['recordCount' => count($images), 'search' => $search ?? '']) ?>
            </div>
            
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
console.log('Inline script loading...');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - picker.php inline script running');
    
    const searchInput = document.getElementById('imageSearch');
    const resultsContainer = document.querySelector('#ajax-target');
    const submitButton = document.getElementById('submit-button');
    const selectedCount = document.getElementById('selected-count');
    const form = document.getElementById('add-images-form');
    
    console.log('Elements found:', {
        searchInput: !!searchInput,
        resultsContainer: !!resultsContainer,
        submitButton: !!submitButton,
        selectedCount: !!selectedCount,
        form: !!form
    });
    
    let debounceTimer;

    // Update UI based on checkbox selections
    function updateUI() {
        const checked = document.querySelectorAll('.image-checkbox:checked');
        const count = checked.length;
        
        console.log('UpdateUI called, checked count:', count);
        
        if (submitButton) {
            submitButton.disabled = count === 0;
            console.log('Submit button disabled:', submitButton.disabled);
        }
        
        if (selectedCount) {
            if (count === 0) {
                selectedCount.textContent = 'No images selected';
            } else if (count === 1) {
                selectedCount.textContent = '1 image selected';
            } else {
                selectedCount.textContent = count + ' images selected';
            }
        }
    }
    
    // Add listeners to checkboxes
    function updateCheckboxListeners() {
        const checkboxes = document.querySelectorAll('.image-checkbox');
        console.log('Found checkboxes:', checkboxes.length);
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                console.log('Checkbox changed:', this.checked, this.value);
                updateUI();
            });
        });
    }
    
    // Simple form submission test
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            console.log('Submit button clicked!');
            console.log('Button disabled:', this.disabled);
            
            if (!this.disabled && form) {
                console.log('Submitting form...');
                form.submit();
            } else {
                console.log('Button disabled or no form found');
                e.preventDefault();
            }
        });
    }
    
    // Initialize
    updateCheckboxListeners();
    updateUI();
});
</script>