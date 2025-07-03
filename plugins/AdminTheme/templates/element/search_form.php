<?php
/**
 * Search Form Element
 * 
 * @var \App\View\AppView $this
 * @var string|null $searchValue Current search value
 * @var array $options Configuration options
 */

$defaults = [
    'id' => 'search-form',
    'inputId' => 'search-input', 
    'placeholder' => __('Search...'),
    'class' => 'd-flex me-3',
    'buttonClass' => 'btn btn-outline-secondary',
    'inputClass' => 'form-control',
    'showClearButton' => false,
];
$config = array_merge($defaults, $options ?? []);
$searchValue = $searchValue ?? $this->request->getQuery('search');
?>

<form class="<?= h($config['class']) ?>" id="<?= h($config['id']) ?>">
    <div class="input-group">
        <input class="<?= h($config['inputClass']) ?>" 
               type="search" 
               id="<?= h($config['inputId']) ?>" 
               name="search"
               placeholder="<?= h($config['placeholder']) ?>" 
               value="<?= h($searchValue) ?>">
        
        <?php if ($config['showClearButton'] && !empty($searchValue)): ?>
            <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                <i class="fas fa-times"></i>
            </button>
        <?php endif; ?>
        
        <button class="<?= h($config['buttonClass']) ?>" type="submit">
            <i class="fas fa-search"></i>
        </button>
    </div>
</form>

<?php if ($config['showClearButton']): ?>
<script>
function clearSearch() {
    document.getElementById('<?= h($config['inputId']) ?>').value = '';
    document.getElementById('<?= h($config['id']) ?>').dispatchEvent(new Event('submit'));
}
</script>
<?php endif; ?>