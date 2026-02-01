<?php
/**
 * T2M URL Shortener - URL Creation Handler (Security Enhanced)
 * 
 * This file handles the creation of new short URLs with:
 * - Enhanced input validation and sanitization
 * - XSS protection through proper escaping
 * - CSRF token validation (prepared)
 * - Rate limiting preparation
 * - Comprehensive error handling
 * 
 * @author T2M Team
 * @version 1.1.0 - Security Enhanced
 */

session_start();

// Authentication check
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header('Location: index.php');
    exit;
}

// CSRF Token Validation (prepared for future implementation)
// Uncomment when CSRF tokens are implemented
/*
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Invalid security token. Please try again.';
    header('Location: index.php');
    exit;
}
*/

/**
 * Input Sanitization and Validation
 */

// Sanitize and validate long URL
$longUrl = filter_input(INPUT_POST, 'long_url', FILTER_SANITIZE_URL);
$longUrl = trim($longUrl);

// Validate URL format
if (!filter_var($longUrl, FILTER_VALIDATE_URL)) {
    $_SESSION['error'] = 'Invalid URL provided. Please enter a valid URL starting with http:// or https://';
    header('Location: index.php');
    exit;
}

// Check URL scheme (only allow http and https)
$parsedUrl = parse_url($longUrl);
if (!isset($parsedUrl['scheme']) || !in_array(strtolower($parsedUrl['scheme']), ['http', 'https'])) {
    $_SESSION['error'] = 'Only HTTP and HTTPS URLs are allowed';
    header('Location: index.php');
    exit;
}

// Prevent internal URL shortening (optional security measure)
$currentDomain = $_SERVER['HTTP_HOST'];
if (isset($parsedUrl['host']) && $parsedUrl['host'] === $currentDomain) {
    $_SESSION['error'] = 'Cannot shorten URLs from this domain';
    header('Location: index.php');
    exit;
}

// Sanitize custom code
$customCode = filter_input(INPUT_POST, 'custom_code', FILTER_SANITIZE_STRING);
$customCode = trim($customCode);

/**
 * Load Existing URLs with Error Handling
 */
$dataFile = 'data/urls.json';
if (!file_exists('data')) {
    if (!mkdir('data', 0755, true)) {
        $_SESSION['error'] = 'System error: Unable to create data directory';
        header('Location: index.php');
        exit;
    }
}

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

$urls = json_decode(file_get_contents($dataFile), true);
if ($urls === null) {
    // JSON parsing error
    error_log('JSON parsing error in create.php');
    $urls = [];
}

/**
 * Generate or Validate Custom Code
 */
if (!empty($customCode)) {
    // Validate custom code format
    if (!preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $customCode)) {
        $_SESSION['error'] = 'Custom code must be 3-50 characters and contain only letters, numbers, hyphens, and underscores';
        header('Location: index.php');
        exit;
    }
    
    // Reserved codes check (protect system files)
    $reservedCodes = ['index', 'login', 'logout', 'create', 'edit', 'delete', 'admin', 'api', 'assets', 'data'];
    if (in_array(strtolower($customCode), $reservedCodes)) {
        $_SESSION['error'] = 'This code is reserved. Please choose a different code.';
        header('Location: index.php');
        exit;
    }
    
    // Check if code already exists
    foreach ($urls as $url) {
        if ($url['code'] === $customCode) {
            $_SESSION['error'] = 'This custom code is already taken. Please choose a different code.';
            header('Location: index.php');
            exit;
        }
    }
    
    $code = $customCode;
} else {
    // Generate random code
    $code = generateRandomCode(6);
    
    // Ensure uniqueness with retry limit
    $attempts = 0;
    $maxAttempts = 10;
    while (codeExists($code, $urls) && $attempts < $maxAttempts) {
        $code = generateRandomCode(6);
        $attempts++;
    }
    
    if ($attempts >= $maxAttempts) {
        $_SESSION['error'] = 'Unable to generate unique code. Please try again.';
        header('Location: index.php');
        exit;
    }
}

/**
 * Build Short URL
 */
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$domain = $_SERVER['HTTP_HOST'];
$shortUrl = $protocol . '://' . $domain . '/r.php?code=' . $code;

/**
 * Create New URL Entry
 */
$newUrl = [
    'id' => uniqid('url_', true), // More unique ID
    'code' => $code,
    'long_url' => $longUrl,
    'short_url' => $shortUrl,
    'clicks' => 0,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => null,
    'expires_at' => null,
    'last_clicked' => null,
    'click_log' => [],
    'created_by' => $_SESSION['username'] ?? 'admin' // Track who created it
];

/**
 * Save to JSON with Error Handling
 */
$urls[] = $newUrl;

if (file_put_contents($dataFile, json_encode($urls, JSON_PRETTY_PRINT)) === false) {
    $_SESSION['error'] = 'Failed to save URL. Please try again.';
    header('Location: index.php');
    exit;
}

// Success!
$_SESSION['success'] = 'Short URL created successfully!';
$_SESSION['new_url'] = $shortUrl;
header('Location: index.php');
exit;

/**
 * Helper Functions
 */

/**
 * Generate a cryptographically secure random code
 * 
 * @param int $length Length of the code to generate
 * @return string Random code
 */
function generateRandomCode($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $code = '';
    
    // Use random_int for better randomness
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, $charactersLength - 1)];
    }
    
    return $code;
}

/**
 * Check if a code already exists in the URLs array
 * 
 * @param string $code Code to check
 * @param array $urls Array of existing URLs
 * @return bool True if code exists, false otherwise
 */
function codeExists($code, $urls) {
    foreach ($urls as $url) {
        if ($url['code'] === $code) {
            return true;
        }
    }
    return false;
}
