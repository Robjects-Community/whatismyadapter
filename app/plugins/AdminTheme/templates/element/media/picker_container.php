<?php
/**
 * Reusable media picker container element
 * 
 * @var \App\View\AppView $this
 * @var iterable $results Items to display in picker
 * @var string|null $search Current search term
 * @var string $mediaType Type of media (image, video, gallery)
 * @var array $pickerOptions Picker configuration options
 */

$results = $results ?? [];
$search = $search ?? '';
$mediaType = $mediaType ?? 'media';
$pickerOptions = $pickerOptions ?? [];

// Container ID for the specific media type
$containerId = $pickerOptions['containerId'] ?? $mediaType . '-gallery';
$viewType = $pickerOptions['viewType'] ?? 'grid';
$searchPlaceholder = $pickerOptions['searchPlaceholder'] ?? __('Search {0}...', $mediaType);
$emptyMessage = $pickerOptions['emptyMessage'] ?? __('No {0} found', $mediaType);
?>

<div class="modal-picker-container">
    <!-- Search Header -->
    <div class="picker-search-header p-3 border-bottom">
        <div class="row align-items-center">
            <div class="col">
                <form class="d-flex" role="search" onsubmit="return false;">
                    <div class="input-group">
                        <input type="search" 
                               class="form-control" 
                               id="<?= $mediaType ?>Search"
                               placeholder="<?= $searchPlaceholder ?>"
                               value="<?= h($search) ?>"
                               autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <?php if (isset($pickerOptions['additionalControls'])): ?>
            <div class="col-auto">
                <?php foreach ($pickerOptions['additionalControls'] as $control): ?>
                    <?= $this->element($control['element'], $control['data'] ?? []) ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Results Container -->
    <div class="picker-results-container" 
         id="<?= $containerId ?>" 
         style="max-height: 60vh; overflow-y: auto;">
        
        <?php if (empty($results)): ?>
            <div class="text-center p-5">
                <i class="fas fa-search fa-2x text-muted mb-3"></i>
                <p class="text-muted"><?= $emptyMessage ?></p>
                <?php if (empty($search)): ?>
                    <small class="text-muted"><?= __('Enter a search term to find {0}', $mediaType) ?></small>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if ($viewType === 'grid'): ?>
                <div class="row g-3 p-3">
                    <?php foreach ($results as $result): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <?= $this->element("media/{$mediaType}_picker_item", [
                            'item' => $result,
                            'pickerOptions' => $pickerOptions
                        ]) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($results as $result): ?>
                        <?= $this->element("media/{$mediaType}_picker_item", [
                            'item' => $result,
                            'pickerOptions' => $pickerOptions,
                            'viewType' => 'list'
                        ]) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Pagination -->
            <div class="p-3 border-top">
                <?= $this->element('pagination') ?>
            </div>
        <?php endif; ?>
    </div>
</div>