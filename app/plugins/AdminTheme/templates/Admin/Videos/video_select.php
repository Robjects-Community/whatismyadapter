<?php
/**
 * Video Picker - For selecting YouTube videos to insert into content
 * Modern layout with separated search form and results for smooth AJAX updates
 * 
 * @var \App\View\AppView $this
 * @var iterable $videos
 * @var string|null $searchTerm
 * @var bool|null $filterByChannel
 * @var string|null $channelId
 */

$videos = $videos ?? [];
$searchTerm = $searchTerm ?? '';
$filterByChannel = $filterByChannel ?? false;
$channelId = $channelId ?? 'your-api-key-here';
?>

<div class="willow-video-picker">
    <!-- Static Search Form (Never gets replaced via AJAX) -->
    <div class="willow-search-form p-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fab fa-youtube text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           id="videoSearch" 
                           placeholder="<?= __('Search YouTube videos by title, description...') ?>"
                           value="<?= h($searchTerm) ?>"
                           autocomplete="off">
                    <?php if ($searchTerm): ?>
                        <button class="btn btn-outline-secondary" type="button" id="clearVideoSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($channelId !== 'your-api-key-here'): ?>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="channelFilter" 
                           <?= $filterByChannel ? 'checked' : '' ?>>
                    <label class="form-check-label" for="channelFilter">
                        <?= __('Channel videos only') ?>
                    </label>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Search Stats -->
        <div class="row mt-2">
            <div class="col">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    <?= __('Search for YouTube videos to embed in your content') ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Dynamic Results Container (Gets replaced via AJAX) -->
    <div id="video-results" class="willow-results-container">
        <?php if (!empty($videos)): ?>
            <div class="row g-3 p-3">
                <?php foreach ($videos as $video): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card willow-picker-card h-100 shadow-sm">
                            <!-- Video Thumbnail -->
                            <div class="position-relative overflow-hidden" style="height: 200px; background: #000;">
                                <img src="<?= h($video['thumbnail']) ?>" 
                                     alt="<?= h($video['title']) ?>"
                                     class="img-fluid w-100 h-100"
                                     style="object-fit: cover;">
                                
                                <!-- Play button overlay -->
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <div class="bg-danger bg-opacity-90 rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fab fa-youtube fa-2x text-white"></i>
                                    </div>
                                </div>
                                
                                <!-- Duration overlay (if available) -->
                                <?php if (!empty($video['duration'])): ?>
                                <div class="position-absolute bottom-0 end-0 m-2">
                                    <span class="badge bg-dark bg-opacity-75 small">
                                        <?= h($video['duration']) ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Video Info -->
                            <div class="card-body p-3">
                                <h6 class="card-title mb-2" title="<?= h($video['title']) ?>">
                                    <?= h($this->Text->truncate($video['title'], 50)) ?>
                                </h6>
                                
                                <?php if (!empty($video['description'])): ?>
                                    <p class="card-text text-muted small mb-2">
                                        <?= h($this->Text->truncate($video['description'], 80, ['exact' => false])) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center text-muted small">
                                    <span>
                                        <i class="fab fa-youtube me-1"></i>
                                        YouTube
                                    </span>
                                    <?php if (!empty($video['publishedAt'])): ?>
                                        <span>
                                            <?= date('M j, Y', strtotime($video['publishedAt'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Selection Button -->
                            <div class="card-footer bg-transparent p-3">
                                <button type="button" 
                                        class="btn btn-primary w-100 select-video" 
                                        data-video-id="<?= h($video['id']) ?>"
                                        data-title="<?= h($video['title']) ?>">
                                    <i class="fas fa-plus me-2"></i>
                                    <?= __('Insert Video') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Beautiful Empty State -->
            <div class="willow-empty-state p-5">
                <div class="text-center">
                    <?php if ($searchTerm): ?>
                        <i class="fab fa-youtube fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted mb-2"><?= __('No videos found') ?></h5>
                        <p class="text-muted mb-3">
                            <?= __('No videos match "{0}"', h($searchTerm)) ?>
                        </p>
                        <button type="button" class="btn btn-outline-primary" id="clearVideoSearchBtn">
                            <i class="fas fa-times me-2"></i>
                            <?= __('Clear Search') ?>
                        </button>
                    <?php else: ?>
                        <i class="fab fa-youtube fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted mb-2"><?= __('Search for videos') ?></h5>
                        <p class="text-muted">
                            <?= __('Enter a search term to find YouTube videos to embed in your content.') ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Enhanced video picker interactions
document.addEventListener('DOMContentLoaded', function() {
    // Clear search functionality
    const clearVideoSearchBtn = document.getElementById('clearVideoSearchBtn');
    if (clearVideoSearchBtn) {
        clearVideoSearchBtn.addEventListener('click', function() {
            const searchInput = document.getElementById('videoSearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                searchInput.focus();
            }
        });
    }
    
    // Clear search button in search form
    const clearVideoSearch = document.getElementById('clearVideoSearch');
    if (clearVideoSearch) {
        clearVideoSearch.addEventListener('click', function() {
            const searchInput = document.getElementById('videoSearch');
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                searchInput.focus();
            }
        });
    }
    
    // Channel filter functionality
    const channelFilter = document.getElementById('channelFilter');
    if (channelFilter) {
        channelFilter.addEventListener('change', function() {
            const searchInput = document.getElementById('videoSearch');
            if (searchInput) {
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }
});
</script>