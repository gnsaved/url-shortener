<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    die('Unauthorized');
}

$id = $_GET['id'] ?? '';
$format = $_GET['format'] ?? 'svg';

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

// Generate QR code using Google Charts API (simple approach)
$shortUrl = urlencode($url['short_url']);
$size = '200x200';

if ($format === 'png') {
    header('Content-Type: image/png');
    $qrUrl = "https://chart.googleapis.com/chart?chs={$size}&cht=qr&chl={$shortUrl}&choe=UTF-8";
    readfile($qrUrl);
} else {
    // Simple SVG QR code placeholder
    header('Content-Type: image/svg+xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    ?>
    <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <rect width="200" height="200" fill="white"/>
        <text x="100" y="100" text-anchor="middle" font-size="12" fill="black">
            QR Code for:
        </text>
        <text x="100" y="120" text-anchor="middle" font-size="10" fill="gray">
            <?= htmlspecialchars($url['code']) ?>
        </text>
        <text x="100" y="140" text-anchor="middle" font-size="8" fill="blue">
            Use PNG format for actual QR
        </text>
    </svg>
    <?php
}
