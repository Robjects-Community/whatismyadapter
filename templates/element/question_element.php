<?php
/**
 * Adapter Picker Element
 * @var \App\View\AppView $this
 */
?>
<!-- In your view file (e.g., templates/Adapters/index.php) -->
<div class="container mt-4">
    <div class="row">
        <?php foreach ($options as $option): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?= h($option['title']) ?></h5>
                        <p class="card-text"><?= h($option['description']) ?></p>
                        <a href="#" class="btn btn-primary">Learn More</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
