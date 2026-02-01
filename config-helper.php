<?php
/**
 * Configuration Helper
 * Auto-detects your installation path and generates correct config
 */

// Detect current setup
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$domain = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$scriptDir = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;

$baseUrl = $protocol . '://' . $domain . $scriptDir;
$isRoot = ($scriptDir === '' || $scriptDir === '/');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Configuration Helper</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d1fae5; border: 2px solid #10b981; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .code { background: #f0f0f0; padding: 15px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        .warning { background: #fef3c7; border: 2px solid #f59e0b; padding: 15px; border-radius: 8px; margin: 20px 0; }
        h1 { color: #10b981; }
        h2 { border-bottom: 2px solid #10b981; padding-bottom: 10px; }
        .step { background: white; border: 1px solid #e5e7eb; padding: 15px; margin: 10px 0; border-radius: 8px; }
    </style>
</head>
<body>
    <h1>🔧 URL Shortener Configuration</h1>
    
    <div class="success">
        <h2>✅ Auto-Detected Configuration</h2>
        <p><strong>Your Base URL:</strong> <code><?php echo htmlspecialchars($baseUrl); ?></code></p>
        <p><strong>Installation Type:</strong> <?php echo $isRoot ? 'Root Directory' : 'Subdirectory'; ?></p>
        <p><strong>Directory Path:</strong> <code><?php echo htmlspecialchars($scriptDir ? $scriptDir : '/'); ?></code></p>
    </div>

    <h2>📝 Required .htaccess Configuration</h2>
    
    <div class="step">
        <h3>Your .htaccess should contain:</h3>
        <div class="code">RewriteEngine On<br>
RewriteBase <?php echo htmlspecialchars($scriptDir ? $scriptDir : '/'); ?><br>
<br>
RewriteCond %{REQUEST_FILENAME} -f<br>
RewriteRule ^ - [L]<br>
<br>
RewriteCond %{REQUEST_FILENAME} -d<br>
RewriteRule ^ - [L]<br>
<br>
RewriteRule ^([a-zA-Z0-9_-]+)/?$ r.php?code=$1 [L,QSA]<br>
<br>
&lt;Files "urls.json"&gt;<br>
    Require all denied<br>
&lt;/Files&gt;</div>
    </div>

    <h2>🧪 Test Your Setup</h2>
    
    <div class="step">
        <h3>1. Test r.php directly:</h3>
        <p><a href="<?php echo $baseUrl; ?>/r.php?code=test" target="_blank">
            <?php echo htmlspecialchars($baseUrl); ?>/r.php?code=test
        </a></p>
        <p><small>Should show "Short URL not found" (that's good - means r.php works!)</small></p>
    </div>

    <div class="step">
        <h3>2. Example Short URLs will look like:</h3>
        
        <?php if (file_exists('.htaccess')): ?>
            <p><strong>With .htaccess (Clean URLs):</strong></p>
            <div class="code"><?php echo htmlspecialchars($baseUrl); ?>/abc123</div>
        <?php else: ?>
            <div class="warning">
                ⚠️ <strong>.htaccess not found!</strong><br>
                URLs will use the longer format below.
            </div>
        <?php endif; ?>
        
        <p><strong>Without .htaccess (Fallback):</strong></p>
        <div class="code"><?php echo htmlspecialchars($baseUrl); ?>/r.php?code=abc123</div>
    </div>

    <h2>🚀 Quick Setup Steps</h2>
    
    <div class="step">
        <h3>Step 1: Update .htaccess</h3>
        <ol>
            <li>Open your .htaccess file</li>
            <li>Change the <code>RewriteBase</code> line to: <br>
                <div class="code">RewriteBase <?php echo htmlspecialchars($scriptDir ? $scriptDir : '/'); ?></div>
            </li>
            <li>Save the file</li>
        </ol>
    </div>

    <div class="step">
        <h3>Step 2: Replace create.php</h3>
        <ol>
            <li>Download the new <code>create-auto-detect.php</code></li>
            <li>Rename it to <code>create.php</code></li>
            <li>Upload and replace your current create.php</li>
        </ol>
        <p><small>✅ This version auto-detects your base URL!</small></p>
    </div>

    <div class="step">
        <h3>Step 3: Test!</h3>
        <ol>
            <li>Go to your dashboard: <a href="index.php">index.php</a></li>
            <li>Create a new short URL</li>
            <li>Check that it generates the correct format</li>
            <li>Test the short URL works!</li>
        </ol>
    </div>

    <h2>📋 Current System Status</h2>
    
    <table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">
        <tr>
            <td><strong>.htaccess exists:</strong></td>
            <td><?php echo file_exists('.htaccess') ? '✅ Yes' : '❌ No'; ?></td>
        </tr>
        <tr>
            <td><strong>r.php exists:</strong></td>
            <td><?php echo file_exists('r.php') ? '✅ Yes' : '❌ No'; ?></td>
        </tr>
        <tr>
            <td><strong>create.php exists:</strong></td>
            <td><?php echo file_exists('create.php') ? '✅ Yes' : '❌ No'; ?></td>
        </tr>
        <tr>
            <td><strong>data directory:</strong></td>
            <td><?php echo file_exists('data') ? '✅ Yes' : '❌ No'; ?></td>
        </tr>
        <tr>
            <td><strong>urls.json:</strong></td>
            <td><?php echo file_exists('data/urls.json') ? '✅ Yes' : '❌ No'; ?></td>
        </tr>
    </table>

    <?php if (file_exists('data/urls.json')): ?>
        <?php
        $urls = json_decode(file_get_contents('data/urls.json'), true) ?: [];
        ?>
        <h2>📊 Your URLs (<?php echo count($urls); ?>)</h2>
        <?php if (!empty($urls)): ?>
            <div class="warning">
                <strong>⚠️ Important:</strong> Your existing URLs were created with the old format.<br>
                They still work, but new URLs will use the correct base URL automatically.
            </div>
            <table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%; margin-top: 10px;">
                <tr>
                    <th>Code</th>
                    <th>Current Short URL</th>
                    <th>Should Be</th>
                </tr>
                <?php foreach (array_slice($urls, 0, 5) as $url): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($url['code']); ?></code></td>
                    <td><?php echo htmlspecialchars($url['short_url']); ?></td>
                    <td style="background: #d1fae5;"><?php echo htmlspecialchars($baseUrl . '/' . $url['code']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <div class="success" style="margin-top: 30px;">
        <h3>✅ Summary</h3>
        <p>Your short URLs will now be:</p>
        <div class="code" style="font-size: 18px; font-weight: bold;">
            <?php echo htmlspecialchars($baseUrl); ?>/<strong>CODE</strong>
        </div>
        <p><small>Example: <?php echo htmlspecialchars($baseUrl); ?>/abc123</small></p>
    </div>

    <hr style="margin: 40px 0;">
    <p><a href="index.php">← Back to Dashboard</a></p>
</body>
</html>
