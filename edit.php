<?php
/**
 * URL Edit Handler
 * 
 * Allows modification of existing short URLs while preserving statistics.
 * Supports editing long URLs and custom codes with validation.
 */

session_start();

// Authentication check
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? $_POST['id'] ?? '';

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

// Find URL to edit
$urlToEdit = null;
$urlIndex = null;
foreach ($urls as $index => $url) {
    if ($url['id'] === $id) {
        $urlToEdit = $url;
        $urlIndex = $index;
        break;
    }
}

if (!$urlToEdit) {
    $_SESSION['error'] = 'URL not found';
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newLongUrl = trim($_POST['long_url'] ?? '');
    $newCustomCode = trim($_POST['custom_code'] ?? '');
    
    // Validate long URL
    if (!filter_var($newLongUrl, FILTER_VALIDATE_URL)) {
        $error = 'Invalid URL provided';
    } else {
        $codeChanged = false;
        
        // Handle custom code change
        if (!empty($newCustomCode) && $newCustomCode !== $urlToEdit['code']) {
            // Validate custom code
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $newCustomCode)) {
                $error = 'Custom code can only contain letters, numbers, hyphens, and underscores';
            } else {
                // Check if new code already exists
                foreach ($urls as $url) {
                    if ($url['code'] === $newCustomCode && $url['id'] !== $id) {
                        $error = 'This custom code is already taken';
                        break;
                    }
                }
                
                if (!isset($error)) {
                    $urlToEdit['code'] = $newCustomCode;
                    $codeChanged = true;
                }
            }
        }
        
        if (!isset($error)) {
            // Update URL data
            $urlToEdit['long_url'] = $newLongUrl;
            $urlToEdit['updated_at'] = date('Y-m-d H:i:s');
            
            // Update short URL if code changed
            if ($codeChanged) {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $domain = $_SERVER['HTTP_HOST'];
                $urlToEdit['short_url'] = $protocol . '://' . $domain . '/r.php?code=' . $urlToEdit['code'];
            }
            
            // Save updated data
            $urls[$urlIndex] = $urlToEdit;
            
            if (file_put_contents($dataFile, json_encode($urls, JSON_PRETTY_PRINT)) !== false) {
                $_SESSION['success'] = 'URL updated successfully';
                header('Location: index.php');
                exit;
            } else {
                $error = 'Failed to save changes. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit URL - T2M URL Shortener</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .edit-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .stats-info {
            background: #f0fdf4;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #10b981;
        }
        .stats-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h1>Edit Short URL</h1>
        <p style="color: #666; margin-bottom: 20px;">
            <a href="index.php">← Back to Dashboard</a>
        </p>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="stats-info">
            <p><strong>📊 Current Statistics:</strong></p>
            <p>Total Clicks: <strong><?= number_format($urlToEdit['clicks']) ?></strong></p>
            <p>Created: <strong><?= date('M j, Y g:i a', strtotime($urlToEdit['created_at'])) ?></strong></p>
            <?php if ($urlToEdit['last_clicked']): ?>
                <p>Last Clicked: <strong><?= date('M j, Y g:i a', strtotime($urlToEdit['last_clicked'])) ?></strong></p>
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            
            <div class="form-group">
                <label for="long_url">Long URL *</label>
                <input 
                    type="url" 
                    id="long_url" 
                    name="long_url" 
                    required 
                    value="<?= htmlspecialchars($urlToEdit['long_url']) ?>"
                    placeholder="https://example.com/very/long/url"
                >
            </div>
            
            <div class="form-group">
                <label for="custom_code">Short Code *</label>
                <input 
                    type="text" 
                    id="custom_code" 
                    name="custom_code" 
                    value="<?= htmlspecialchars($urlToEdit['code']) ?>"
                    placeholder="custom-code"
                >
                <small>Current short URL: <?= htmlspecialchars($urlToEdit['short_url']) ?></small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                <a href="index.php" class="btn btn-secondary" style="flex: 1; text-align: center; padding: 10px;">Cancel</a>
            </div>
        </form>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <p style="color: #999; font-size: 13px;">
                ⚠️ Note: Changing the short code will create a new URL. The old code will no longer work.
                Click statistics will be preserved.
            </p>
        </div>
    </div>
</body>
</html>
