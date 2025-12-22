<?php
// DEBUG: Enable error reporting to diagnose 500 error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/config.php';

// Handle logout
if (isset($_GET['logout'])) {
    logout();
    header('Location: editor.php');
    exit;
}

// Handle login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captchaAnswer = $_POST['captcha_answer'] ?? '';
    $captchaValue = $_POST['captcha_value'] ?? '';
    
    if (empty($captchaAnswer) || intval($captchaAnswer) !== intval($captchaValue)) {
        $error = 'Incorrect security question answer.';
    } elseif (login($username, $password)) {
        header('Location: editor.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

// Check authentication
if (!isAuthenticated()) {
    // Generate new captcha
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $captchaValue = $num1 + $num2;
    
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>JSON Editor - Login</title>
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
            .login-container {
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                padding: 40px;
                max-width: 400px;
                width: 100%;
            }
            .login-container h1 {
                color: #333;
                margin-bottom: 10px;
                font-size: 28px;
            }
            .login-container p {
                color: #666;
                margin-bottom: 30px;
                font-size: 14px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 8px;
                color: #333;
                font-weight: 500;
            }
            .form-group input {
                width: 100%;
                padding: 12px;
                border: 2px solid #e0e0e0;
                border-radius: 6px;
                font-size: 16px;
                transition: border-color 0.3s;
            }
            .form-group input:focus {
                outline: none;
                border-color: #667eea;
            }
            .captcha-group label span {
                color: #667eea;
                font-weight: 700;
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
                transition: transform 0.2s, box-shadow 0.2s;
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
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>🔐 Editor Login</h1>
            <p>Enter your credentials to continue</p>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="captcha_value" value="<?php echo $captchaValue; ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group captcha-group">
                    <label for="captcha">Security Question: <span><?php echo $num1; ?> + <?php echo $num2; ?> = ?</span></label>
                    <input type="number" id="captcha" name="captcha_answer" required placeholder="Your answer">
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// User is authenticated - show editor
if (!file_exists(DATA_FILE)) {
    die('Error: data.json file not found at ' . htmlspecialchars(DATA_FILE));
}

$jsonData = file_get_contents(DATA_FILE);
if ($jsonData === false) {
    die('Error: Unable to read data.json file');
}

$jsonArray = json_decode($jsonData, true);

if ($jsonArray === null && json_last_error() !== JSON_ERROR_NONE) {
    die('Error: Invalid JSON in data.json: ' . json_last_error_msg());
}

// Ensure array structure exists to prevent errors
$jsonArray = $jsonArray ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON Editor - Arinsol.ai</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
        }
        .header-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: white;
            color: #667eea;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.3);
        }
        .btn-success {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            font-size: 16px;
        }
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px 100px 20px; /* Added bottom padding for sticky footer */
        }
        .form-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 15px; /* Reduced margin */
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .section-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef; /* Thinner border */
            cursor: pointer;
            transition: background 0.2s;
        }
        .section-header:hover {
            background: #e9ecef;
        }
        .section-header h2 {
            font-size: 18px; /* Slightly smaller */
            color: #333;
            display: flex;
            align-items: center;
            justify-content: space-between; /* Push arrow to right */
            gap: 10px;
            margin: 0;
        }
        .section-header h2::after {
            content: '▼';
            font-size: 12px;
            color: #666;
            transition: transform 0.3s ease;
        }
        .form-section.active .section-header h2::after {
            transform: rotate(180deg);
        }
        .section-content {
            padding: 30px;
            display: none; /* Collapsed by default */
        }
        .form-section.active .section-content {
            display: block; /* Expanded when active */
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .array-item {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        .array-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .array-item-title {
            font-weight: 600;
            color: #333;
        }
        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-remove:hover {
            background: #c82333;
        }
        .btn-add {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
        }
        .btn-add:hover {
            background: #218838;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        .image-upload-wrapper {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            background: #f8f9fa;
        }
        .image-preview-container {
            margin-bottom: 15px;
            text-align: center;
        }
        .image-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .image-placeholder {
            padding: 40px;
            background: #e9ecef;
            border-radius: 6px;
            color: #6c757d;
            font-size: 14px;
        }
        .image-upload-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .image-file-input {
            display: none;
        }
        .image-upload-controls .btn {
            padding: 8px 16px;
            font-size: 14px;
        }
        .footer-actions {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
            padding: 15px;
            text-align: center;
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }
        .status-message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }
        .status-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📝 Content Editor - Arinsol.ai</h1>
            <div class="header-actions">
                <a href="/" class="btn btn-secondary" target="_blank">View Site</a>
                <a href="?logout=1" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div id="status-message" class="status-message"></div>

        <form id="editor-form">
            <!-- Site Meta -->
            <div class="form-section">
                <div class="section-header">
                    <h2>🌐 Site Meta</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="siteMeta_title">Site Title</label>
                        <input type="text" id="siteMeta_title" name="siteMeta[title]" value="<?php echo htmlspecialchars($jsonArray['siteMeta']['title'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div class="form-section">
                <div class="section-header">
                    <h2>📋 Header</h2>
                </div>
                <div class="section-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="header_logoText">Logo Text</label>
                            <input type="text" id="header_logoText" name="header[logoText]" value="<?php echo htmlspecialchars($jsonArray['header']['logoText'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="header_ctaText">CTA Button Text</label>
                            <input type="text" id="header_ctaText" name="header[ctaText]" value="<?php echo htmlspecialchars($jsonArray['header']['ctaText'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Navigation Links</label>
                        <div id="navLinks-container">
                            <?php foreach ($jsonArray['header']['navLinks'] ?? [] as $index => $link): ?>
                                <div class="array-item" data-index="<?php echo $index; ?>">
                                    <div class="array-item-header">
                                        <span class="array-item-title">Link #<?php echo $index + 1; ?></span>
                                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Link Text</label>
                                            <input type="text" name="header[navLinks][<?php echo $index; ?>][text]" value="<?php echo htmlspecialchars($link['text'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Link URL</label>
                                            <input type="text" name="header[navLinks][<?php echo $index; ?>][href]" value="<?php echo htmlspecialchars($link['href'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addNavLink()">+ Add Navigation Link</button>
                    </div>
                </div>
            </div>

            <!-- Hero -->
            <div class="form-section">
                <div class="section-header">
                    <h2>🎯 Hero Section</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="hero_badge">Badge Text</label>
                        <input type="text" id="hero_badge" name="hero[badge]" value="<?php echo htmlspecialchars($jsonArray['hero']['badge'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="hero_title">Title</label>
                        <input type="text" id="hero_title" name="hero[title]" value="<?php echo htmlspecialchars($jsonArray['hero']['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="hero_description">Description</label>
                        <textarea id="hero_description" name="hero[description]"><?php echo htmlspecialchars($jsonArray['hero']['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="hero_ctaText">CTA Button Text</label>
                            <input type="text" id="hero_ctaText" name="hero[ctaText]" value="<?php echo htmlspecialchars($jsonArray['hero']['ctaText'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="hero_ctaLink">CTA Link</label>
                            <input type="text" id="hero_ctaLink" name="hero[ctaLink]" value="<?php echo htmlspecialchars($jsonArray['hero']['ctaLink'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- About -->
            <div class="form-section">
                <div class="section-header">
                    <h2>ℹ️ About ArInSol</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="about_title">Section Title</label>
                        <input type="text" id="about_title" name="about[title]" value="<?php echo htmlspecialchars($jsonArray['about']['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="about_description">Description (Paragraphs separated by blank lines)</label>
                        <textarea id="about_description" name="about[description]" style="min-height: 200px;"><?php echo htmlspecialchars($jsonArray['about']['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="about_principlesTitle">Principles Title</label>
                        <input type="text" id="about_principlesTitle" name="about[principlesTitle]" value="<?php echo htmlspecialchars($jsonArray['about']['principlesTitle'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Principles List</label>
                        <div id="about-principles-container">
                            <?php foreach ($jsonArray['about']['principles'] ?? [] as $index => $principle): ?>
                                <div class="array-item" data-index="<?php echo $index; ?>">
                                    <div class="array-item-header">
                                        <span class="array-item-title">Principle #<?php echo $index + 1; ?></span>
                                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="about[principles][<?php echo $index; ?>]" value="<?php echo htmlspecialchars($principle); ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addPrinciple()">+ Add Principle</button>
                    </div>
                </div>
            </div>

            <!-- Services -->
            <div class="form-section">
                <div class="section-header">
                    <h2>⚙️ Services</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="services_title">Services Title</label>
                        <input type="text" id="services_title" name="services[title]" value="<?php echo htmlspecialchars($jsonArray['services']['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Service Items</label>
                        <div id="services-container">
                            <?php foreach ($jsonArray['services']['items'] ?? [] as $index => $item): ?>
                                <div class="array-item" data-index="<?php echo $index; ?>">
                                    <div class="array-item-header">
                                        <span class="array-item-title">Service #<?php echo $index + 1; ?></span>
                                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                                    </div>
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="services[items][<?php echo $index; ?>][title]" value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="services[items][<?php echo $index; ?>][description]"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Icon Class (Font Awesome)</label>
                                            <input type="text" name="services[items][<?php echo $index; ?>][iconClass]" value="<?php echo htmlspecialchars($item['iconClass'] ?? ''); ?>" placeholder="fas fa-mobile-alt">
                                        </div>
                                        <div class="form-group">
                                            <label>Color Class</label>
                                            <input type="text" name="services[items][<?php echo $index; ?>][colorClass]" value="<?php echo htmlspecialchars($item['colorClass'] ?? ''); ?>" placeholder="green">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addService()">+ Add Service</button>
                    </div>
                </div>
            </div>

            <!-- Industries -->
            <div class="form-section">
                <div class="section-header">
                    <h2>🏭 Industries</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="industries_title">Industries Title</label>
                        <input type="text" id="industries_title" name="industries[title]" value="<?php echo htmlspecialchars($jsonArray['industries']['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="industries_subText">Sub Text</label>
                        <textarea id="industries_subText" name="industries[subText]"><?php echo htmlspecialchars($jsonArray['industries']['subText'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Industry Items</label>
                        <div id="industries-container">
                            <?php foreach ($jsonArray['industries']['items'] ?? [] as $index => $item): ?>
                                <div class="array-item" data-index="<?php echo $index; ?>">
                                    <div class="array-item-header">
                                        <span class="array-item-title">Industry #<?php echo $index + 1; ?></span>
                                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" name="industries[items][<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Icon Class</label>
                                            <input type="text" name="industries[items][<?php echo $index; ?>][iconClass]" value="<?php echo htmlspecialchars($item['iconClass'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="industries[items][<?php echo $index; ?>][description]" rows="3"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Best Fit</label>
                                        <input type="text" name="industries[items][<?php echo $index; ?>][bestFit]" value="<?php echo htmlspecialchars($item['bestFit'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Color (Hex)</label>
                                        <input type="text" name="industries[items][<?php echo $index; ?>][color]" value="<?php echo htmlspecialchars($item['color'] ?? ''); ?>" placeholder="#e74c3c">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addIndustry()">+ Add Industry</button>
                    </div>
                </div>
            </div>

            <!-- Trust -->
            <div class="form-section">
                <div class="section-header">
                    <h2>🛡️ Trust & Governance</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="trust_title">Title</label>
                        <input type="text" id="trust_title" name="trust[title]" value="<?php echo htmlspecialchars($jsonArray['trust']['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Trust Items</label>
                        <div id="trust-container">
                            <?php foreach ($jsonArray['trust']['items'] ?? [] as $index => $item): ?>
                                <div class="array-item" data-index="<?php echo $index; ?>">
                                    <div class="array-item-header">
                                        <span class="array-item-title">Item #<?php echo $index + 1; ?></span>
                                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="trust[items][<?php echo $index; ?>]" value="<?php echo htmlspecialchars($item); ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addTrustItem()">+ Add Item</button>
                    </div>
                </div>
            </div>

            <!-- Engagement -->
            <div class="form-section">
                <div class="section-header">
                    <h2>🚀 Engagement Block</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="eng_title">Title</label>
                        <input type="text" id="eng_title" name="engagement[title]" value="<?php echo htmlspecialchars($jsonArray['engagement']['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="eng_subText">Sub Text</label>
                        <input type="text" id="eng_subText" name="engagement[subText]" value="<?php echo htmlspecialchars($jsonArray['engagement']['subText'] ?? ''); ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="eng_ctaText">CTA Text</label>
                            <input type="text" id="eng_ctaText" name="engagement[ctaText]" value="<?php echo htmlspecialchars($jsonArray['engagement']['ctaText'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="eng_ctaLink">CTA Link</label>
                            <input type="text" id="eng_ctaLink" name="engagement[ctaLink]" value="<?php echo htmlspecialchars($jsonArray['engagement']['ctaLink'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Engagement Tiles</label>
                        <div id="eng-container">
                            <?php foreach ($jsonArray['engagement']['items'] ?? [] as $index => $item): ?>
                                <div class="array-item" data-index="<?php echo $index; ?>">
                                    <div class="array-item-header">
                                        <span class="array-item-title">Tile #<?php echo $index + 1; ?></span>
                                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                                    </div>
                                    <div class="form-group">
                                        <label>Text</label>
                                        <input type="text" name="engagement[items][<?php echo $index; ?>][text]" value="<?php echo htmlspecialchars($item['text'] ?? ''); ?>">
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Link Text</label>
                                            <input type="text" name="engagement[items][<?php echo $index; ?>][linkText]" value="<?php echo htmlspecialchars($item['linkText'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Link URL</label>
                                            <input type="text" name="engagement[items][<?php echo $index; ?>][link]" value="<?php echo htmlspecialchars($item['link'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addEngItem()">+ Add Tile</button>
                    </div>
                </div>
            </div>

            <!-- Images Management -->
            <div class="form-section">
                <div class="section-header">
                    <h2>🖼️ Image Management</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>Logo Image</label>
                        <div class="image-upload-wrapper">
                            <input type="hidden" id="logo_image" value="logo.png">
                            <div class="image-preview-container" id="logo_preview">
                                <?php if (file_exists('assets/logo.png')): ?>
                                    <img src="assets/logo.png" alt="Logo" class="image-preview">
                                <?php else: ?>
                                    <div class="image-placeholder">No image</div>
                                <?php endif; ?>
                            </div>
                            <div class="image-upload-controls">
                                <input type="file" accept="image/*" class="image-file-input" data-target="logo_image" data-preview="logo_preview" data-filename="logo.png" onchange="handleImageUpload(this, 'logo.png')">
                                <button type="button" class="btn btn-primary" onclick="this.previousElementSibling.click()">Upload Logo</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Software Image 1</label>
                        <div class="image-upload-wrapper">
                            <input type="hidden" id="software1_image" value="software1.jpg">
                            <div class="image-preview-container" id="software1_preview">
                                <?php if (file_exists('assets/software1.jpg')): ?>
                                    <img src="assets/software1.jpg" alt="Software 1" class="image-preview">
                                <?php else: ?>
                                    <div class="image-placeholder">No image</div>
                                <?php endif; ?>
                            </div>
                            <div class="image-upload-controls">
                                <input type="file" accept="image/*" class="image-file-input" data-target="software1_image" data-preview="software1_preview" data-filename="software1.jpg" onchange="handleImageUpload(this, 'software1.jpg')">
                                <button type="button" class="btn btn-primary" onclick="this.previousElementSibling.click()">Upload Software 1</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Software Image 2</label>
                        <div class="image-upload-wrapper">
                            <input type="hidden" id="software2_image" value="software2.jpg">
                            <div class="image-preview-container" id="software2_preview">
                                <?php if (file_exists('assets/software2.jpg')): ?>
                                    <img src="assets/software2.jpg" alt="Software 2" class="image-preview">
                                <?php else: ?>
                                    <div class="image-placeholder">No image</div>
                                <?php endif; ?>
                            </div>
                            <div class="image-upload-controls">
                                <input type="file" accept="image/*" class="image-file-input" data-target="software2_image" data-preview="software2_preview" data-filename="software2.jpg" onchange="handleImageUpload(this, 'software2.jpg')">
                                <button type="button" class="btn btn-primary" onclick="this.previousElementSibling.click()">Upload Software 2</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Case Studies -->
            <div class="form-section">
                <div class="section-header">
                    <h2>📚 Case Studies</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="caseStudies_title">Case Studies Title</label>
                        <input type="text" id="caseStudies_title" name="caseStudies[title]" value="<?php echo htmlspecialchars($jsonArray['caseStudies']['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="caseStudies_subText">Sub Text</label>
                        <textarea id="caseStudies_subText" name="caseStudies[subText]"><?php echo htmlspecialchars($jsonArray['caseStudies']['subText'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Case Study Items</label>
                        <div id="caseStudies-container">
                            <?php foreach ($jsonArray['caseStudies']['items'] ?? [] as $index => $item): ?>
                                <div class="array-item" data-index="<?php echo $index; ?>">
                                    <div class="array-item-header">
                                        <span class="array-item-title">Case Study #<?php echo $index + 1; ?></span>
                                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                                    </div>
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="caseStudies[items][<?php echo $index; ?>][title]" value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="caseStudies[items][<?php echo $index; ?>][description]"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>CTA Text</label>
                                            <input type="text" name="caseStudies[items][<?php echo $index; ?>][ctaText]" value="<?php echo htmlspecialchars($item['ctaText'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Icon Class</label>
                                            <input type="text" name="caseStudies[items][<?php echo $index; ?>][iconClass]" value="<?php echo htmlspecialchars($item['iconClass'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Preview Text</label>
                                        <input type="text" name="caseStudies[items][<?php echo $index; ?>][previewText]" value="<?php echo htmlspecialchars($item['previewText'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Key Features</label>
                                        <div id="cs-features-container-<?php echo $index; ?>">
                                            <?php foreach ($item['features'] ?? [] as $fIndex => $feature): ?>
                                                <div class="array-item feature-item" style="padding: 10px; margin-bottom: 10px; background: #fff;">
                                                    <div class="form-row" style="margin-bottom: 0; display: flex; align-items: flex-start; gap: 10px;">
                                                        <textarea name="caseStudies[items][<?php echo $index; ?>][features][<?php echo $fIndex; ?>]" rows="2" style="flex: 1;"><?php echo htmlspecialchars($feature); ?></textarea>
                                                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">×</button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn-add" style="padding: 6px 12px; font-size: 13px;" onclick="addCaseStudyFeature(<?php echo $index; ?>)">+ Add Feature</button>
                                    </div>
                                    <div class="form-group">
                                        <label>Image File</label>
                                        <div class="image-upload-wrapper">
                                            <input type="hidden" name="caseStudies[items][<?php echo $index; ?>][image]" id="caseStudy_image_<?php echo $index; ?>" value="<?php echo htmlspecialchars($item['image'] ?? ''); ?>">
                                            <div class="image-preview-container" id="caseStudy_preview_<?php echo $index; ?>">
                                                <?php 
                                                $currentImage = $item['image'] ?? '';
                                                if ($currentImage): 
                                                    $imagePath = 'assets/' . $currentImage;
                                                    if (file_exists($imagePath)):
                                                ?>
                                                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Preview" class="image-preview">
                                                <?php else: ?>
                                                    <div class="image-placeholder">No image</div>
                                                <?php endif; else: ?>
                                                    <div class="image-placeholder">No image</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="image-upload-controls">
                                                <input type="file" accept="image/*" class="image-file-input" data-target="caseStudy_image_<?php echo $index; ?>" data-preview="caseStudy_preview_<?php echo $index; ?>" onchange="handleImageUpload(this)">
                                                <button type="button" class="btn btn-primary" onclick="this.previousElementSibling.click()">Choose Image</button>
                                                <button type="button" class="btn btn-danger" onclick="removeImage('caseStudy_image_<?php echo $index; ?>', 'caseStudy_preview_<?php echo $index; ?>')">Remove</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="checkbox-group">
                                            <input type="checkbox" name="caseStudies[items][<?php echo $index; ?>][reverseLayout]" value="1" <?php echo ($item['reverseLayout'] ?? false) ? 'checked' : ''; ?>>
                                            <label>Reverse Layout</label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addCaseStudy()">+ Add Case Study</button>
                    </div>
                </div>
            </div>

            <!-- FAQ -->
            <div class="form-section">
                <div class="section-header">
                    <h2>❓ FAQ</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="faq_title">FAQ Title</label>
                        <input type="text" id="faq_title" name="faq[title]" value="<?php echo htmlspecialchars($jsonArray['faq']['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="faq_subText">Sub Text</label>
                        <textarea id="faq_subText" name="faq[subText]"><?php echo htmlspecialchars($jsonArray['faq']['subText'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>FAQ Items</label>
                        <div id="faq-container">
                            <?php foreach ($jsonArray['faq']['items'] ?? [] as $index => $item): ?>
                                <div class="array-item" data-index="<?php echo $index; ?>">
                                    <div class="array-item-header">
                                        <span class="array-item-title">Item #<?php echo $index + 1; ?></span>
                                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                                    </div>
                                    <div class="form-group">
                                        <label>Question</label>
                                        <input type="text" name="faq[items][<?php echo $index; ?>][question]" value="<?php echo htmlspecialchars($item['question'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Answer (Use newlines for paragraphs, '•' for bullets)</label>
                                        <textarea name="faq[items][<?php echo $index; ?>][answer]" style="min-height: 150px;"><?php echo htmlspecialchars($item['answer'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addFaqItem()">+ Add FAQ Item</button>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <div class="form-section">
                <div class="section-header">
                    <h2>📞 Contact</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="contact_title">Contact Title</label>
                        <input type="text" id="contact_title" name="contact[title]" value="<?php echo htmlspecialchars($jsonArray['contact']['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="contact_subText">Sub Text</label>
                        <textarea id="contact_subText" name="contact[subText]"><?php echo htmlspecialchars($jsonArray['contact']['subText'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_yearsExp">Years Experience</label>
                            <input type="text" id="contact_yearsExp" name="contact[yearsExp]" value="<?php echo htmlspecialchars($jsonArray['contact']['yearsExp'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="contact_yearsLabel">Years Label</label>
                            <input type="text" id="contact_yearsLabel" name="contact[yearsLabel]" value="<?php echo htmlspecialchars($jsonArray['contact']['yearsLabel'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_successTitle">Success Title</label>
                            <input type="text" id="contact_successTitle" name="contact[successTitle]" value="<?php echo htmlspecialchars($jsonArray['contact']['successTitle'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="contact_successSubtitle">Success Subtitle</label>
                            <input type="text" id="contact_successSubtitle" name="contact[successSubtitle]" value="<?php echo htmlspecialchars($jsonArray['contact']['successSubtitle'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Stats</label>
                        <div id="stats-container">
                            <?php foreach ($jsonArray['contact']['stats'] ?? [] as $index => $stat): ?>
                                <div class="array-item" data-index="<?php echo $index; ?>">
                                    <div class="array-item-header">
                                        <span class="array-item-title">Stat #<?php echo $index + 1; ?></span>
                                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Number</label>
                                            <input type="text" name="contact[stats][<?php echo $index; ?>][number]" value="<?php echo htmlspecialchars($stat['number'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Label</label>
                                            <input type="text" name="contact[stats][<?php echo $index; ?>][label]" value="<?php echo htmlspecialchars($stat['label'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addStat()">+ Add Stat</button>
                    </div>
                    <div class="form-group">
                        <label>Form Labels</label>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Name Label</label>
                                <input type="text" name="contact[formLabels][name]" value="<?php echo htmlspecialchars($jsonArray['contact']['formLabels']['name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Email Label</label>
                                <input type="text" name="contact[formLabels][email]" value="<?php echo htmlspecialchars($jsonArray['contact']['formLabels']['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone Label</label>
                                <input type="text" name="contact[formLabels][phone]" value="<?php echo htmlspecialchars($jsonArray['contact']['formLabels']['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Skype Label</label>
                                <input type="text" name="contact[formLabels][skype]" value="<?php echo htmlspecialchars($jsonArray['contact']['formLabels']['skype'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Project Description Label</label>
                            <input type="text" name="contact[formLabels][projectDesc]" value="<?php echo htmlspecialchars($jsonArray['contact']['formLabels']['projectDesc'] ?? ''); ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Upload Label</label>
                                <input type="text" name="contact[formLabels][upload]" value="<?php echo htmlspecialchars($jsonArray['contact']['formLabels']['upload'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Choose File Label</label>
                                <input type="text" name="contact[formLabels][chooseFile]" value="<?php echo htmlspecialchars($jsonArray['contact']['formLabels']['chooseFile'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>No File Label</label>
                                <input type="text" name="contact[formLabels][noFile]" value="<?php echo htmlspecialchars($jsonArray['contact']['formLabels']['noFile'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Submit Label</label>
                                <input type="text" name="contact[formLabels][submit]" value="<?php echo htmlspecialchars($jsonArray['contact']['formLabels']['submit'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="form-section">
                <div class="section-header">
                    <h2>📱 Social Media</h2>
                </div>
                <div class="section-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Facebook URL</label>
                            <input type="text" name="socialMedia[facebook]" value="<?php echo htmlspecialchars($jsonArray['socialMedia']['facebook'] ?? ''); ?>" placeholder="https://facebook.com/...">
                        </div>
                        <div class="form-group">
                            <label>Twitter URL</label>
                            <input type="text" name="socialMedia[twitter]" value="<?php echo htmlspecialchars($jsonArray['socialMedia']['twitter'] ?? ''); ?>" placeholder="https://twitter.com/...">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>LinkedIn URL</label>
                            <input type="text" name="socialMedia[linkedin]" value="<?php echo htmlspecialchars($jsonArray['socialMedia']['linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/...">
                        </div>
                        <div class="form-group">
                            <label>Instagram URL</label>
                            <input type="text" name="socialMedia[instagram]" value="<?php echo htmlspecialchars($jsonArray['socialMedia']['instagram'] ?? ''); ?>" placeholder="https://instagram.com/...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="form-section">
                <div class="section-header">
                    <h2>📍 Location & Map</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="location[address]" value="<?php echo htmlspecialchars($jsonArray['location']['address'] ?? ''); ?>" placeholder="123 Tech Street">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="location[city]" value="<?php echo htmlspecialchars($jsonArray['location']['city'] ?? ''); ?>" placeholder="New York">
                        </div>
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" name="location[country]" value="<?php echo htmlspecialchars($jsonArray['location']['country'] ?? ''); ?>" placeholder="USA">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Latitude</label>
                            <input type="text" name="location[latitude]" value="<?php echo htmlspecialchars($jsonArray['location']['latitude'] ?? ''); ?>" placeholder="40.7128">
                        </div>
                        <div class="form-group">
                            <label>Longitude</label>
                            <input type="text" name="location[longitude]" value="<?php echo htmlspecialchars($jsonArray['location']['longitude'] ?? ''); ?>" placeholder="-74.0060">
                        </div>
                    </div>
                    <p style="font-size: 13px; color: #666; margin-top: 10px; background: #fff3cd; padding: 10px; border-radius: 4px; border: 1px solid #ffeeba;">
                        💡 <strong>Tip:</strong> For the most accurate map, right-click a place on Google Maps, select the coordinates (e.g., 40.7128, -74.0060), and paste them above.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="form-section">
                <div class="section-header">
                    <h2>📄 Footer</h2>
                </div>
                <div class="section-content">
                    <div class="form-group">
                        <label for="footer_copyright">Copyright Text</label>
                        <input type="text" id="footer_copyright" name="footer[copyright]" value="<?php echo htmlspecialchars($jsonArray['footer']['copyright'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="footer-actions">
                <button type="submit" class="btn btn-success">💾 Save All Changes</button>
            </div>
        </form>
    </div>

    <script>
        // Accordion functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sectionHeaders = document.querySelectorAll('.section-header');
            
            sectionHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const section = this.parentElement;
                    
                    // Close all other sections
                    document.querySelectorAll('.form-section').forEach(s => {
                        if (s !== section) {
                            s.classList.remove('active');
                        }
                    });

                    // Toggle current section
                    section.classList.toggle('active');

                    // Scroll to section if it's being opened
                    if (section.classList.contains('active')) {
                        setTimeout(() => {
                            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 300);
                    }
                });
            });

            // Open first section by default
            // const firstSection = document.querySelector('.form-section');
            // if (firstSection) firstSection.classList.add('active');
        });

        let navLinkIndex = <?php echo count($jsonArray['header']['navLinks'] ?? []); ?>;
        let aboutPrincipleIndex = <?php echo count($jsonArray['about']['principles'] ?? []); ?>;
        let serviceIndex = <?php echo count($jsonArray['services']['items'] ?? []); ?>;
        let industryIndex = <?php echo count($jsonArray['industries']['items'] ?? []); ?>;
        let trustIndex = <?php echo count($jsonArray['trust']['items'] ?? []); ?>;
        let engIndex = <?php echo count($jsonArray['engagement']['items'] ?? []); ?>;
        let caseStudyIndex = <?php echo count($jsonArray['caseStudies']['items'] ?? []); ?>;
        let statIndex = <?php echo count($jsonArray['contact']['stats'] ?? []); ?>;
        let faqIndex = <?php echo count($jsonArray['faq']['items'] ?? []); ?>;

        function removeArrayItem(btn) {
            if (confirm('Are you sure you want to remove this item?')) {
                btn.closest('.array-item').remove();
            }
        }

        function addNavLink() {
            const container = document.getElementById('navLinks-container');
            const item = document.createElement('div');
            item.className = 'array-item';
            item.innerHTML = `
                <div class="array-item-header">
                    <span class="array-item-title">Link #${navLinkIndex + 1}</span>
                    <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Link Text</label>
                        <input type="text" name="header[navLinks][${navLinkIndex}][text]" value="">
                    </div>
                    <div class="form-group">
                        <label>Link URL</label>
                        <input type="text" name="header[navLinks][${navLinkIndex}][href]" value="">
                    </div>
                </div>
            `;
            container.appendChild(item);
            navLinkIndex++;
        }

        function addPrinciple() {
            const container = document.getElementById('about-principles-container');
            const item = document.createElement('div');
            item.className = 'array-item';
            item.innerHTML = `
                <div class="array-item-header">
                    <span class="array-item-title">Principle #${aboutPrincipleIndex + 1}</span>
                    <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                </div>
                <div class="form-group">
                    <input type="text" name="about[principles][${aboutPrincipleIndex}]" value="">
                </div>
            `;
            container.appendChild(item);
            aboutPrincipleIndex++;
        }

        function addService() {
            const container = document.getElementById('services-container');
            const item = document.createElement('div');
            item.className = 'array-item';
            item.innerHTML = `
                <div class="array-item-header">
                    <span class="array-item-title">Service #${serviceIndex + 1}</span>
                    <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                </div>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="services[items][${serviceIndex}][title]" value="">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="services[items][${serviceIndex}][description]"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Icon Class (Font Awesome)</label>
                        <input type="text" name="services[items][${serviceIndex}][iconClass]" value="" placeholder="fas fa-mobile-alt">
                    </div>
                    <div class="form-group">
                        <label>Color Class</label>
                        <input type="text" name="services[items][${serviceIndex}][colorClass]" value="" placeholder="green">
                    </div>
                </div>
            `;
            container.appendChild(item);
            serviceIndex++;
        }

        function addIndustry() {
            const container = document.getElementById('industries-container');
            const item = document.createElement('div');
            item.className = 'array-item';
            item.innerHTML = `
                <div class="array-item-header">
                    <span class="array-item-title">Industry #${industryIndex + 1}</span>
                    <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="industries[items][${industryIndex}][name]" value="">
                    </div>
                    <div class="form-group">
                        <label>Icon Class</label>
                        <input type="text" name="industries[items][${industryIndex}][iconClass]" value="">
                    </div>
                </div>
                <div class="form-group">
                    <label>Color (Hex)</label>
                    <input type="text" name="industries[items][${industryIndex}][color]" value="" placeholder="#e74c3c">
                </div>
            `;
            container.appendChild(item);
            industryIndex++;
        }

        function addCaseStudyFeature(csIndex) {
            const container = document.getElementById(`cs-features-container-${csIndex}`);
            const featureIndex = container.children.length;
            const item = document.createElement('div');
            item.className = 'array-item feature-item';
            item.style.cssText = 'padding: 10px; margin-bottom: 10px; background: #fff;';
            
            item.innerHTML = `
                <div class="form-row" style="margin-bottom: 0; display: flex; align-items: flex-start; gap: 10px;">
                    <textarea name="caseStudies[items][${csIndex}][features][${featureIndex}]" rows="2" style="flex: 1;"></textarea>
                    <button type="button" class="btn-remove" onclick="removeArrayItem(this)">×</button>
                </div>
            `;
            container.appendChild(item);
        }

        function addTrustItem() {
            const container = document.getElementById('trust-container');
            const item = document.createElement('div');
            item.className = 'array-item';
            item.innerHTML = `
                <div class="array-item-header">
                    <span class="array-item-title">Item #${trustIndex + 1}</span>
                    <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                </div>
                <div class="form-group">
                    <input type="text" name="trust[items][${trustIndex}]" value="">
                </div>
            `;
            container.appendChild(item);
            trustIndex++;
        }

        function addEngItem() {
            const container = document.getElementById('eng-container');
            const item = document.createElement('div');
            item.className = 'array-item';
            item.innerHTML = `
                <div class="array-item-header">
                    <span class="array-item-title">Tile #${engIndex + 1}</span>
                    <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                </div>
                <div class="form-group">
                    <label>Text</label>
                    <input type="text" name="engagement[items][${engIndex}][text]" value="">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Link Text</label>
                        <input type="text" name="engagement[items][${engIndex}][linkText]" value="">
                    </div>
                    <div class="form-group">
                        <label>Link URL</label>
                        <input type="text" name="engagement[items][${engIndex}][link]" value="">
                    </div>
                </div>
            `;
            container.appendChild(item);
            engIndex++;
        }

        function addCaseStudy() {
            const container = document.getElementById('caseStudies-container');
            const item = document.createElement('div');
            item.className = 'array-item';
            const imageId = `caseStudy_image_${caseStudyIndex}`;
            const previewId = `caseStudy_preview_${caseStudyIndex}`;
            item.innerHTML = `
                <div class="array-item-header">
                    <span class="array-item-title">Case Study #${caseStudyIndex + 1}</span>
                    <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                </div>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="caseStudies[items][${caseStudyIndex}][title]" value="">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="caseStudies[items][${caseStudyIndex}][description]"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>CTA Text</label>
                        <input type="text" name="caseStudies[items][${caseStudyIndex}][ctaText]" value="">
                    </div>
                    <div class="form-group">
                        <label>Icon Class</label>
                        <input type="text" name="caseStudies[items][${caseStudyIndex}][iconClass]" value="">
                    </div>
                </div>
                <div class="form-group">
                    <label>Preview Text</label>
                    <input type="text" name="caseStudies[items][${caseStudyIndex}][previewText]" value="">
                </div>
                <div class="form-group">
                    <label>Key Features</label>
                    <div id="cs-features-container-${caseStudyIndex}"></div>
                    <button type="button" class="btn-add" style="padding: 6px 12px; font-size: 13px;" onclick="addCaseStudyFeature(${caseStudyIndex})">+ Add Feature</button>
                </div>
                <div class="form-group">
                    <label>Image File</label>
                    <div class="image-upload-wrapper">
                        <input type="hidden" name="caseStudies[items][${caseStudyIndex}][image]" id="${imageId}" value="">
                        <div class="image-preview-container" id="${previewId}">
                            <div class="image-placeholder">No image</div>
                        </div>
                        <div class="image-upload-controls">
                            <input type="file" accept="image/*" class="image-file-input" data-target="${imageId}" data-preview="${previewId}" onchange="handleImageUpload(this)">
                            <button type="button" class="btn btn-primary" onclick="this.previousElementSibling.click()">Choose Image</button>
                            <button type="button" class="btn btn-danger" onclick="removeImage('${imageId}', '${previewId}')">Remove</button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="caseStudies[items][${caseStudyIndex}][reverseLayout]" value="1">
                        <label>Reverse Layout</label>
                    </div>
                </div>
            `;
            container.appendChild(item);
            caseStudyIndex++;
        }

        function addStat() {
            const container = document.getElementById('stats-container');
            const item = document.createElement('div');
            item.className = 'array-item';
            item.innerHTML = `
                <div class="array-item-header">
                    <span class="array-item-title">Stat #${statIndex + 1}</span>
                    <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Number</label>
                        <input type="text" name="contact[stats][${statIndex}][number]" value="">
                    </div>
                    <div class="form-group">
                        <label>Label</label>
                        <input type="text" name="contact[stats][${statIndex}][label]" value="">
                    </div>
                </div>
            `;
            container.appendChild(item);
            statIndex++;
        }

        function addFaqItem() {
            const container = document.getElementById('faq-container');
            const item = document.createElement('div');
            item.className = 'array-item';
            item.innerHTML = `
                <div class="array-item-header">
                    <span class="array-item-title">Item #${faqIndex + 1}</span>
                    <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                </div>
                <div class="form-group">
                    <label>Question</label>
                    <input type="text" name="faq[items][${faqIndex}][question]" value="">
                </div>
                <div class="form-group">
                    <label>Answer (Use newlines for paragraphs, '•' for bullets)</label>
                    <textarea name="faq[items][${faqIndex}][answer]" style="min-height: 150px;"></textarea>
                </div>
            `;
            container.appendChild(item);
            faqIndex++;
        }

        document.getElementById('editor-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            
            // Helper function to set nested value with proper array handling
            function setNestedValue(obj, keys, value) {
                let current = obj;
                for (let i = 0; i < keys.length - 1; i++) {
                    const key = keys[i];
                    const nextKey = keys[i + 1];
                    
                    // Determine if next key is numeric (array index)
                    const isNextNumeric = !isNaN(nextKey) && nextKey !== '';
                    
                    if (!current[key]) {
                        current[key] = isNextNumeric ? [] : {};
                    }
                    current = current[key];
                }
                
                const lastKey = keys[keys.length - 1];
                if (lastKey === 'reverseLayout') {
                    current[lastKey] = value === '1';
                } else {
                    current[lastKey] = value;
                }
            }
            
            // Convert FormData to nested object
            for (let [key, value] of formData.entries()) {
                if (key === 'key') continue; // Skip the key field
                // Parse bracket notation: "header[navLinks][0][text]" -> ["header", "navLinks", "0", "text"]
                const keys = key.split(/[\[\]]/).filter(k => k !== '');
                setNestedValue(data, keys, value);
            }

            // Clean up arrays - convert sparse arrays to dense arrays
            function cleanArrays(obj) {
                for (let key in obj) {
                    if (Array.isArray(obj[key])) {
                        // Convert sparse array to dense array
                        const arr = [];
                        for (let i = 0; i < obj[key].length; i++) {
                            if (obj[key][i] !== undefined && obj[key][i] !== null) {
                                arr.push(obj[key][i]);
                            }
                        }
                        // Recursively clean nested objects in arrays
                        arr.forEach(item => {
                            if (typeof item === 'object' && item !== null) {
                                cleanArrays(item);
                            }
                        });
                        obj[key] = arr;
                    } else if (typeof obj[key] === 'object' && obj[key] !== null) {
                        cleanArrays(obj[key]);
                    }
                }
            }
            cleanArrays(data);

            const statusMsg = document.getElementById('status-message');
            statusMsg.style.display = 'block';
            statusMsg.className = 'status-message';
            statusMsg.textContent = 'Saving...';

            try {
                const response = await fetch('save_json.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        data: JSON.stringify(data, null, 2)
                    })
                });

                const result = await response.json();

                if (result.success) {
                    statusMsg.className = 'status-message success';
                    statusMsg.textContent = '✓ Changes saved successfully!';
                    setTimeout(() => {
                        statusMsg.style.display = 'none';
                    }, 3000);
                } else {
                    statusMsg.className = 'status-message error';
                    statusMsg.textContent = 'Error: ' + (result.error || 'Failed to save');
                }
            } catch (e) {
                statusMsg.className = 'status-message error';
                statusMsg.textContent = 'Error: ' + e.message;
            }
        });

        // Image upload handler
        async function handleImageUpload(input, fixedFilename = null) {
            const file = input.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB');
                return;
            }

            const targetInput = document.getElementById(input.dataset.target);
            const previewId = input.dataset.preview;
            const previewContainer = document.getElementById(previewId) || input.closest('.image-upload-wrapper').querySelector('.image-preview-container');

            // Show loading state
            previewContainer.innerHTML = '<div class="image-placeholder">Uploading...</div>';

            const formData = new FormData();
            formData.append('image', file);
            if (fixedFilename) {
                formData.append('fixedFilename', fixedFilename);
            }

            try {
                const response = await fetch('upload_image.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    const imageUrl = fixedFilename ? `assets/${fixedFilename}` : result.url;
                    const imageName = fixedFilename || result.filename;
                    
                    // Update hidden input
                    if (targetInput) {
                        targetInput.value = imageName;
                    }

                    // Update preview
                    previewContainer.innerHTML = `<img src="${imageUrl}?t=${Date.now()}" alt="Preview" class="image-preview" id="${previewId}">`;
                    
                    showStatus('Image uploaded successfully!', 'success');
                } else {
                    previewContainer.innerHTML = '<div class="image-placeholder">Upload failed</div>';
                    showStatus('Error: ' + (result.error || 'Upload failed'), 'error');
                }
            } catch (e) {
                previewContainer.innerHTML = '<div class="image-placeholder">Upload failed</div>';
                showStatus('Error: ' + e.message, 'error');
            }
        }

        function removeImage(inputId, previewId) {
            if (confirm('Are you sure you want to remove this image?')) {
                const input = document.getElementById(inputId);
                const previewContainer = document.getElementById(previewId) || input.closest('.image-upload-wrapper').querySelector('.image-preview-container');
                
                if (input) {
                    input.value = '';
                }
                if (previewContainer) {
                    previewContainer.innerHTML = '<div class="image-placeholder">No image</div>';
                }
            }
        }

        function showStatus(message, type) {
            const statusMsg = document.getElementById('status-message');
            statusMsg.textContent = message;
            statusMsg.className = 'status-message ' + type;
            statusMsg.style.display = 'block';
            setTimeout(() => {
                statusMsg.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>
