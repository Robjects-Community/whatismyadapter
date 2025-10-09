<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div>
        <?= h($message) ?>
    </div>
    <?php if (!empty($params['errors'])): ?>
        <div>
            <ul>
                <?php foreach ($params['errors'] as $error): ?>
                    <li><?= h($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>