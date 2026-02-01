<?php
session_start();

// Simple auth check
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$longUrl = trim($_POST['long_url'] ?? '');
$customCode = trim($_POST['custom_code'] ?? '');

// Validate URL
if (!filter_var($longUrl, FILTER_VALIDATE_URL)) {
    $_SESSION['error'] = 'Invalid URL provided';
    header('Location: index.php');
    exit;
}

// Load existing URLs
$dataFile = 'data/urls.json';
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

$urls = json_decode(file_get_contents($dataFile), true) ?: [];

// Generate or validate custom code
if (!empty($customCode)) {
    // Validate custom code
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $customCode)) {
        $_SESSION['error'] = 'Custom code can only contain letters, numbers, hyphens, and underscores';
        header('Location: index.php');
        exit;
    }
    
    // Check if code already exists
    foreach ($urls as $url) {
        if ($url['code'] === $customCode) {
            $_SESSION['error'] = 'This custom code is already taken';
            header('Location: index.php');
            exit;
        }
    }
    
    $code = $customCode;
} else {
    // Generate random code
    $code = generateRandomCode(6);
    
    // Ensure uniqueness
    $attempts = 0;
    while (codeExists($code, $urls) && $attempts < 10) {
        $code = generateRandomCode(6);
        $attempts++;
    }
}

// AUTO-DETECT BASE URL (works in any folder/subdomain!)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$domain = $_SERVER['HTTP_HOST'];

// Get the directory where the script is located
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$scriptDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;

// Build base URL automatically
$baseUrl = $protocol . '://' . $domain . $scriptDir;

// Create short URL - works with or without .htaccess
// First, try to detect if .htaccess is working
$htaccessWorks = file_exists('.htaccess');

if ($htaccessWorks) {
    // Use clean URLs if .htaccess exists
    $shortUrl = $baseUrl . '/' . $code;
} else {
    // Use r.php?code= format as fallback
    $shortUrl = $baseUrl . '/r.php?code=' . $code;
}

// Create new URL entry
$newUrl = [
    'id' => uniqid(),
    'code' => $code,
    'long_url' => $longUrl,
    'short_url' => $shortUrl,
    'base_url' => $baseUrl, // Store base URL for reference
    'clicks' => 0,
    'created_at' => date('Y-m-d H:i:s'),
    'expires_at' => null,
    'last_clicked' => null,
    'click_log' => []
];

// Add to array and save
$urls[] = $newUrl;
file_put_contents($dataFile, json_encode($urls, JSON_PRETTY_PRINT));

$_SESSION['success'] = 'Short URL created successfully!';
$_SESSION['new_url'] = $shortUrl;
header('Location: index.php');
exit;

function generateRandomCode($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function codeExists($code, $urls) {
    foreach ($urls as $url) {
        if ($url['code'] === $code) {
            return true;
        }
    }
    return false;
}
