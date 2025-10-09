<?php
/**
 * Funding Banner Element
 * 
 * This element displays a funding/sponsorship banner for robjects-community
 * 
 * Usage in templates:
 * <?= $this->element('funding_banner', [
 *     'current_amount' => 8226,
 *     'goal_amount' => 12000,
 *     'campaign_name' => 'Monthly Goal',
 *     'sponsor_url' => 'https://github.com/sponsors/robjects-community',
 *     'api_endpoint' => null, // Optional: API URL for dynamic data
 *     'position' => 'top' // 'top' or 'bottom'
 * ]) ?>
 * 
 * @var \App\View\AppView $this
 * @var int $current_amount Current funding amount (default from settings)
 * @var int $goal_amount Goal amount (default from settings)
 * @var string $campaign_name Campaign name (e.g., "Monthly Goal", "Annual Campaign")
 * @var string $sponsor_url URL for the sponsor/donate button
 * @var string|null $api_endpoint Optional API endpoint for dynamic updates
 * @var string $position Banner position ('top' or 'bottom')
 */

use App\Utility\SettingsManager;

// Get settings or use defaults/passed parameters
$current_amount = $current_amount ?? SettingsManager::read('Funding.currentAmount', 8226);
$goal_amount = $goal_amount ?? SettingsManager::read('Funding.goalAmount', 12000);
$campaign_name = $campaign_name ?? SettingsManager::read('Funding.campaignName', 'Monthly Goal');
$sponsor_url = $sponsor_url ?? SettingsManager::read('Funding.sponsorUrl', 'https://github.com/sponsors/robjects-community');
$api_endpoint = $api_endpoint ?? SettingsManager::read('Funding.apiEndpoint', null);
$cta_text = $cta_text ?? SettingsManager::read('Funding.ctaText', 'Help us reach our goal!');
$button_text = $button_text ?? SettingsManager::read('Funding.buttonText', 'Sponsor robjects-community');
$position = $position ?? 'top';

// Check if banner is enabled
$banner_enabled = SettingsManager::read('Funding.enabled', true);
if (!$banner_enabled) {
    return;
}

// Calculate percentage
$percentage = min(($current_amount / $goal_amount) * 100, 100);
$percentage_display = number_format($percentage, 0);

// Format currency
$current_display = '$' . number_format($current_amount, 0);
$goal_display = '$' . number_format($goal_amount, 0);

// Generate unique ID for this instance
$banner_id = 'funding-banner-' . uniqid();
?>

<!-- Funding Banner for robjects-community -->
<div class="robjects-funding-banner <?= h($position) ?>" id="<?= h($banner_id) ?>" data-api-endpoint="<?= h($api_endpoint) ?>">
    <div class="robjects-funding-banner__container">
        <div class="robjects-funding-banner__content">
            <!-- Desktop Layout -->
            <div class="robjects-funding-banner__desktop-view">
                <span class="robjects-funding-banner__amount" data-field="raised">
                    <?= __('Raised: {0}', $current_display) ?>
                </span>
                
                <div class="robjects-funding-banner__progress-section">
                    <div class="robjects-funding-banner__progress-header">
                        <span data-field="percent"><?= __('%s%% of %s', $percentage_display, $campaign_name) ?></span>
                        <span class="robjects-funding-banner__cta"><?= h($cta_text) ?></span>
                    </div>
                    <div class="robjects-funding-banner__progress-bar">
                        <div class="robjects-funding-banner__progress-fill" 
                             data-field="progress" 
                             style="width: <?= h($percentage_display) ?>%;"
                             aria-valuenow="<?= h($percentage_display) ?>"
                             aria-valuemin="0"
                             aria-valuemax="100"></div>
                    </div>
                </div>
                
                <span class="robjects-funding-banner__amount" data-field="goal">
                    <?= __('Goal: {0}', $goal_display) ?>
                </span>
            </div>
            
            <!-- Mobile Layout -->
            <div class="robjects-funding-banner__mobile-view">
                <div class="robjects-funding-banner__amounts-row">
                    <span class="robjects-funding-banner__amount" data-field="raised-mobile">
                        <?= __('Raised: {0}', $current_display) ?>
                    </span>
                    <span class="robjects-funding-banner__amount" data-field="goal-mobile">
                        <?= __('Goal: {0}', $goal_display) ?>
                    </span>
                </div>
                <div class="robjects-funding-banner__progress-bar">
                    <div class="robjects-funding-banner__progress-fill" 
                         data-field="progress-mobile" 
                         style="width: <?= h($percentage_display) ?>%;"
                         aria-valuenow="<?= h($percentage_display) ?>"
                         aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>
                <div class="robjects-funding-banner__meta-row">
                    <span data-field="percent-mobile"><?= __('%s%% of %s', $percentage_display, $campaign_name) ?></span>
                    <span class="robjects-funding-banner__cta"><?= h($cta_text) ?></span>
                </div>
            </div>
        </div>
        
        <?= $this->Html->link(
            h($button_text),
            $sponsor_url,
            [
                'class' => 'robjects-funding-banner__button',
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
                'escape' => false
            ]
        ) ?>
    </div>
</div>

<?php if ($api_endpoint): ?>
<script>
// Initialize funding banner with API endpoint for this specific instance
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initializeFundingBanner === 'function') {
        initializeFundingBanner('<?= h($banner_id) ?>', {
            apiEndpoint: '<?= h($api_endpoint) ?>',
            currentAmount: <?= json_encode($current_amount) ?>,
            goalAmount: <?= json_encode($goal_amount) ?>,
            campaignName: <?= json_encode($campaign_name) ?>
        });
    }
});
</script>
<?php endif; ?>