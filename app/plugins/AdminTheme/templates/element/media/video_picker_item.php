<?php
/**
 * Video picker item element
 * 
 * @var \App\View\AppView $this
 * @var array $item Video data from YouTube API
 * @var array $pickerOptions Picker configuration
 * @var string $viewType View type (grid|list)
 */

$item = $item ?? [];
$pickerOptions = $pickerOptions ?? [];
$viewType = $viewType ?? 'grid';

if (empty($item)) return;

$videoId = h($item['id'] ?? '');
$videoTitle = h($item['title'] ?? '');
$videoThumbnail = h($item['thumbnail'] ?? '');
$videoDescription = h($item['description'] ?? '');
?>

<?php if ($viewType === 'list'): ?>
    <div class="list-group-item list-group-item-action select-video" 
         data-video-id="<?= $videoId ?>"
         data-title="<?= $videoTitle ?>"
         style="cursor: pointer;">
        <div class="d-flex align-items-center">
            <div class="me-3 position-relative">
                <img src="<?= $videoThumbnail ?>" 
                     alt="<?= $videoTitle ?>"
                     style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                <div class="position-absolute top-50 start-50 translate-middle">
                    <i class="fas fa-play-circle fa-lg text-white"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1"><?= $this->Text->truncate($videoTitle, 60) ?></h6>
                <?php if ($videoDescription): ?>
                    <small class="text-muted"><?= $this->Text->truncate($videoDescription, 100) ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card h-100 video-picker-card">
        <div class="card-body p-2">
            <div class="position-relative mb-2 select-video"
                 data-video-id="<?= $videoId ?>"
                 data-title="<?= $videoTitle ?>"
                 style="cursor: pointer;">
                <img src="<?= $videoThumbnail ?>" 
                     alt="<?= $videoTitle ?>"
                     class="img-fluid"
                     style="width: 100%; height: 120px; object-fit: cover; border-radius: 4px;">
                
                <!-- Play button overlay -->
                <div class="position-absolute top-50 start-50 translate-middle">
                    <i class="fas fa-play-circle fa-2x text-white"></i>
                </div>
                
                <!-- YouTube logo overlay -->
                <div class="position-absolute bottom-0 end-0 m-1">
                    <span class="badge bg-danger">
                        <i class="fab fa-youtube"></i>
                    </span>
                </div>
            </div>
            
            <h6 class="card-title small mb-1"><?= $this->Text->truncate($videoTitle, 30) ?></h6>
            
            <?php if ($videoDescription): ?>
                <small class="text-muted"><?= $this->Text->truncate($videoDescription, 50) ?></small>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>