<?php
/**
 * Setup script to set your access key
 * Run this once to set your key, then delete this file for security
 */

$configFile = __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key'])) {
    $key = trim($_POST['key']);
    if (strlen($key) < 10) {
        $error = 'Key must be at least 10 characters long';
    } else {
        $configContent = file_get_contents($configFile);
        
        // Replace the accessKey line
        $pattern = '/\$accessKey\s*=\s*[^;]+;/';
        $replacement = '$accessKey = \'' . addslashes($key) . '\';';
        $newContent = preg_replace($pattern, $replacement, $configContent);
        
        if (file_put_contents($configFile, $newContent)) {
            $success = true;
        } else {
            $error = 'Failed to write config file. Check permissions.';
        }
    }
}

// Read current key
$currentKey = 'Not set';
if (file_exists($configFile)) {
    $content = file_get_contents($configFile);
    if (preg_match('/\$accessKey\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        $currentKey = $matches[1];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - JSON Editor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .setup-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .success {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
        .current-key {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
            margin-bottom: 20px;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>🔧 Setup Access Key</h1>
        <p>Set your access key. Users will need to enter this exact key to access the editor.</p>
        
        <div class="current-key">
            <strong>Current Key:</strong><br>
            <?php echo htmlspecialchars($currentKey); ?>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success">
                <strong>Success!</strong> Your access key has been updated.
            </div>
            <div class="warning">
                <strong>⚠️ Security Warning:</strong> Delete this setup.php file after setup for security!
            </div>
            <a href="editor.php" class="btn" style="text-decoration: none; display: block; text-align: center; margin-top: 20px;">
                Go to Editor
            </a>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="key">Enter your access key (min 10 characters)</label>
                    <input type="text" id="key" name="key" required autofocus minlength="10" placeholder="Enter a long random key">
                </div>
                <button type="submit" class="btn">Update Access Key</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
