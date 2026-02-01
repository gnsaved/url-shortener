<?php
/**
 * URL Deletion Handler
 * 
 * Handles the deletion of short URLs from the dashboard.
 * Preserves data integrity and provides user feedback.
 */

session_start();

// Authentication check
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

// Verify deletion request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['confirm'])) {
    $_SESSION['error'] = 'Invalid deletion request';
    header('Location: index.php');
    exit;
}

$id = $_POST['id'] ?? $_GET['id'] ?? '';

if (empty($id)) {
    $_SESSION['error'] = 'No URL ID provided';
    header('Location: index.php');
    exit;
}

// Load URLs from JSON
$dataFile = 'data/urls.json';
if (!file_exists($dataFile)) {
    $_SESSION['error'] = 'Data file not found';
    header('Location: index.php');
    exit;
}

$urls = json_decode(file_get_contents($dataFile), true) ?: [];

// Show confirmation page
if (isset($_GET['confirm'])) {
    $urlToDelete = null;
    foreach ($urls as $url) {
        if ($url['id'] === $id) {
            $urlToDelete = $url;
            break;
        }
    }
    
    if (!$urlToDelete) {
        $_SESSION['error'] = 'URL not found';
        header('Location: index.php');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirm Deletion</title>
        <link rel="stylesheet" href="assets/style.css">
        <style>
            .confirm-container {
                max-width: 600px;
                margin: 100px auto;
                padding: 40px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }
            .warning-icon {
                font-size: 64px;
                text-align: center;
                margin-bottom: 20px;
            }
            .url-info {
                background: #f9fafb;
                padding: 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
            .button-group {
                display: flex;
                gap: 10px;
                margin-top: 30px;
            }
            .btn-danger {
                background: #ef4444;
                color: white;
            }
            .btn-danger:hover {
                background: #dc2626;
            }
        </style>
    </head>
    <body>
        <div class="confirm-container">
            <div class="warning-icon">⚠️</div>
            <h1>Confirm Deletion</h1>
            <p>Are you sure you want to delete this short URL? This action cannot be undone.</p>
            
            <div class="url-info">
                <p><strong>Short URL:</strong> <?= htmlspecialchars($urlToDelete['short_url']) ?></p>
                <p><strong>Target:</strong> <?= htmlspecialchars($urlToDelete['long_url']) ?></p>
                <p><strong>Total Clicks:</strong> <?= number_format($urlToDelete['clicks']) ?></p>
                <p><strong>Created:</strong> <?= date('M j, Y g:i a', strtotime($urlToDelete['created_at'])) ?></p>
            </div>
            
            <div class="button-group">
                <form method="POST" action="delete.php" style="flex: 1;">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                    <button type="submit" class="btn btn-danger btn-block">Yes, Delete URL</button>
                </form>
                <a href="index.php" class="btn btn-secondary" style="flex: 1; text-align: center; padding: 10px;">Cancel</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Process deletion
$found = false;
$deletedUrl = null;
$newUrls = [];

foreach ($urls as $url) {
    if ($url['id'] === $id) {
        $found = true;
        $deletedUrl = $url;
        // Skip this URL (delete it)
        continue;
    }
    $newUrls[] = $url;
}

if (!$found) {
    $_SESSION['error'] = 'URL not found';
    header('Location: index.php');
    exit;
}

// Save updated data (with deleted URL removed)
if (file_put_contents($dataFile, json_encode($newUrls, JSON_PRETTY_PRINT)) === false) {
    $_SESSION['error'] = 'Failed to delete URL. Please try again.';
    header('Location: index.php');
    exit;
}

// Create backup of deleted URL (optional - for recovery)
$backupFile = 'data/deleted_urls.json';
$deletedUrls = [];
if (file_exists($backupFile)) {
    $deletedUrls = json_decode(file_get_contents($backupFile), true) ?: [];
}
$deletedUrl['deleted_at'] = date('Y-m-d H:i:s');
$deletedUrls[] = $deletedUrl;
file_put_contents($backupFile, json_encode($deletedUrls, JSON_PRETTY_PRINT));

// Success message
$_SESSION['success'] = 'Short URL deleted successfully';
header('Location: index.php');
exit;
