<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? '';

$dataFile = 'data/urls.json';
$urls = json_decode(file_get_contents($dataFile), true) ?: [];

$url = null;
foreach ($urls as $u) {
    if ($u['id'] === $id) {
        $url = $u;
        break;
    }
}

if (!$url) {
    die('URL not found');
}

$clickLog = $url['click_log'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - <?= htmlspecialchars($url['code']) ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .analytics-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #10b981;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .click-table {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="analytics-container">
        <a href="index.php">← Back to Dashboard</a>
        
        <h1>Analytics for <?= htmlspecialchars($url['short_url']) ?></h1>
        <p>Target: <?= htmlspecialchars($url['long_url']) ?></p>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= number_format($url['clicks']) ?></div>
                <div class="stat-label">Total Clicks</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $url['last_clicked'] ? date('M j, Y', strtotime($url['last_clicked'])) : 'Never' ?></div>
                <div class="stat-label">Last Clicked</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= date('M j, Y', strtotime($url['created_at'])) ?></div>
                <div class="stat-label">Created</div>
            </div>
        </div>
        
        <div class="click-table">
            <h2>Recent Clicks</h2>
            <table class="url-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Referer</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clickLog)): ?>
                        <tr><td colspan="3">No clicks yet</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice(array_reverse($clickLog), 0, 50) as $click): ?>
                            <tr>
                                <td><?= htmlspecialchars($click['timestamp']) ?></td>
                                <td><?= htmlspecialchars($click['referer'] ?: 'Direct') ?></td>
                                <td><?= htmlspecialchars(substr($click['user_agent'], 0, 100)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
