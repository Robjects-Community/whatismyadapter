<h1>Featured Adapters</h1>
<?php foreach ($adapters as $adapter): ?>
    <div class="adapter-card">
        <h2><?= h($adapter->title) ?></h2>
        <img src="<?= h($adapter->image) ?>" alt="<?= h($adapter->alt_text) ?>">
        <p><strong>Connectors:</strong> <?= h($adapter->connector_type_a) ?> to <?= h($adapter->connector_type_b) ?></p>
        <p><strong>Max Power:</strong> <?= h($adapter->max_power_delivery) ?></p>
        <p><?= h($adapter->description) ?></p>
        <a href="<?= h($adapter->shopping_link) ?>">Buy Now - $<?= $adapter->price ?></a>
    </div>
<?php endforeach; ?>
