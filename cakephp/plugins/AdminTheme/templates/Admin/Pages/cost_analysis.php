<?php
/**
 * Cost Analysis Template for Admin Pages - AdminTheme Plugin
 * @var \App\View\AppView $this
 * @var array $platforms
 * @var array $aiCosts
 * @var array $insights
 * @var array $timeline
 */

$this->assign('title', 'Server Deployment Cost Analysis');
$this->assign('page_title', 'Cost Analysis');

// Add page-specific CSS
$this->Html->css(['cost-analysis'], ['block' => true]);
?>

<div class="cost-analysis-page admin-content">
    <!-- Path Selection Cards -->
    <div class="path-selection-section mb-5" id="pathSelection">
        <div class="section-header">
            <h2><i class="fas fa-route"></i> <?= __('Choose Your Deployment Path') ?></h2>
            <p class="section-description">
                <?= __('Select how you want to deploy your Willow CMS project. Choose from linking an existing domain/URL or creating a complete new deployment.') ?>
            </p>
        </div>
        
        <div class="path-cards-grid">
            <div class="path-card" data-path="existing-url">
                <div class="path-icon">
                    <i class="fas fa-link"></i>
                </div>
                <h3><?= __('Link Existing URL/Domain') ?></h3>
                <p><?= __('Connect to an existing website, domain, or PHP application that you already have running.') ?></p>
                <ul class="path-features">
                    <li><i class="fas fa-check"></i> <?= __('Connect to live domain') ?></li>
                    <li><i class="fas fa-check"></i> <?= __('Validate existing setup') ?></li>
                    <li><i class="fas fa-check"></i> <?= __('Cost analysis for current infrastructure') ?></li>
                </ul>
                <button class="btn btn-primary btn-select-path" data-target="existing-url">
                    <?= __('Choose This Path') ?>
                </button>
            </div>
            
            <div class="path-card" data-path="new-deployment">
                <div class="path-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h3><?= __('Create New Deployment') ?></h3>
                <p><?= __('Build a fresh deployment from scratch with custom files, configurations, and documentation.') ?></p>
                <ul class="path-features">
                    <li><i class="fas fa-check"></i> <?= __('Upload custom files (HTML, CSS, JS)') ?></li>
                    <li><i class="fas fa-check"></i> <?= __('Generate deployment documentation') ?></li>
                    <li><i class="fas fa-check"></i> <?= __('Complete platform recommendation') ?></li>
                </ul>
                <button class="btn btn-success btn-select-path" data-target="new-deployment">
                    <?= __('Choose This Path') ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Dynamic Form Section -->
    <div class="form-section" id="dynamicForm" style="display: none;">
        <!-- Forms will be injected here based on path selection -->
    </div>

    <!-- Page Header -->
    <div class="page-header mb-4" id="costAnalysisHeader" style="display: none;">
        <div class="header-content">
            <div class="title-section">
                <i class="fas fa-chart-line header-icon"></i>
                <h1><?= __('Server Deployment Cost Analysis') ?></h1>
                <p class="subtitle"><?= __('10-Year Infrastructure Investment Comparison for Willow CMS') ?></p>
            </div>
            <div class="summary-stats">
                <div class="stat-card primary">
                    <span class="stat-number"><?= count($platforms) ?></span>
                    <span class="stat-label"><?= __('Platforms Analyzed') ?></span>
                </div>
                <div class="stat-card success">
                    <span class="stat-number">$<?= number_format(min(array_column($platforms, 'ten_year_cost'))) ?></span>
                    <span class="stat-label"><?= __('Minimum 10-Year Cost') ?></span>
                </div>
                <div class="stat-card warning">
                    <span class="stat-number">$<?= number_format($aiCosts['estimated_yearly']) ?></span>
                    <span class="stat-label"><?= __('Estimated AI Costs/Year') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Comparison Grid -->
    <div class="platforms-section mb-5">
        <h2><i class="fas fa-balance-scale"></i> <?= __('Platform Comparison') ?></h2>
        <p class="section-description">
            <?= __('Comprehensive analysis of deployment platforms for a 10-year CakePHP server project with AI integration.') ?>
            <strong><?= __('Note:') ?></strong> <?= __('Infrastructure costs are minimal compared to AI API usage (~$3,000/year).') ?>
        </p>

        <div class="platforms-grid">
            <?php foreach ($platforms as $platform): ?>
                <div class="platform-card <?= $platform['color_class'] ?> <?= !empty($platform['recommended']) ? 'recommended' : '' ?>">
                    <?php if (!empty($platform['recommended'])): ?>
                        <div class="recommended-badge">
                            <i class="fas fa-star"></i> <?= __('RECOMMENDED') ?>
                        </div>
                    <?php endif; ?>

                    <div class="platform-header">
                        <i class="<?= $platform['icon'] ?> platform-icon"></i>
                        <h3><?= h($platform['name']) ?></h3>
                        <span class="category-badge <?= $platform['category'] ?>"><?= __(ucwords(str_replace('-', ' ', $platform['category']))) ?></span>
                    </div>

                    <div class="cost-display">
                        <div class="cost-item">
                            <span class="cost-label"><?= __('Monthly:') ?></span>
                            <span class="cost-value"><?= $platform['monthly_cost'] == 0 ? __('FREE') : '$' . number_format($platform['monthly_cost']) ?></span>
                        </div>
                        <div class="cost-item">
                            <span class="cost-label"><?= __('Yearly:') ?></span>
                            <span class="cost-value"><?= $platform['yearly_cost'] == 0 ? __('FREE') : '$' . number_format($platform['yearly_cost']) ?></span>
                        </div>
                        <div class="cost-item total">
                            <span class="cost-label"><?= __('10-Year Total:') ?></span>
                            <span class="cost-value"><?= $platform['ten_year_cost'] == 0 ? __('FREE') : '$' . number_format($platform['ten_year_cost']) ?></span>
                        </div>
                    </div>

                    <div class="platform-details">
                        <div class="detail-row">
                            <i class="fas fa-layer-group"></i>
                            <span><strong><?= __('Difficulty:') ?></strong> <?= h($platform['difficulty']) ?></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-user-graduate"></i>
                            <span><strong><?= __('Experience:') ?></strong> <?= h($platform['experience_needed']) ?></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span><strong><?= __('Scaling:') ?></strong> <?= h($platform['scalability']) ?></span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-bullseye"></i>
                            <span><strong><?= __('Best For:') ?></strong> <?= h($platform['best_for']) ?></span>
                        </div>
                    </div>

                    <div class="pros-cons">
                        <div class="pros">
                            <h4><i class="fas fa-check-circle"></i> <?= __('Pros') ?></h4>
                            <ul>
                                <?php foreach ($platform['pros'] as $pro): ?>
                                    <li><?= h($pro) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="cons">
                            <h4><i class="fas fa-times-circle"></i> <?= __('Cons') ?></h4>
                            <ul>
                                <?php foreach ($platform['cons'] as $con): ?>
                                    <li><?= h($con) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- AI Cost Impact Section -->
    <div class="ai-costs-section mb-5">
        <h2><i class="fas fa-robot"></i> <?= __('AI Integration Cost Impact') ?></h2>
        <div class="ai-cost-grid">
            <div class="ai-cost-card primary">
                <i class="fas fa-brain"></i>
                <h3><?= __('Anthropic Claude API') ?></h3>
                <div class="cost-breakdown">
                    <div class="cost-line">
                        <span><?= __('Rate:') ?></span>
                        <span class="cost-value">$<?= $aiCosts['anthropic_claude'] ?>/1M characters</span>
                    </div>
                    <div class="cost-line">
                        <span><?= __('Estimated Monthly:') ?></span>
                        <span class="cost-value">$<?= number_format($aiCosts['estimated_monthly']) ?></span>
                    </div>
                    <div class="cost-line total">
                        <span><?= __('Yearly Estimate:') ?></span>
                        <span class="cost-value">$<?= number_format($aiCosts['estimated_yearly']) ?></span>
                    </div>
                </div>
            </div>

            <div class="comparison-card">
                <h3><i class="fas fa-calculator"></i> <?= __('Cost Reality Check') ?></h3>
                <div class="comparison-item">
                    <span class="comparison-label"><?= __('Most expensive infrastructure (10 years):') ?></span>
                    <span class="comparison-value">$<?= number_format(max(array_column($platforms, 'ten_year_cost'))) ?></span>
                </div>
                <div class="comparison-item">
                    <span class="comparison-label"><?= __('AI costs (10 years est.):') ?></span>
                    <span class="comparison-value">$<?= number_format($aiCosts['estimated_yearly'] * 10) ?></span>
                </div>
                <div class="comparison-item highlight">
                    <span class="comparison-label"><?= __('AI costs dominate by:') ?></span>
                    <span class="comparison-value"><?= round(($aiCosts['estimated_yearly'] * 10) / max(array_column($platforms, 'ten_year_cost')), 1) ?>x</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Insights Section -->
    <div class="insights-section mb-5">
        <h2><i class="fas fa-lightbulb"></i> <?= __('Key Insights & Recommendations') ?></h2>
        <div class="insights-grid">
            <?php foreach ($insights as $index => $insight): ?>
                <div class="insight-card">
                    <div class="insight-number"><?= $index + 1 ?></div>
                    <div class="insight-content">
                        <p><?= h($insight) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="recommendation-banner">
            <div class="recommendation-content">
                <i class="fas fa-trophy"></i>
                <div>
                    <h3><?= __('Final Recommendation') ?></h3>
                    <p><?= __('Start with a <strong>$7/month DigitalOcean Droplet</strong> for demos and early production. Add Docker Compose ($8/month total) when you need multi-container setup. Most projects never need Kubernetes - focus your budget on AI prompt efficiency instead of infrastructure costs.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Development Path Timeline -->
    <div class="timeline-section">
        <h2><i class="fas fa-road"></i> <?= __('Recommended Development Path') ?></h2>
        <div class="development-timeline">
            <?php $phaseCount = 1; ?>
            <?php foreach ($timeline as $phase): ?>
                <div class="timeline-item">
                    <div class="timeline-marker phase-<?= $phaseCount ?>"></div>
                    <div class="timeline-content">
                        <h4><?= h($phase['title']) ?></h4>
                        <p><?= h($phase['description']) ?></p>
                        <?php if (!empty($phase['recommendation'])): ?>
                            <div class="phase-recommendation">
                                <i class="fas fa-lightbulb"></i>
                                <strong><?= __('Recommendation:') ?></strong> <?= h($phase['recommendation']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="phase-cost">
                            <?= __('Total Cost: ~$') ?><?= number_format($phase['cost_range'][0]) ?>
                            <?php if ($phase['cost_range'][0] !== $phase['cost_range'][1]): ?>
                                - $<?= number_format($phase['cost_range'][1]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php $phaseCount++; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php $this->start('css'); ?>
<style>
/* Cost Analysis Page Styles using Bootstrap 5.3+ CSS variables for theme support */
.cost-analysis-page {
    font-family: var(--bs-font-sans-serif);
    background: var(--bs-body-bg);
    color: var(--bs-body-color);
    min-height: 100vh;
    padding: 2rem;
}

/* Page Header */
.page-header {
    background: var(--bs-body-bg);
    border-radius: .5rem;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--bs-border-color);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.header-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--space-32, 2rem);
    align-items: center;
}

.title-section {
    text-align: left;
}

.header-icon {
    font-size: 3rem;
    color: var(--bs-primary);
    margin-bottom: 1rem;
}

.title-section h1 {
    font-size: 2.5rem;
    margin-bottom: var(--space-12, 0.75rem);
    color: var(--bs-body-color);
    background: linear-gradient(135deg, var(--color-primary, #007bff), var(--color-info, #17a2b8));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.subtitle {
    font-size: 1.125rem;
    color: var(--bs-secondary-color);
    font-style: italic;
    margin: 0;
}

/* Summary Stats */
.summary-stats {
    display: flex;
    flex-direction: column;
    gap: var(--space-16, 1rem);
}

.stat-card {
    background: var(--bs-body-bg);
    padding: 1rem;
    border-radius: .375rem;
    border: 1px solid var(--bs-border-color);
    text-align: center;
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-card.primary {
    border: 2px solid var(--bs-primary);
    background: color-mix(in oklab, var(--bs-primary), transparent 92%);
}

.stat-card.success {
    border: 2px solid var(--bs-success);
    background: color-mix(in oklab, var(--bs-success), transparent 92%);
}

.stat-card.warning {
    border: 2px solid var(--bs-warning);
    background: color-mix(in oklab, var(--bs-warning), transparent 92%);
}

.stat-number {
    display: block;
    font-size: 1.75rem;
    font-weight: bold;
    color: var(--bs-primary);
    line-height: 1;
}

.stat-card.success .stat-number {
    color: var(--bs-success);
}

.stat-card.warning .stat-number {
    color: var(--bs-warning);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--color-text-secondary, #6c757d);
    margin-top: 0.25rem;
    display: block;
}

/* Section Headers */
.cost-analysis-page h2 {
    display: flex;
    align-items: center;
    gap: var(--space-12, 0.75rem);
    margin-bottom: var(--space-16, 1rem);
    font-size: 2rem;
    color: var(--bs-body-color);
}

.section-description {
    color: var(--bs-secondary-color);
    font-size: 1.125rem;
    margin-bottom: var(--space-32, 2rem);
    line-height: 1.6;
}

/* Platform Cards Grid */
.platforms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: var(--space-24, 1.5rem);
    margin-bottom: var(--space-32, 2rem);
}

.platform-card {
    background: var(--bs-body-bg);
    border-radius: .5rem;
    padding: 1.5rem;
    border: 1px solid var(--bs-border-color);
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.platform-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.platform-card.primary { border: 2px solid var(--bs-primary); background: color-mix(in oklab, var(--bs-primary), transparent 92%); }
.platform-card.success { border: 2px solid var(--bs-success); background: color-mix(in oklab, var(--bs-success), transparent 92%); }
.platform-card.info    { border: 2px solid var(--bs-info);    background: color-mix(in oklab, var(--bs-info), transparent 92%); }
.platform-card.warning { border: 2px solid var(--bs-warning); background: color-mix(in oklab, var(--bs-warning), transparent 92%); }
.platform-card.error   { border: 2px solid var(--bs-danger);  background: color-mix(in oklab, var(--bs-danger), transparent 92%); }
.platform-card.recommended { border: 3px solid var(--bs-success); background: color-mix(in oklab, var(--bs-success), transparent 90%); }

/* Recommended Badge */
.recommended-badge {
    position: absolute;
    top: -12px;
    right: var(--space-16, 1rem);
    background: var(--color-success, #28a745);
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Platform Header */
.platform-header {
    display: flex;
    align-items: center;
    gap: var(--space-12, 0.75rem);
    margin-bottom: var(--space-20, 1.25rem);
    flex-wrap: wrap;
}

.platform-icon {
    font-size: 2.5rem;
    color: var(--bs-primary);
}

.platform-card.success .platform-icon {
    color: var(--bs-success);
}

.platform-card.warning .platform-icon {
    color: var(--bs-warning);
}

.platform-card.error .platform-icon {
    color: var(--bs-danger);
}

.platform-card.info .platform-icon {
    color: var(--bs-info);
}

.platform-header h3 {
    flex: 1;
    margin: 0;
    font-size: 1.25rem;
    color: var(--bs-body-color);
}

.category-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    background: var(--bs-secondary);
    color: var(--bs-body-bg);
}

.category-badge.zero-cost { background: var(--bs-success); }
.category-badge.low-cost { background: var(--bs-primary); }
.category-badge.moderate-cost { background: var(--bs-warning); }
.category-badge.expensive { background: var(--bs-danger); }

/* Cost Display */
.cost-display {
    background: color-mix(in oklab, var(--bs-primary), transparent 95%);
    border-radius: 6px;
    padding: var(--space-16, 1rem);
    margin-bottom: var(--space-20, 1.25rem);
}

.cost-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--bs-border-color-translucent);
}

.cost-item:last-child {
    border-bottom: none;
}

.cost-item.total {
    font-weight: bold;
    font-size: 1.125rem;
    color: var(--bs-primary);
    border-top: 2px solid var(--bs-primary);
    margin-top: 0.5rem;
    padding-top: 0.75rem;
}

.cost-label {
    color: var(--bs-secondary-color);
}

.cost-value {
    font-weight: 600;
    color: var(--bs-body-color);
}

/* Platform Details */
.platform-details {
    margin-bottom: var(--space-20, 1.25rem);
}

.detail-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.detail-row i {
    color: var(--bs-primary);
    width: 16px;
    text-align: center;
}

/* Pros and Cons */
.pros-cons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16, 1rem);
}

.pros, .cons {
    font-size: 0.875rem;
}

.pros h4, .cons h4 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.pros h4 {
    color: var(--bs-success);
}

.cons h4 {
    color: var(--bs-danger);
}

.pros ul, .cons ul {
    margin: 0;
    padding-left: 1rem;
    list-style: none;
}

.pros li, .cons li {
    margin-bottom: 0.25rem;
    padding-left: 1rem;
    position: relative;
    line-height: 1.4;
}

.pros li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: var(--color-success, #28a745);
    font-weight: bold;
}

.cons li:before {
    content: "✗";
    position: absolute;
    left: 0;
    color: var(--color-danger, #dc3545);
    font-weight: bold;
}

/* AI Costs Section */
.ai-costs-section {
    background: var(--bs-body-bg);
    border-radius: .5rem;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--bs-border-color);
}

.ai-cost-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-24, 1.5rem);
    margin-top: var(--space-24, 1.5rem);
}

.ai-cost-card {
    background: var(--bs-body-bg);
    border-radius: .5rem;
    padding: 1.5rem;
    border: 2px solid var(--bs-primary);
    text-align: center;
}

.ai-cost-card i {
    font-size: 3rem;
    color: var(--bs-primary);
    margin-bottom: 1rem;
}

.cost-breakdown {
    text-align: left;
    margin-top: var(--space-16, 1rem);
}

.cost-line {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--bs-border-color);
}

.cost-line.total {
    font-weight: bold;
    color: var(--bs-primary);
    border-top: 2px solid var(--bs-primary);
    margin-top: 0.5rem;
}

/* Comparison Card */
.comparison-card {
    background: var(--bs-body-bg);
    border-radius: var(--radius-lg, 8px);
    padding: var(--space-24, 1.5rem);
    border: 1px solid var(--bs-border-color);
}

.comparison-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--color-border, #dee2e6);
}

.comparison-item.highlight {
    background: rgba(255, 193, 7, 0.1);
    padding: 0.75rem;
    border-radius: 4px;
    margin-top: 0.5rem;
    border: 1px solid var(--color-warning, #ffc107);
    color: #856404;
    font-weight: bold;
}

/* Insights Section */
.insights-section {
    background: var(--bs-body-bg);
    border-radius: var(--radius-lg, 8px);
    padding: var(--space-32, 2rem);
    margin-bottom: var(--space-32, 2rem);
    border: 1px solid var(--bs-border-color);
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--space-16, 1rem);
    margin-bottom: var(--space-32, 2rem);
}

.insight-card {
    background: var(--bs-body-bg);
    border-radius: var(--radius-lg, 8px);
    padding: var(--space-20, 1.25rem);
    border: 1px solid var(--bs-border-color);
    display: flex;
    align-items: flex-start;
    gap: var(--space-16, 1rem);
}

.insight-number {
    background: var(--bs-primary);
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.insight-content p {
    margin: 0;
    line-height: 1.6;
}

/* Recommendation Banner */
.recommendation-banner {
    background: var(--bs-body-bg);
    border: 2px solid var(--bs-success);
    border-radius: var(--radius-lg, 8px);
    padding: var(--space-24, 1.5rem);
}

.recommendation-content {
    display: flex;
    align-items: flex-start;
    gap: var(--space-16, 1rem);
}

.recommendation-content i {
    font-size: 2rem;
    color: var(--bs-success);
    margin-top: 0.25rem;
}

.recommendation-content h3 {
    color: var(--color-success, #28a745);
    margin-bottom: 0.5rem;
}

/* Timeline Section */
.timeline-section {
    background: var(--bs-body-bg);
    border-radius: var(--radius-lg, 8px);
    padding: var(--space-32, 2rem);
    border: 1px solid var(--bs-border-color);
}

.development-timeline {
    position: relative;
    margin-top: var(--space-32, 2rem);
}

.development-timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--color-border, #dee2e6);
}

.timeline-item {
    position: relative;
    margin-bottom: var(--space-32, 2rem);
    padding-left: 4rem;
}

.timeline-marker {
    position: absolute;
    left: 12px;
    top: 0.5rem;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid var(--bs-border-color);
    background: var(--bs-body-bg);
}

.timeline-marker.phase-1 {
    background: var(--bs-success);
    border-color: var(--bs-success);
}

.timeline-marker.phase-2 {
    background: var(--bs-warning);
    border-color: var(--bs-warning);
}

.timeline-marker.phase-3 {
    background: var(--bs-primary);
    border-color: var(--bs-primary);
}

.timeline-content {
    background: color-mix(in oklab, var(--bs-primary), transparent 95%);
    border-radius: .375rem;
    padding: 1.25rem;
    border: 1px solid var(--bs-border-color);
}

.timeline-content h4 {
    color: var(--bs-primary);
    margin-bottom: 0.75rem;
}

.phase-recommendation {
    background: color-mix(in oklab, var(--bs-info), transparent 90%);
    color: var(--bs-info);
    padding: 0.75rem;
    border-radius: .375rem;
    margin: 0.75rem 0;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.9rem;
    border-left: 4px solid var(--bs-info);
}

.phase-recommendation i {
    color: var(--bs-info);
    margin-top: 0.1rem;
    flex-shrink: 0;
}

.phase-cost {
    background: color-mix(in oklab, var(--bs-primary), transparent 90%);
    color: var(--bs-primary);
    padding: 0.5rem;
    border-radius: .375rem;
    margin-top: 0.75rem;
    font-weight: 600;
    text-align: center;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .platforms-grid {
        grid-template-columns: 1fr;
    }

    .header-content {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .ai-cost-grid,
    .pros-cons {
        grid-template-columns: 1fr;
    }

    .insights-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .cost-analysis-page {
        padding: var(--space-16, 1rem);
    }

    .summary-stats {
        flex-direction: column;
    }

    .platform-header {
        flex-direction: column;
        text-align: center;
    }

    .cost-display {
        font-size: 0.875rem;
    }

    .recommendation-content {
        flex-direction: column;
        text-align: center;
    }

    .timeline-item {
        padding-left: 2rem;
    }

    .development-timeline::before {
        left: 8px;
    }

    .timeline-marker {
        left: 0;
    }
}

/* Path Selection Styles */
.path-selection-section {
    background: var(--bs-body-bg);
    border-radius: .5rem;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--bs-border-color);
}

.section-header {
    text-align: center;
    margin-bottom: 2rem;
}

.section-header h2 {
    color: var(--bs-body-color);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.path-cards-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    max-width: 900px;
    margin: 0 auto;
}

.path-card {
    background: var(--bs-body-bg);
    border: 2px solid var(--bs-border-color);
    border-radius: .75rem;
    padding: 2rem;
    text-align: center;
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transform: translateY(0px) scale(1);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04), 0 2px 4px -1px rgba(0,0,0,0.02);
}

.path-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1), 0 8px 16px rgba(0,0,0,0.08);
    border-color: var(--bs-primary);
}

.path-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.8s ease;
    z-index: 1;
}

.path-card:hover::before {
    left: 100%;
}

.path-card[data-path="existing-url"]:hover {
    border-color: var(--bs-primary);
    background: color-mix(in oklab, var(--bs-primary), transparent 95%);
}

.path-card[data-path="new-deployment"]:hover {
    border-color: var(--bs-success);
    background: color-mix(in oklab, var(--bs-success), transparent 95%);
}

.path-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    background: var(--bs-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    z-index: 2;
}

.path-card[data-path="new-deployment"] .path-icon {
    background: var(--bs-success);
}

.path-card:hover .path-icon {
    transform: scale(1.1) rotateY(15deg);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.path-card.selected {
    border-color: var(--bs-success);
    background: color-mix(in oklab, var(--bs-success), transparent 95%);
    transform: translateY(-4px) scale(1.02);
}

.path-card.selected .path-icon {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.path-card h3 {
    color: var(--bs-body-color);
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.path-card p {
    color: var(--bs-secondary-color);
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.path-features {
    list-style: none;
    padding: 0;
    margin-bottom: 2rem;
    text-align: left;
}

.path-features li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    color: var(--bs-body-color);
}

.path-features i {
    color: var(--bs-success);
    font-size: 0.875rem;
}

.btn-select-path {
    width: 100%;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: .5rem;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
    z-index: 2;
}

.btn-select-path::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
}

.btn-select-path:active::after {
    width: 300px;
    height: 300px;
}

/* Form Section */
.form-section {
    background: var(--bs-body-bg);
    border-radius: .5rem;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--bs-border-color);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: var(--bs-body-color);
    margin-bottom: 0.5rem;
    display: block;
}

.form-control {
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    color: var(--bs-body-color);
    border-radius: .375rem;
    padding: 0.75rem 1rem;
    width: 100%;
}

.form-control:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem color-mix(in oklab, var(--bs-primary), transparent 75%);
}

.file-upload-area {
    border: 2px dashed var(--bs-border-color);
    border-radius: .5rem;
    padding: 2rem;
    text-align: center;
    background: color-mix(in oklab, var(--bs-primary), transparent 97%);
    transition: all 0.3s ease;
}

.file-upload-area:hover {
    border-color: var(--bs-primary);
    background: color-mix(in oklab, var(--bs-primary), transparent 93%);
}

.file-upload-area.dragover {
    border-color: var(--bs-success);
    background: color-mix(in oklab, var(--bs-success), transparent 90%);
}

.file-list {
    margin-top: 1rem;
    padding: 0;
    list-style: none;
}

.file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: .375rem;
    margin-bottom: 0.5rem;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.file-icon {
    color: var(--bs-primary);
}

.file-remove {
    background: none;
    border: none;
    color: var(--bs-danger);
    cursor: pointer;
    padding: 0.25rem;
}

/* Cost Preview Mini-Chart */
.cost-preview {
    position: absolute;
    bottom: -120px;
    left: 1rem;
    right: 1rem;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: .5rem;
    padding: 1rem;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 10;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.mini-chart {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.mini-chart .chart-bar {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    opacity: 0;
    transform: translateX(-20px);
    animation: slideInChart 0.5s ease forwards;
}

.mini-chart .bar-fill {
    width: 4px;
    height: 16px;
    background: linear-gradient(45deg, var(--bs-primary), var(--bs-success));
    border-radius: 2px;
    animation: growBar 0.8s ease forwards;
}

.mini-chart span {
    font-size: 0.75rem;
    color: var(--bs-body-color);
    font-weight: 500;
}

@keyframes slideInChart {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes growBar {
    from { height: 2px; }
    to { height: 16px; }
}

/* Card entrance animation setup */
.path-card {
    opacity: 0;
    transform: translateY(20px);
    animation: none;
}

.path-card.animate-in {
    animation: cardEnter 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes cardEnter {
    to {
        opacity: 1;
        transform: translateY(0px);
    }
}

@media (max-width: 768px) {
    .path-cards-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .path-card {
        padding: 1.5rem;
    }
    
    .cost-preview {
        position: relative;
        bottom: auto;
        margin-top: 1rem;
    }
}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pathCards = document.querySelectorAll('.path-card');
    const selectButtons = document.querySelectorAll('.btn-select-path');
    const dynamicForm = document.getElementById('dynamicForm');
    const costAnalysisHeader = document.getElementById('costAnalysisHeader');
    const pathSelection = document.getElementById('pathSelection');
    
    let uploadedFiles = [];
    let selectedPath = null;
    
    // Add entrance animation to cards
    setTimeout(() => {
        pathCards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('animate-in');
            }, index * 200);
        });
    }, 100);
    
    // Path selection handlers with enhanced animations
    selectButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const target = this.dataset.target;
            const card = this.closest('.path-card');
            
            // Add ripple effect
            createRippleEffect(e, this);
            
            // Mark as selected
            pathCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            
            selectedPath = target;
            
            // Delay form showing for animation
            setTimeout(() => {
                showForm(target);
            }, 300);
        });
    });
    
    // Card hover effects with cost preview
    pathCards.forEach(card => {
        const pathType = card.dataset.path;
        
        card.addEventListener('mouseenter', function() {
            if (!selectedPath) {
                showCostPreview(pathType, this);
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!selectedPath) {
                hideCostPreview(this);
            }
        });
    });
    
    // Create ripple effect
    function createRippleEffect(e, button) {
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            pointer-events: none;
            z-index: 1;
        `;
        
        button.appendChild(ripple);
        
        requestAnimationFrame(() => {
            ripple.style.transform = 'scale(2)';
            ripple.style.opacity = '0';
            ripple.style.transition = 'transform 0.6s ease, opacity 0.6s ease';
        });
        
        setTimeout(() => ripple.remove(), 600);
    }
    
    // Show cost preview animation
    function showCostPreview(pathType, card) {
        const costData = pathType === 'existing-url' ? 
            ['Analyze Current: $0-50/month', 'Optimization potential', 'Infrastructure review'] :
            ['New Setup: $7-25/month', 'Full deployment', 'Complete documentation'];
            
        let preview = card.querySelector('.cost-preview');
        if (!preview) {
            preview = document.createElement('div');
            preview.className = 'cost-preview';
            preview.innerHTML = `
                <div class="mini-chart">
                    ${costData.map((item, i) => `
                        <div class="chart-bar" style="animation-delay: ${i * 0.1}s">
                            <div class="bar-fill"></div>
                            <span>${item}</span>
                        </div>
                    `).join('')}
                </div>
            `;
            card.appendChild(preview);
        }
        
        preview.style.opacity = '1';
        preview.style.transform = 'translateY(0)';
    }
    
    // Hide cost preview
    function hideCostPreview(card) {
        const preview = card.querySelector('.cost-preview');
        if (preview) {
            preview.style.opacity = '0';
            preview.style.transform = 'translateY(10px)';
        }
    }
    
    function showForm(pathType) {
        let formHTML = '';
        
        if (pathType === 'existing-url') {
            formHTML = `
                <div class="form-header">
                    <h3><i class="fas fa-link"></i> <?= __('Connect Existing URL/Domain') ?></h3>
                    <p><?= __('Provide details about your existing website or application for cost analysis.') ?></p>
                </div>
                
                <form id="existingUrlForm" class="deployment-form">
                    <div class="form-group">
                        <label class="form-label" for="existing_url"><?= __('Website URL/Domain') ?></label>
                        <input type="url" class="form-control" id="existing_url" name="existing_url" 
                               placeholder="https://your-website.com" required>
                        <small class="form-text text-muted"><?= __('Enter the full URL of your existing website') ?></small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="current_hosting"><?= __('Current Hosting Provider') ?></label>
                        <select class="form-control" id="current_hosting" name="current_hosting" required>
                            <option value=""><?= __('Select your current hosting') ?></option>
                            <option value="digitalocean"><?= __('DigitalOcean') ?></option>
                            <option value="aws"><?= __('Amazon Web Services') ?></option>
                            <option value="heroku"><?= __('Heroku') ?></option>
                            <option value="shared-hosting"><?= __('Shared Hosting (cPanel/Plesk)') ?></option>
                            <option value="vps"><?= __('VPS/Dedicated Server') ?></option>
                            <option value="other"><?= __('Other') ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="monthly_cost"><?= __('Current Monthly Cost (USD)') ?></label>
                        <input type="number" class="form-control" id="monthly_cost" name="monthly_cost" 
                               placeholder="25" min="0" step="0.01">
                        <small class="form-text text-muted"><?= __('Approximate monthly hosting cost in USD') ?></small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="traffic_estimate"><?= __('Monthly Visitors/Traffic') ?></label>
                        <select class="form-control" id="traffic_estimate" name="traffic_estimate">
                            <option value="low"><?= __('Low (< 1,000 visitors/month)') ?></option>
                            <option value="medium" selected><?= __('Medium (1,000-10,000 visitors/month)') ?></option>
                            <option value="high"><?= __('High (10,000+ visitors/month)') ?></option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="resetForm()"><?= __('Back') ?></button>
                        <button type="submit" class="btn btn-primary"><?= __('Analyze Existing Setup') ?></button>
                    </div>
                </form>
            `;
        } else if (pathType === 'new-deployment') {
            formHTML = `
                <div class="form-header">
                    <h3><i class="fas fa-plus-circle"></i> <?= __('Create New Deployment') ?></h3>
                    <p><?= __('Upload custom files and configure your new deployment.') ?></p>
                </div>
                
                <form id="newDeploymentForm" class="deployment-form">
                    <div class="form-group">
                        <label class="form-label" for="project_name"><?= __('Project Name') ?></label>
                        <input type="text" class="form-control" id="project_name" name="project_name" 
                               placeholder="My Willow CMS Project" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?= __('Upload Custom Files') ?></label>
                        <div class="file-upload-area" id="fileUploadArea">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--bs-primary); margin-bottom: 1rem;"></i>
                            <p><strong><?= __('Drag & drop files here or click to browse') ?></strong></p>
                            <p class="text-muted"><?= __('Supported: HTML, CSS, JS, PHP, Images, Documents') ?></p>
                            <input type="file" id="fileInput" multiple accept=".html,.css,.js,.php,.png,.jpg,.jpeg,.gif,.pdf,.txt,.md" style="display: none;">
                        </div>
                        <ul id="fileList" class="file-list"></ul>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="deployment_type"><?= __('Preferred Deployment Type') ?></label>
                        <select class="form-control" id="deployment_type" name="deployment_type" required>
                            <option value="simple"><?= __('Simple (Single droplet + Docker)') ?></option>
                            <option value="compose" selected><?= __('Docker Compose (Multi-container)') ?></option>
                            <option value="kubernetes"><?= __('Kubernetes (Advanced scaling)') ?></option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="expected_traffic"><?= __('Expected Monthly Traffic') ?></label>
                        <select class="form-control" id="expected_traffic" name="expected_traffic">
                            <option value="low"><?= __('Low (< 1,000 visitors)') ?></option>
                            <option value="medium" selected><?= __('Medium (1,000-10,000 visitors)') ?></option>
                            <option value="high"><?= __('High (10,000+ visitors)') ?></option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="resetForm()"><?= __('Back') ?></button>
                        <button type="submit" class="btn btn-success"><?= __('Generate Deployment Plan') ?></button>
                    </div>
                </form>
            `;
        }
        
        dynamicForm.innerHTML = formHTML;
        dynamicForm.style.display = 'block';
        
        // Hide path selection and show cost analysis
        pathSelection.style.display = 'none';
        costAnalysisHeader.style.display = 'block';
        
        // Setup file upload if new deployment form
        if (pathType === 'new-deployment') {
            setupFileUpload();
        }
        
        // Setup form submission
        setupFormSubmission(pathType);
    }
    
    function setupFileUpload() {
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        
        // Click to upload
        fileUploadArea.addEventListener('click', () => fileInput.click());
        
        // Drag and drop
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });
        
        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });
        
        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            const files = Array.from(e.dataTransfer.files);
            addFiles(files);
        });
        
        fileInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            addFiles(files);
        });
        
        function addFiles(files) {
            files.forEach(file => {
                if (uploadedFiles.length < 10) { // Limit to 10 files
                    uploadedFiles.push(file);
                    addFileToList(file);
                }
            });
        }
        
        function addFileToList(file) {
            const li = document.createElement('li');
            li.className = 'file-item';
            li.innerHTML = `
                <div class="file-info">
                    <i class="fas fa-file file-icon"></i>
                    <span>${file.name} (${formatFileSize(file.size)})</span>
                </div>
                <button type="button" class="file-remove" onclick="removeFile('${file.name}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            fileList.appendChild(li);
        }
    }
    
    function setupFormSubmission(pathType) {
        const form = document.getElementById(pathType === 'existing-url' ? 'existingUrlForm' : 'newDeploymentForm');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show success message
            const successMessage = `
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <strong><?= __('Analysis Complete!') ?></strong>
                    ${pathType === 'existing-url' ? 
                        '<?= __("Your existing setup has been analyzed. See recommendations below.") ?>' : 
                        '<?= __("Your deployment plan has been generated. Files uploaded and recommendations ready.") ?>'}
                </div>
            `;
            
            form.insertAdjacentHTML('beforebegin', successMessage);
            form.style.display = 'none';
            
            // Scroll to cost analysis
            setTimeout(() => {
                document.querySelector('.platforms-section').scrollIntoView({ behavior: 'smooth' });
            }, 1000);
        });
    }
    
    // Global functions with enhanced animations
    window.resetForm = function() {
        // Fade out current form
        dynamicForm.style.opacity = '0';
        dynamicForm.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            dynamicForm.style.display = 'none';
            costAnalysisHeader.style.display = 'none';
            
            // Reset selection states
            pathCards.forEach(c => c.classList.remove('selected'));
            selectedPath = null;
            
            // Show path selection with animation
            pathSelection.style.display = 'block';
            pathSelection.style.opacity = '0';
            
            requestAnimationFrame(() => {
                pathSelection.style.transition = 'opacity 0.4s ease';
                pathSelection.style.opacity = '1';
            });
            
            uploadedFiles = [];
        }, 300);
    };
    
    window.removeFile = function(fileName) {
        uploadedFiles = uploadedFiles.filter(file => file.name !== fileName);
        const fileItems = document.querySelectorAll('.file-item');
        fileItems.forEach(item => {
            if (item.textContent.includes(fileName)) {
                item.remove();
            }
        });
    };
    
    window.formatFileSize = function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };
});
</script>
<?php $this->end(); ?>
