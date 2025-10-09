#!/usr/bin/env php
<?php

/**
 * Test script to log into admin and test queue configuration interface
 */

$baseUrl = 'http://localhost:8080';
$cookieFile = tempnam(sys_get_temp_dir(), 'admin_cookies');

function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    global $cookieFile;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Admin Interface Test');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1',
    ], $headers));
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    curl_close($ch);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    return [
        'code' => $httpCode,
        'headers' => $headers,
        'body' => $body
    ];
}

try {
    echo "Testing Queue Configuration Admin Interface\n";
    echo "==========================================\n\n";
    
    // Step 1: Get login form (get CSRF token)
    echo "1. Accessing login page...\n";
    $loginPage = makeRequest($baseUrl . '/en/users/login');
    
    if ($loginPage['code'] !== 200) {
        echo "   ❌ Failed to access login page (HTTP {$loginPage['code']})\n";
        exit(1);
    }
    
    // Extract CSRF token from login form
    preg_match('/<input type="hidden" name="_csrfToken" value="([^"]+)"/', $loginPage['body'], $tokenMatch);
    if (empty($tokenMatch[1])) {
        echo "   ❌ Could not find CSRF token in login form\n";
        exit(1);
    }
    
    $csrfToken = $tokenMatch[1];
    echo "   ✅ Login page loaded, CSRF token: " . substr($csrfToken, 0, 20) . "...\n";
    
    // Step 2: Attempt login
    echo "\n2. Attempting admin login...\n";
    $loginData = http_build_query([
        'username' => 'admin@test.com',
        'password' => 'password',
        '_csrfToken' => $csrfToken
    ]);
    
    $loginResponse = makeRequest($baseUrl . '/en/users/login', 'POST', $loginData, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    if ($loginResponse['code'] === 302 && strpos($loginResponse['headers'], 'Location: http://localhost:8080/en/admin') !== false) {
        echo "   ✅ Login successful (redirected to admin)\n";
    } elseif ($loginResponse['code'] === 302) {
        echo "   ⚠️  Login redirect to: " . preg_match('/Location: ([^\r\n]+)/', $loginResponse['headers'], $match) ? $match[1] : 'unknown' . "\n";
    } else {
        echo "   ❌ Login failed (HTTP {$loginResponse['code']})\n";
    }
    
    // Step 3: Access queue configurations admin page
    echo "\n3. Accessing queue configurations admin page...\n";
    $queueConfigPage = makeRequest($baseUrl . '/admin/queue-configurations');
    
    if ($queueConfigPage['code'] === 200) {
        echo "   ✅ Queue configurations page loaded successfully\n";
        
        // Check if the page contains expected elements
        $hasTable = strpos($queueConfigPage['body'], 'table') !== false;
        $hasHealthColumn = strpos($queueConfigPage['body'], 'Health Status') !== false || strpos($queueConfigPage['body'], 'Health') !== false;
        $hasRefreshButton = strpos($queueConfigPage['body'], 'Health Check') !== false || strpos($queueConfigPage['body'], 'refreshHealthBtn') !== false;
        
        echo "   Content checks:\n";
        echo "     - Has table: " . ($hasTable ? "✅" : "❌") . "\n";
        echo "     - Has health column: " . ($hasHealthColumn ? "✅" : "❌") . "\n";
        echo "     - Has health check button: " . ($hasRefreshButton ? "✅" : "❌") . "\n";
        
    } elseif ($queueConfigPage['code'] === 302) {
        echo "   ⚠️  Still redirecting (possibly auth issue)\n";
        preg_match('/Location: ([^\r\n]+)/', $queueConfigPage['headers'], $match);
        echo "   Redirect to: " . ($match[1] ?? 'unknown') . "\n";
    } else {
        echo "   ❌ Failed to load queue configurations (HTTP {$queueConfigPage['code']})\n";
    }
    
    // Step 4: Test health check endpoint
    echo "\n4. Testing health check endpoint...\n";
    $healthResponse = makeRequest($baseUrl . '/admin/queue-configurations/health-check-all', 'GET', null, [
        'X-Requested-With: XMLHttpRequest',
        'Accept: application/json'
    ]);
    
    if ($healthResponse['code'] === 200) {
        echo "   ✅ Health check endpoint accessible\n";
        
        // Try to parse JSON response
        $jsonStart = strpos($healthResponse['body'], '{');
        if ($jsonStart !== false) {
            $jsonBody = substr($healthResponse['body'], $jsonStart);
            $healthData = json_decode($jsonBody, true);
            
            if ($healthData && isset($healthData['success'])) {
                echo "   ✅ Valid JSON response\n";
                echo "   Success: " . ($healthData['success'] ? 'true' : 'false') . "\n";
                
                if (isset($healthData['health_statuses'])) {
                    $count = count($healthData['health_statuses']);
                    echo "   Health statuses returned: $count\n";
                }
            } else {
                echo "   ⚠️  Response not valid JSON or missing success field\n";
            }
        } else {
            echo "   ⚠️  No JSON found in response\n";
        }
    } else {
        echo "   ❌ Health check endpoint failed (HTTP {$healthResponse['code']})\n";
    }
    
    echo "\nTest completed!\n";
    
    // Cleanup
    if (file_exists($cookieFile)) {
        unlink($cookieFile);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}