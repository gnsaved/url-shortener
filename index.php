<?php
session_start();

// Simple auth check
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

// Load URLs from JSON
$dataFile = 'data/urls.json';
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}
$urls = json_decode(file_get_contents($dataFile), true) ?: [];

// Sort by created date (newest first)
usort($urls, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>T2M Dashboard - URL Shortener</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/logo.png" alt="T2M" onerror="this.style.display='none'">
            </div>
            <div class="user-info">
                <div class="avatar">TA</div>
                <div class="user-name">T2M Admin</div>
                <div class="user-role">T2M Member</div>
                <button class="premium-btn">Premium Plan</button>
            </div>
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

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <button class="menu-toggle">☰</button>
                <div class="header-right">
                    <span>Welcome to T2M Dashboard!</span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </header>

            <div class="content-area">
                <div class="breadcrumb">
                    <a href="#">Home</a> / <a href="#">Dashboard</a> / <strong>All Short URLs</strong>
                </div>

                <h1>Dashboard</h1>

                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="showCreateModal()">+ Create Short URL</button>
                    <button class="btn btn-secondary">🔄 Bulk Actions</button>
                    <button class="btn btn-secondary">📥 Download QR Codes</button>
                </div>

                <div class="note">
                    <strong>Note:</strong> Date values are showing in <strong>UTC-06:00</strong> timezone.
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2>Your Short URL(s)</h2>
                        <button class="btn-filters">🔍 Filters</button>
                    </div>

                    <table class="url-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
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
                                <tr>
                                    <td colspan="9" class="empty-state">
                                        No URLs yet. Create your first short URL!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($urls as $index => $url): ?>
                                <tr>
                                    <td><input type="checkbox"></td>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($url['short_url']) ?>" target="_blank" class="short-link">
                                            <?= htmlspecialchars($url['short_url']) ?>
                                        </a>
                                        <div class="long-url"><?= htmlspecialchars($url['long_url']) ?></div>
                                        <div class="url-actions">
                                            <a href="?action=edit&id=<?= $url['id'] ?>">✏️ Edit</a>
                                            <a href="?action=copy&url=<?= urlencode($url['short_url']) ?>">📋 Copy</a>
                                        </div>
                                    </td>
                                    <td><?= number_format($url['clicks']) ?></td>
                                    <td>
                                        <a href="qr.php?id=<?= $url['id'] ?>">SVG</a> | 
                                        <a href="qr.php?id=<?= $url['id'] ?>&format=png">PNG</a>
                                    </td>
                                    <td><a href="analytics.php?id=<?= $url['id'] ?>" class="view-link">📊 View</a></td>
                                    <td>
                                        <?php if ($url['expires_at']): ?>
                                            <a href="#" class="expiry-link">Set Expiry</a><br>
                                            <small><?= date('M j, Y g:i a', strtotime($url['expires_at'])) ?></small>
                                        <?php else: ?>
                                            <a href="#" class="expiry-link">Set Expiry</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('M j, Y', strtotime($url['created_at'])) ?><br>
                                        <small><?= date('g:i a', strtotime($url['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="share-buttons">
                                            <button class="share-btn facebook">f</button>
                                            <button class="share-btn twitter">𝕏</button>
                                            <button class="share-btn more">⚫</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <footer class="footer">
                Powered by <strong>T2M URL Shortener</strong>
            </footer>
        </main>
    </div>

    <!-- Create URL Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCreateModal()">&times;</span>
            <h2>Create Short URL</h2>
            <form action="create.php" method="POST">
                <div class="form-group">
                    <label for="long_url">Long URL *</label>
                    <input type="url" id="long_url" name="long_url" required placeholder="https://example.com/very/long/url">
                </div>
                <div class="form-group">
                    <label for="custom_code">Custom Code (optional)</label>
                    <input type="text" id="custom_code" name="custom_code" placeholder="my-custom-code">
                    <small>Leave empty for random generation</small>
                </div>
                <button type="submit" class="btn btn-primary">Create Short URL</button>
            </form>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
