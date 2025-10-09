<?php
// Handle null or empty icon values by falling back to original image URL
$imageUrl = $icon ?? ($model->image ?? null);

if (!empty($model->image) && $imageUrl): ?>
    <?php 
    // Base image options
    $imageOptions = [
        'pathPrefix' => '', 
        'alt' => $model->alt_text ?? '', 
        'class' => 'img-thumbnail'
    ];
    
    // Only add popover options if preview is provided
    if (isset($preview) && $preview !== false) {
        $imageOptions = array_merge($imageOptions, [
            'data-bs-toggle' => 'popover',
            'data-bs-trigger' => 'hover',
            'data-bs-html' => 'true',
            'data-bs-content' => $this->Html->image(
                $preview, 
                [
                    'pathPrefix' => '', 
                    'alt' => $model->alt_text ?? '', 
                    'class' => 'img-fluid', 
                ]
            )
        ]);
    }
    ?>
    <?= $this->Html->image($imageUrl, $imageOptions) ?>
<?php endif; ?>