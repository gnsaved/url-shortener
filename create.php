<?php
/**
 * T2M URL Shortener - Main Dashboard
 * 
 * This is the primary interface for managing short URLs.
 * Displays a comprehensive list of all shortened URLs with statistics,
 * provides actions for creating, editing, and deleting URLs,
 * and shows click analytics.
 * 
 * @author T2M Team
 * @version 1.1.0
 * @since 2024-01-01
 */

// Start session for authentication
session_start();

/**
 * Authentication Check
 * Redirects unauthenticated users to login page
 */
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

/**
 * Load URL Data from JSON Storage
 * Reads the urls.json file and parses it into an array
 */
$dataFile = 'data/urls.json';
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

// Parse JSON data with error handling
$urls = json_decode(file_get_contents($dataFile), true);
if ($urls === null) {
    // Handle JSON parsing errors
    $urls = [];
    error_log('JSON parsing error in index.php');
}

/**
 * Sort URLs by Creation Date
 * Display newest URLs first for better UX
 */
usort($urls, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

/**
 * Display Success/Error Messages
 * Shows feedback from create, edit, or delete operations
 */
$successMessage = $_SESSION['success'] ?? null;
$errorMessage = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T2M Dashboard - URL Shortener</title>
    <link rel="stylesheet" href="assets/style.css">
    <meta name="description" content="Manage your shortened URLs with analytics and tracking">
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/logo.png" alt="T2M" onerror="this.style.display='none'">
            </div>
            
            <!-- User Profile Section -->
            <div class="user-info">
                <div class="avatar">TA</div>
                <div class="user-name">T2M Admin</div>
                <div class="user-role">T2M Member</div>
                <button class="premium-btn">Premium Plan</button>
            </div>
            
            <!-- Main Navigation Menu -->
            <nav class="nav-menu">
                <a href="index.php" class="nav-item active">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="index.php" class="nav-item">
                    <span class="icon">🔗</span> Manage Short URL(s)
                </a>
                <a href="#" class="nav-item">
                    <span class="icon">⚙️</span> Account Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Header Bar -->
            <header class="top-bar">
                <button class="menu-toggle" aria-label="Toggle menu">☰</button>
                <div class="header-right">
                    <span>Welcome to T2M Dashboard!</span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <div class="content-area">
                <!-- Breadcrumb Navigation -->
                <div class="breadcrumb">
                    <a href="#">Home</a> / <a href="#">Dashboard</a> / <strong>All Short URLs</strong>
                </div>

                <h1>Dashboard</h1>

                <!-- Success/Error Messages -->
                <?php if ($successMessage): ?>
                    <div class="alert alert-success">
                        ✅ <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($errorMessage): ?>
                    <div class="alert alert-error">
                        ⚠️ <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="showCreateModal()">+ Create Short URL</button>
                    <button class="btn btn-secondary">🔄 Bulk Actions</button>
                    <a href="export.php" class="btn btn-secondary">📥 Export to CSV</a>
                </div>

                <!-- Timezone Notice -->
                <div class="note">
                    <strong>Note:</strong> Date values are showing in <strong>UTC-06:00</strong> timezone.
                </div>

                <!-- URL Table Container -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>Your Short URL(s) (<?= count($urls) ?>)</h2>
                        <button class="btn-filters">🔍 Filters</button>
                    </div>

                    <!-- URLs Data Table -->
                    <table class="url-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all" aria-label="Select all"></th>
                                <th>#</th>
                                <th>Short URL</th>
                                <th>Clicks</th>
                                <th>QR Code</th>
                                <th>Analytics</th>
                                <th>Expires At</th>
                                <th>Created At</th>
                                <th>Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($urls)): ?>
                                <!-- Empty State -->
                                <tr>
                                    <td colspan="9" class="empty-state">
                                        📭 No URLs yet. Create your first short URL!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <!-- URL Rows -->
                                <?php foreach ($urls as $index => $url): ?>
                                <tr>
                                    <td><input type="checkbox" aria-label="Select URL"></td>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <!-- Short URL Link -->
                                        <a href="<?= htmlspecialchars($url['short_url']) ?>" 
                                           target="_blank" 
                                           class="short-link"
                                           rel="noopener">
                                            <?= htmlspecialchars($url['short_url']) ?>
                                        </a>
                                        
                                        <!-- Long URL Preview -->
                                        <div class="long-url" title="<?= htmlspecialchars($url['long_url']) ?>">
                                            <?= htmlspecialchars($url['long_url']) ?>
                                        </div>
                                        
                                        <!-- Action Links -->
                                        <div class="url-actions">
                                            <a href="edit.php?id=<?= urlencode($url['id']) ?>">✏️ Edit</a>
                                            <a href="delete.php?confirm=1&id=<?= urlencode($url['id']) ?>">🗑️ Delete</a>
                                            <a href="#" onclick="copyToClipboard('<?= htmlspecialchars($url['short_url']) ?>'); return false;">📋 Copy</a>
                                        </div>
                                    </td>
                                    
                                    <!-- Click Count -->
                                    <td><?= number_format($url['clicks']) ?></td>
                                    
                                    <!-- QR Code Downloads -->
                                    <td>
                                        <a href="qr.php?id=<?= urlencode($url['id']) ?>" target="_blank">SVG</a> | 
                                        <a href="qr.php?id=<?= urlencode($url['id']) ?>&format=png" target="_blank">PNG</a>
                                    </td>
                                    
                                    <!-- Analytics Link -->
                                    <td>
                                        <a href="analytics.php?id=<?= urlencode($url['id']) ?>" class="view-link">📊 View</a>
                                    </td>
                                    
                                    <!-- Expiration Info -->
                                    <td>
                                        <?php if ($url['expires_at']): ?>
                                            <a href="#" class="expiry-link">Set Expiry</a><br>
                                            <small><?= date('M j, Y g:i a', strtotime($url['expires_at'])) ?></small>
                                        <?php else: ?>
                                            <a href="#" class="expiry-link">Set Expiry</a>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Creation Date -->
                                    <td>
                                        <?= date('M j, Y', strtotime($url['created_at'])) ?><br>
                                        <small><?= date('g:i a', strtotime($url['created_at'])) ?></small>
                                    </td>
                                    
                                    <!-- Share Buttons -->
                                    <td>
                                        <div class="share-buttons">
                                            <button class="share-btn facebook" 
                                                    data-url="<?= htmlspecialchars($url['short_url']) ?>"
                                                    aria-label="Share on Facebook">f</button>
                                            <button class="share-btn twitter" 
                                                    data-url="<?= htmlspecialchars($url['short_url']) ?>"
                                                    aria-label="Share on Twitter">𝕏</button>
                                            <button class="share-btn more" 
                                                    data-url="<?= htmlspecialchars($url['short_url']) ?>"
                                                    aria-label="More options">⚫</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                Powered by <strong>T2M URL Shortener</strong> | 
                Total URLs: <?= count($urls) ?> | 
                Total Clicks: <?= number_format(array_sum(array_column($urls, 'clicks'))) ?>
            </footer>
        </main>
    </div>

    <!-- Create URL Modal -->
    <div id="createModal" class="modal" role="dialog" aria-labelledby="modalTitle">
        <div class="modal-content">
            <span class="close" onclick="closeCreateModal()" aria-label="Close">&times;</span>
            <h2 id="modalTitle">Create Short URL</h2>
            <form action="create.php" method="POST">
                <div class="form-group">
                    <label for="long_url">Long URL *</label>
                    <input type="url" 
                           id="long_url" 
                           name="long_url" 
                           required 
                           placeholder="https://example.com/very/long/url"
                           aria-required="true">
                </div>
                <div class="form-group">
                    <label for="custom_code">Custom Code (optional)</label>
                    <input type="text" 
                           id="custom_code" 
                           name="custom_code" 
                           placeholder="my-custom-code"
                           pattern="[a-zA-Z0-9_-]+"
                           title="Only letters, numbers, hyphens, and underscores allowed">
                    <small>Leave empty for random generation</small>
                </div>
                <button type="submit" class="btn btn-primary">Create Short URL</button>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/script.js"></script>
</body>
</html>
