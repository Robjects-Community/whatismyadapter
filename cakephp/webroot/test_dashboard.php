<?php
/**
 * Simple test dashboard to verify rate limiting functionality
 */
echo "<!DOCTYPE html>
<html>
<head>
    <title>AI Metrics Dashboard - Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .metric { margin: 10px 0; }
        .progress { width: 100%; height: 20px; border: 1px solid #ccc; border-radius: 10px; overflow: hidden; }
        .progress-bar { height: 100%; background: linear-gradient(45deg, #007bff, #0056b3); }
        .progress-bar.warning { background: linear-gradient(45deg, #ffc107, #e0a800); }
        .progress-bar.danger { background: linear-gradient(45deg, #dc3545, #b02a37); }
        .usage-text { font-weight: bold; margin-top: 5px; }
        button { padding: 10px 15px; margin: 5px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        #status { margin-top: 20px; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🤖 AI Metrics Dashboard - Rate Limiting Test</h1>
    
    <div class='card'>
        <h3>📊 Rate Limit Status</h3>
        <div id='rate-limits'>
            <div class='metric'>
                <strong>Combined Usage</strong>
                <div class='progress'><div id='combined-bar' class='progress-bar' style='width: 0%'></div></div>
                <div id='combined-text' class='usage-text'>0/20 requests (20 remaining)</div>
            </div>
            <div class='metric'>
                <strong>Google Service</strong>
                <div class='progress'><div id='google-bar' class='progress-bar' style='width: 0%'></div></div>
                <div id='google-text' class='usage-text'>0/10 requests (10 remaining)</div>
            </div>
            <div class='metric'>
                <strong>Anthropic Service</strong>
                <div class='progress'><div id='anthropic-bar' class='progress-bar' style='width: 0%'></div></div>
                <div id='anthropic-text' class='usage-text'>0/10 requests (10 remaining)</div>
            </div>
        </div>
    </div>
    
    <div class='card'>
        <h3>🧪 Test Controls</h3>
        <button class='btn-primary' onclick='refreshMetrics()'>Refresh Metrics</button>
        <button class='btn-success' onclick='simulateGoogleCall()'>Simulate Google API Call</button>
        <button class='btn-success' onclick='simulateAnthropicCall()'>Simulate Anthropic API Call</button>
        <button class='btn-warning' onclick='resetCounters()'>Reset Counters</button>
        <div id='status'></div>
    </div>
</div>

<script>
// Auto-refresh every 2 seconds
setInterval(refreshMetrics, 2000);

// Initial load
refreshMetrics();

function refreshMetrics() {
    fetch('test_api.php?action=get_metrics')
        .then(response => response.json())
        .then(data => {
            updateMetrics(data);
        })
        .catch(error => {
            showStatus('Error fetching metrics: ' + error.message, 'error');
        });
}

function updateMetrics(data) {
    // Combined metrics
    updateProgressBar('combined', data.combined.current, data.combined.limit);
    
    // Individual service metrics
    updateProgressBar('google', data.services.google.current, data.services.google.limit);
    updateProgressBar('anthropic', data.services.anthropic.current, data.services.anthropic.limit);
    
    showStatus('Metrics updated: ' + new Date().toLocaleTimeString(), 'success');
}

function updateProgressBar(service, current, limit) {
    const percentage = limit > 0 ? (current / limit) * 100 : 0;
    const bar = document.getElementById(service + '-bar');
    const text = document.getElementById(service + '-text');
    
    bar.style.width = percentage + '%';
    
    // Color coding
    if (percentage >= 90) {
        bar.className = 'progress-bar danger';
    } else if (percentage >= 70) {
        bar.className = 'progress-bar warning';
    } else {
        bar.className = 'progress-bar';
    }
    
    text.textContent = `\${current}/\${limit} requests (\${Math.max(0, limit - current)} remaining)`;
}

function simulateGoogleCall() {
    fetch('test_api.php?action=simulate_call&service=google')
        .then(response => response.json())
        .then(data => {
            showStatus(data.message, data.success ? 'success' : 'error');
            refreshMetrics();
        });
}

function simulateAnthropicCall() {
    fetch('test_api.php?action=simulate_call&service=anthropic')
        .then(response => response.json())
        .then(data => {
            showStatus(data.message, data.success ? 'success' : 'error');
            refreshMetrics();
        });
}

function resetCounters() {
    fetch('test_api.php?action=reset')
        .then(response => response.json())
        .then(data => {
            showStatus(data.message, 'success');
            refreshMetrics();
        });
}

function showStatus(message, type) {
    const status = document.getElementById('status');
    status.textContent = message;
    status.className = type;
}
</script>
</body>
</html>";
?>
