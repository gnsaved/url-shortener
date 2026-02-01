<?php
// r.php - Redirect handler
// Usage: r.php?code=abc123

$code = $_GET['code'] ?? '';

if (empty($code)) {
    http_response_code(404);
    die('Short URL not found');
}

// Load URLs from JSON
$dataFile = 'data/urls.json';
if (!file_exists($dataFile)) {
    http_response_code(404);
    die('Short URL not found');
}

$urls = json_decode(file_get_contents($dataFile), true) ?: [];

// Find the URL by code
$found = null;
foreach ($urls as &$url) {
    if ($url['code'] === $code) {
        $found = &$url;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    die('Short URL not found');
}

// Check if expired
if ($found['expires_at'] && strtotime($found['expires_at']) < time()) {
    http_response_code(410);
    die('This short URL has expired');
}

// Increment click count
$found['clicks']++;
$found['last_clicked'] = date('Y-m-d H:i:s');

// Log click (optional - for analytics)
$clickData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    'referer' => $_SERVER['HTTP_REFERER'] ?? ''
];

if (!isset($found['click_log'])) {
    $found['click_log'] = [];
}
$found['click_log'][] = $clickData;

// Save updated data
file_put_contents($dataFile, json_encode($urls, JSON_PRETTY_PRINT));

// Redirect to long URL
header('Location: ' . $found['long_url'], true, 301);
exit;
