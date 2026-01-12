<?php
// DEBUG: Enable error reporting
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
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $captchaValue = $num1 + $num2;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>JSON Editor - Login</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .login-container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); width: 100%; max-width: 400px; }
            .login-container h1 { font-size: 1.5rem; margin-bottom: 1.5rem; text-align: center; color: #111827; }
            .form-group { margin-bottom: 1rem; }
            label { display: block; margin-bottom: 0.5rem; color: #374151; font-size: 0.875rem; font-weight: 500; }
            input { width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
            input:focus { outline: none; border-color: #3b82f6; ring: 2px solid #3b82f6; }
            .btn { width: 100%; background: #2563eb; color: white; padding: 0.5rem; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; }
            .btn:hover { background: #1d4ed8; }
            .error { background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.875rem; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>Editor Login</h1>
            <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="captcha_value" value="<?php echo $captchaValue; ?>">
                <div class="form-group"><label>Username</label><input type="text" name="username" required autofocus></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
                <div class="form-group"><label>Question: <?php echo $num1; ?> + <?php echo $num2; ?> = ?</label><input type="number" name="captcha_answer" required></div>
                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php exit;
}

if (!file_exists(DATA_FILE)) die('Error: data.json not found');
$jsonData = file_get_contents(DATA_FILE);
$jsonArray = json_decode($jsonData, true) ?? [];

// Get data changes history
$changesDir = __DIR__ . '/data-changes';
$changeFiles = [];
if (is_dir($changesDir)) {
    $files = scandir($changesDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $changeFiles[] = $file;
        }
    }
    rsort($changeFiles);
}

// SECTION KEYS
$defaultContentKeys = ['hero', 'about', 'services', 'industries', 'trust', 'caseStudies', 'engagement', 'faq', 'contact', 'location'];
$globalKeys = ['siteMeta', 'header', 'footer', 'socialMedia', 'sectionOrder', 'sectionLabels', 'chatbot', 'customCode']; // Reserved

// Determine Current Order
$currentOrder = $jsonArray['sectionOrder'] ?? $defaultContentKeys;

// Identify Custom Sections (Keys in data that are not global and not in default list)
$allKeys = array_keys($jsonArray);
$customKeys = array_diff($allKeys, $defaultContentKeys, $globalKeys);

// Merge Custom Keys into Order if not already present
foreach ($customKeys as $k) {
    if (!in_array($k, $currentOrder)) {
        $currentOrder[] = $k;
    }
}

// Calculate Available Keys for Restore Logic (Standard keys not in current order)
$availableKeys = array_diff($defaultContentKeys, $currentOrder);

// SECTION LABELS
$sectionLabels = $jsonArray['sectionLabels'] ?? [];
function getLabel($key, $default) {
    global $sectionLabels;
    return $sectionLabels[$key] ?? $default;
}

// RENDER HELPERS
function renderToggle($key, $data) {
    $checked = isset($data['disabled']) && $data['disabled'] === true ? 'checked' : '';
    // Use hidden input to ensure false is sent if unchecked, but here we handle via JS mostly
    // We'll use a specific naming convention: [disabled]
    return '
    <div class="toggle-wrapper" title="Enable/Disable Section">
        <label class="toggle-switch">
            <input type="checkbox" name="'.$key.'[disabled]" value="1" '.$checked.'>
            <span class="slider"></span>
        </label>
        <span class="toggle-label">Disabled</span>
    </div>';
}

// CAPTURE SECTIONS
$renderedSections = [];

// --- SITE META ---
ob_start(); ?>
<div class="form-section">
    <div class="section-header"><h2>🌐 Site Meta</h2></div>
    <div class="section-content">
        <div class="form-group"><label>Site Title</label><input type="text" name="siteMeta[title]" value="<?php echo htmlspecialchars($jsonArray['siteMeta']['title'] ?? ''); ?>"></div>
        <div class="form-group"><label>Google Analytics ID</label><input type="text" name="siteMeta[googleAnalytics]" value="<?php echo htmlspecialchars($jsonArray['siteMeta']['googleAnalytics'] ?? ''); ?>"></div>
    </div>
</div>
<?php $renderedSections['siteMeta'] = ob_get_clean();

// --- CUSTOM CODE ---
ob_start(); ?>
<div class="form-section">
    <div class="section-header"><h2>💻 Custom Code & Scripts</h2></div>
    <div class="section-content">
        <div class="form-group">
            <label>Head Code (Meta tags, Analytics, Verification)</label>
            <textarea name="customCode[head]" rows="5" class="code-editor" placeholder="<meta name='...' content='...'>
<script>...</script>"><?php echo htmlspecialchars($jsonArray['customCode']['head'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label>Custom CSS (Style overrides)</label>
            <textarea name="customCode[css]" rows="5" class="code-editor" placeholder=".my-class { color: red; }"><?php echo htmlspecialchars($jsonArray['customCode']['css'] ?? ''); ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Body Start (After &lt;body&gt; tag)</label>
                <textarea name="customCode[bodyStart]" rows="4" class="code-editor"><?php echo htmlspecialchars($jsonArray['customCode']['bodyStart'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Body End (Before &lt;/body&gt; tag)</label>
                <textarea name="customCode[bodyEnd]" rows="4" class="code-editor"><?php echo htmlspecialchars($jsonArray['customCode']['bodyEnd'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
</div>
<?php $renderedSections['customCode'] = ob_get_clean();

// --- HEADER ---
ob_start(); ?>
<div class="form-section">
    <div class="section-header"><h2>📋 Header</h2></div>
    <div class="section-content">
        <div class="form-row">
            <div class="form-group"><label>Logo Text</label><input type="text" name="header[logoText]" value="<?php echo htmlspecialchars($jsonArray['header']['logoText'] ?? ''); ?>"></div>
            <div class="form-group"><label>CTA Text</label><input type="text" name="header[ctaText]" value="<?php echo htmlspecialchars($jsonArray['header']['ctaText'] ?? ''); ?>"></div>
        </div>
        <div class="form-group"><label>Navigation Links</label><div id="navLinks-container">
            <?php foreach ($jsonArray['header']['navLinks'] ?? [] as $i => $link): ?>
                <div class="array-item"><div class="array-item-header"><span>Link #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                <div class="form-row"><div class="form-group"><label>Text</label><input type="text" name="header[navLinks][<?php echo $i; ?>][text]" value="<?php echo htmlspecialchars($link['text'] ?? ''); ?>"></div>
                <div class="form-group"><label>URL</label><input type="text" name="header[navLinks][<?php echo $i; ?>][href]" value="<?php echo htmlspecialchars($link['href'] ?? ''); ?>"></div></div></div>
            <?php endforeach; ?>
        </div><button type="button" class="btn-add" onclick="addNavLink()">+ Add Link</button></div>
    </div>
</div>
<?php $renderedSections['header'] = ob_get_clean();

// MERGE ALL KEYS TO RENDER
// We need to render ALL standard keys (even if removed/not in order) + any custom keys in order
$keysToRender = array_unique(array_merge($currentOrder, $defaultContentKeys));

// --- CONTENT SECTIONS ---
foreach ($keysToRender as $key) {
    // If it's a standard key, we always want to render it (either in main list or hidden store)
    // If it's a custom key, it must exist in jsonArray to be rendered
    
    // For standard keys, data might be missing if it was "deleted" from data.json manually or empty. 
    // But we usually want to render the form with empty values then.
    // However, the previous logic checked: if (!isset($jsonArray[$key])) continue;
    
    // If it's a standard key and data is missing, we should probably initialize it with defaults so it can be restored.
    $data = $jsonArray[$key] ?? []; 
    
    // If it's a CUSTOM key and data is missing, we skip it (it's properly deleted).
    if (!in_array($key, $defaultContentKeys) && !isset($jsonArray[$key])) continue;
    
    ob_start();
    $label = getLabel($key, ucfirst($key));
    
    // Check if Standard or Custom
    if (in_array($key, $defaultContentKeys)) {
        // RENDER STANDARD SECTIONS
        ?>
        <div class="form-section content-section" id="section-<?php echo $key; ?>">
            <div class="section-header">
                <h2><span class="drag-handle">☰</span> <span class="section-label"><?php echo htmlspecialchars($label); ?></span></h2>
                <div class="header-controls">
                    <?php echo renderToggle($key, $data); ?>
                    <button type="button" class="btn-icon" onclick="renameSection('<?php echo $key; ?>')" title="Rename">✏️</button>
                    <!-- Standard sections can't be deleted entirely, only disabled or moved to unused list (simulated remove) -->
                    <button type="button" class="btn-icon btn-remove-section" onclick="removeSection('<?php echo $key; ?>')" title="Remove from list">🗑️</button>
                </div>
            </div>
            <div class="section-content">
                <?php if ($key === 'hero'): ?>
                    <div class="form-group"><label>Badge</label><input type="text" name="hero[badge]" value="<?php echo htmlspecialchars($data['badge'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Title</label><input type="text" name="hero[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Description</label><textarea name="hero[description]"><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea></div>
                    <div class="form-row"><div class="form-group"><label>CTA Text</label><input type="text" name="hero[ctaText]" value="<?php echo htmlspecialchars($data['ctaText'] ?? ''); ?>"></div>
                    <div class="form-group"><label>CTA Link</label><input type="text" name="hero[ctaLink]" value="<?php echo htmlspecialchars($data['ctaLink'] ?? ''); ?>"></div></div>
                <?php elseif ($key === 'about'): ?>
                    <div class="form-group"><label>Title</label><input type="text" name="about[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Description</label><textarea name="about[description]" style="min-height: 200px;"><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea></div>
                    <div class="form-group"><label>Principles Title</label><input type="text" name="about[principlesTitle]" value="<?php echo htmlspecialchars($data['principlesTitle'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Principles</label><div id="about-principles-container">
                        <?php foreach ($data['principles'] ?? [] as $i => $p): ?>
                            <div class="array-item"><div class="array-item-header"><span>Principle #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                            <div class="form-group"><input type="text" name="about[principles][<?php echo $i; ?>]" value="<?php echo htmlspecialchars($p); ?>"></div></div>
                        <?php endforeach; ?>
                    </div><button type="button" class="btn-add" onclick="addPrinciple()">+ Add Principle</button></div>
                <?php elseif ($key === 'services'): ?>
                    <div class="form-group"><label>Title</label><input type="text" name="services[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Items</label><div id="services-container">
                        <?php foreach ($data['items'] ?? [] as $i => $item): ?>
                            <div class="array-item"><div class="array-item-header"><span>Service #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                            <div class="form-group"><label>Title</label><input type="text" name="services[items][<?php echo $i; ?>][title]" value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Description</label><textarea name="services[items][<?php echo $i; ?>][description]"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea></div>
                            <div class="form-row"><div class="form-group"><label>Icon</label><input type="text" name="services[items][<?php echo $i; ?>][iconClass]" value="<?php echo htmlspecialchars($item['iconClass'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Color Class</label><input type="text" name="services[items][<?php echo $i; ?>][colorClass]" value="<?php echo htmlspecialchars($item['colorClass'] ?? ''); ?>"></div></div></div>
                        <?php endforeach; ?>
                    </div><button type="button" class="btn-add" onclick="addService()">+ Add Service</button></div>
                <?php elseif ($key === 'industries'): ?>
                    <div class="form-group"><label>Title</label><input type="text" name="industries[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Sub Text</label><textarea name="industries[subText]"><?php echo htmlspecialchars($data['subText'] ?? ''); ?></textarea></div>
                    <div class="form-group"><label>Items</label><div id="industries-container">
                        <?php foreach ($data['items'] ?? [] as $i => $item): ?>
                            <div class="array-item"><div class="array-item-header"><span>Industry #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                            <div class="form-row"><div class="form-group"><label>Name</label><input type="text" name="industries[items][<?php echo $i; ?>][name]" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Icon</label><input type="text" name="industries[items][<?php echo $i; ?>][iconClass]" value="<?php echo htmlspecialchars($item['iconClass'] ?? ''); ?>"></div></div>
                            <div class="form-group"><label>Description</label><textarea name="industries[items][<?php echo $i; ?>][description]"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea></div>
                            <div class="form-group"><label>Best Fit</label><input type="text" name="industries[items][<?php echo $i; ?>][bestFit]" value="<?php echo htmlspecialchars($item['bestFit'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Color</label><input type="text" name="industries[items][<?php echo $i; ?>][color]" value="<?php echo htmlspecialchars($item['color'] ?? ''); ?>"></div></div>
                        <?php endforeach; ?>
                    </div><button type="button" class="btn-add" onclick="addIndustry()">+ Add Industry</button></div>
                <?php elseif ($key === 'trust'): ?>
                    <div class="form-group"><label>Title</label><input type="text" name="trust[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Items</label><div id="trust-container">
                        <?php foreach ($data['items'] ?? [] as $i => $item): ?>
                            <div class="array-item"><div class="array-item-header"><span>Item #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                            <div class="form-group"><input type="text" name="trust[items][<?php echo $i; ?>]" value="<?php echo htmlspecialchars($item); ?>"></div></div>
                        <?php endforeach; ?>
                    </div><button type="button" class="btn-add" onclick="addTrustItem()">+ Add Item</button></div>
                <?php elseif ($key === 'caseStudies'): ?>
                    <div class="form-group"><label>Title</label><input type="text" name="caseStudies[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Sub Text</label><textarea name="caseStudies[subText]"><?php echo htmlspecialchars($data['subText'] ?? ''); ?></textarea></div>
                    <div class="form-group"><label>Items</label><div id="caseStudies-container">
                        <?php foreach ($data['items'] ?? [] as $i => $item): ?>
                            <div class="array-item"><div class="array-item-header"><span>CS #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                            <div class="form-group"><label>Title</label><input type="text" name="caseStudies[items][<?php echo $i; ?>][title]" value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Description</label><textarea name="caseStudies[items][<?php echo $i; ?>][description]"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea></div>
                            <div class="form-row"><div class="form-group"><label>CTA Text</label><input type="text" name="caseStudies[items][<?php echo $i; ?>][ctaText]" value="<?php echo htmlspecialchars($item['ctaText'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Icon</label><input type="text" name="caseStudies[items][<?php echo $i; ?>][iconClass]" value="<?php echo htmlspecialchars($item['iconClass'] ?? ''); ?>"></div></div>
                            <div class="form-group"><label>Preview Text</label><input type="text" name="caseStudies[items][<?php echo $i; ?>][previewText]" value="<?php echo htmlspecialchars($item['previewText'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Features</label><div id="cs-features-container-<?php echo $i; ?>">
                                <?php foreach ($item['features'] ?? [] as $fi => $f): ?>
                                    <div class="array-item" style="padding:5px"><div class="form-row" style="margin:0"><textarea name="caseStudies[items][<?php echo $i; ?>][features][<?php echo $fi; ?>]" rows="1" style="flex:1"><?php echo htmlspecialchars($f); ?></textarea><button type="button" class="btn-remove" onclick="removeArrayItem(this)">x</button></div></div>
                                <?php endforeach; ?>
                            </div><button type="button" class="btn-add" onclick="addCaseStudyFeature(<?php echo $i; ?>)">+ Add Feature</button></div>
                            <div class="form-group"><div class="checkbox-group"><input type="checkbox" name="caseStudies[items][<?php echo $i; ?>][reverseLayout]" value="1" <?php echo ($item['reverseLayout'] ?? false) ? 'checked' : ''; ?>><label>Reverse Layout</label></div></div></div>
                        <?php endforeach; ?>
                    </div><button type="button" class="btn-add" onclick="addCaseStudy()">+ Add Case Study</button></div>
                <?php elseif ($key === 'engagement'): ?>
                    <div class="form-group"><label>Title</label><input type="text" name="engagement[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Sub Text</label><input type="text" name="engagement[subText]" value="<?php echo htmlspecialchars($data['subText'] ?? ''); ?>"></div>
                    <div class="form-row"><div class="form-group"><label>CTA Text</label><input type="text" name="engagement[ctaText]" value="<?php echo htmlspecialchars($data['ctaText'] ?? ''); ?>"></div>
                    <div class="form-group"><label>CTA Link</label><input type="text" name="engagement[ctaLink]" value="<?php echo htmlspecialchars($data['ctaLink'] ?? ''); ?>"></div></div>
                    <div class="form-group"><label>Tiles</label><div id="eng-container">
                        <?php foreach ($data['items'] ?? [] as $i => $item): ?>
                            <div class="array-item"><div class="array-item-header"><span>Tile #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                            <div class="form-group"><label>Text</label><input type="text" name="engagement[items][<?php echo $i; ?>][text]" value="<?php echo htmlspecialchars($item['text'] ?? ''); ?>"></div>
                            <div class="form-row"><div class="form-group"><label>Link Text</label><input type="text" name="engagement[items][<?php echo $i; ?>][linkText]" value="<?php echo htmlspecialchars($item['linkText'] ?? ''); ?>"></div>
                            <div class="form-group"><label>URL</label><input type="text" name="engagement[items][<?php echo $i; ?>][link]" value="<?php echo htmlspecialchars($item['link'] ?? ''); ?>"></div></div></div>
                        <?php endforeach; ?>
                    </div><button type="button" class="btn-add" onclick="addEngItem()">+ Add Tile</button></div>
                <?php elseif ($key === 'faq'): ?>
                    <div class="form-group"><label>Title</label><input type="text" name="faq[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Sub Text</label><textarea name="faq[subText]"><?php echo htmlspecialchars($data['subText'] ?? ''); ?></textarea></div>
                    <div class="form-group"><label>Items</label><div id="faq-container">
                        <?php foreach ($data['items'] ?? [] as $i => $item): ?>
                            <div class="array-item"><div class="array-item-header"><span>Item #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                            <div class="form-group"><label>Question</label><input type="text" name="faq[items][<?php echo $i; ?>][question]" value="<?php echo htmlspecialchars($item['question'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Answer</label><textarea name="faq[items][<?php echo $i; ?>][answer]"><?php echo htmlspecialchars($item['answer'] ?? ''); ?></textarea></div></div>
                        <?php endforeach; ?>
                    </div><button type="button" class="btn-add" onclick="addFaqItem()">+ Add FAQ Item</button></div>
                <?php elseif ($key === 'contact'): ?>
                    <div class="form-group"><label>Title</label><input type="text" name="contact[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Sub Text</label><textarea name="contact[subText]"><?php echo htmlspecialchars($data['subText'] ?? ''); ?></textarea></div>
                    <div class="form-row"><div class="form-group"><label>Years</label><input type="text" name="contact[yearsExp]" value="<?php echo htmlspecialchars($data['yearsExp'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Years Label</label><input type="text" name="contact[yearsLabel]" value="<?php echo htmlspecialchars($data['yearsLabel'] ?? ''); ?>"></div></div>
                    <div class="form-row"><div class="form-group"><label>Success Title</label><input type="text" name="contact[successTitle]" value="<?php echo htmlspecialchars($data['successTitle'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Subtitle</label><input type="text" name="contact[successSubtitle]" value="<?php echo htmlspecialchars($data['successSubtitle'] ?? ''); ?>"></div></div>
                <?php elseif ($key === 'location'): ?>
                    <div class="form-row">
                        <div class="form-group"><label>Address</label><input type="text" name="location[address]" value="<?php echo htmlspecialchars($data['address'] ?? ''); ?>"></div>
                        <div class="form-group"><label>City</label><input type="text" name="location[city]" value="<?php echo htmlspecialchars($data['city'] ?? ''); ?>"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Country</label><input type="text" name="location[country]" value="<?php echo htmlspecialchars($data['country'] ?? ''); ?>"></div>
                        <div class="form-group"><label>Coords</label><div style="display:flex;gap:10px">
                            <input type="text" name="location[latitude]" placeholder="Lat" value="<?php echo htmlspecialchars($data['latitude'] ?? ''); ?>">
                            <input type="text" name="location[longitude]" placeholder="Long" value="<?php echo htmlspecialchars($data['longitude'] ?? ''); ?>">
                        </div></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    } else {
        // RENDER CUSTOM SECTION
        ?>
        <div class="form-section content-section" id="section-<?php echo $key; ?>">
            <div class="section-header">
                <h2><span class="drag-handle">☰</span> <span class="section-label"><?php echo htmlspecialchars($label); ?> (Custom)</span></h2>
                <div class="header-controls">
                    <?php echo renderToggle($key, $data); ?>
                    <button type="button" class="btn-icon" onclick="renameSection('<?php echo $key; ?>')" title="Rename">✏️</button>
                    <button type="button" class="btn-icon btn-remove-section" onclick="removeSection('<?php echo $key; ?>')" title="Remove">🗑️</button>
                </div>
            </div>
            <div class="section-content">
                <div class="form-group"><label>Section Title</label><input type="text" name="<?php echo $key; ?>[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                <div class="form-group"><label>Sub Title</label><input type="text" name="<?php echo $key; ?>[subText]" value="<?php echo htmlspecialchars($data['subText'] ?? ''); ?>"></div>
                <div class="form-group"><label>Items</label><div id="<?php echo $key; ?>-container">
                    <?php foreach ($data['items'] ?? [] as $i => $item): 
                        // Handle simple strings or objects
                        $iTitle = is_array($item) ? ($item['title'] ?? '') : '';
                        $iDesc = is_array($item) ? ($item['description'] ?? '') : (is_string($item) ? $item : '');
                    ?>
                        <div class="array-item"><div class="array-item-header"><span>Item #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                        <div class="form-group"><label>Item Title</label><input type="text" name="<?php echo $key; ?>[items][<?php echo $i; ?>][title]" value="<?php echo htmlspecialchars($iTitle); ?>"></div>
                        <div class="form-group"><label>Item Description</label><textarea name="<?php echo $key; ?>[items][<?php echo $i; ?>][description]"><?php echo htmlspecialchars($iDesc); ?></textarea></div></div>
                    <?php endforeach; ?>
                </div><button type="button" class="btn-add" onclick="addCustomItem('<?php echo $key; ?>')">+ Add Item</button></div>
            </div>
        </div>
        <?php
    }
    
    $renderedSections[$key] = ob_get_clean();
}

// --- IMAGES (Global) ---
ob_start(); ?>
<div class="form-section">
    <div class="section-header"><h2>🖼️ Image Management (Global)</h2></div>
    <div class="section-content">
         <div class="form-group"><label>Logo</label><div class="image-upload-wrapper"><div class="image-preview-container"><img src="assets/logo.png" style="max-height:100px;max-width:100%"></div></div></div>
    </div>
</div>
<?php $renderedSections['images'] = ob_get_clean();

// --- SOCIAL ---
ob_start(); ?>
<div class="form-section">
    <div class="section-header"><h2>📱 Social Media</h2></div>
    <div class="section-content">
        <div class="form-row">
            <div class="form-group"><label>Facebook</label><input type="text" name="socialMedia[facebook]" value="<?php echo htmlspecialchars($jsonArray['socialMedia']['facebook'] ?? ''); ?>"></div>
            <div class="form-group"><label>Twitter</label><input type="text" name="socialMedia[twitter]" value="<?php echo htmlspecialchars($jsonArray['socialMedia']['twitter'] ?? ''); ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>LinkedIn</label><input type="text" name="socialMedia[linkedin]" value="<?php echo htmlspecialchars($jsonArray['socialMedia']['linkedin'] ?? ''); ?>"></div>
            <div class="form-group"><label>Instagram</label><input type="text" name="socialMedia[instagram]" value="<?php echo htmlspecialchars($jsonArray['socialMedia']['instagram'] ?? ''); ?>"></div>
        </div>
    </div>
</div>
<?php $renderedSections['socialMedia'] = ob_get_clean();

// --- FOOTER ---
ob_start(); ?>
<div class="form-section">
    <div class="section-header"><h2>📄 Footer</h2></div>
    <div class="section-content">
        <div class="form-group"><label>Copyright</label><input type="text" name="footer[copyright]" value="<?php echo htmlspecialchars($jsonArray['footer']['copyright'] ?? ''); ?>"></div>
    </div>
</div>
<?php $renderedSections['footer'] = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON Editor - Arinsol.ai</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Modern & Compact Editor Styles */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f3f4f6; color: #111827; margin: 0; padding-bottom: 80px; font-size: 14px; }
        
        .header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 12px 20px; position: sticky; top: 0; z-index: 50; }
        .header-content { max-width: 1000px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 1.25rem; font-weight: 600; margin: 0; color: #111827; }
        
        .container { max-width: 1000px; margin: 24px auto; padding: 0 16px; }
        
        /* Section Dividers */
        h3 { 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 0.05em; 
            color: #6b7280; 
            margin: 32px 0 12px; 
            border-bottom: 1px solid #e5e7eb; 
            padding-bottom: 8px; 
            font-weight: 600;
        }
        
        /* Form Sections (Cards) */
        .form-section { 
            background: #fff; 
            border: 1px solid #e5e7eb; 
            border-radius: 6px; 
            margin-bottom: 12px; 
            overflow: hidden; 
            box-shadow: 0 1px 2px rgba(0,0,0,0.02); 
        }
        
        .section-header { 
            background: #f9fafb; 
            padding: 10px 16px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            cursor: pointer; 
            user-select: none;
            transition: background 0.15s;
        }
        .section-header:hover { background: #f3f4f6; }
        
        .section-header h2 { 
            font-size: 0.95rem; 
            font-weight: 600; 
            margin: 0; 
            color: #374151; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }
        
        .section-content { 
            padding: 20px; 
            display: none; 
            border-top: 1px solid #e5e7eb;
        }
        .form-section.active .section-content { display: block; }
        
        /* Inputs */
        .form-group { margin-bottom: 16px; }
        .form-group:last-child { margin-bottom: 0; }
        
        .form-group label { 
            display: block; 
            margin-bottom: 4px; 
            font-size: 0.8rem; 
            font-weight: 500; 
            color: #4b5563; 
        }
        
        .form-group input, .form-group textarea, .form-control { 
            width: 100%; 
            padding: 8px 12px; 
            border: 1px solid #d1d5db; 
            border-radius: 4px; 
            font-size: 0.9rem; 
            box-sizing: border-box; 
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        
        .form-group input:focus, .form-group textarea:focus { 
            outline: none; 
            border-color: #3b82f6; 
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); 
        }
        
        .code-editor { font-family: 'Consolas', 'Monaco', monospace; background: #fafafa; font-size: 0.85rem; color: #333; }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        
        /* Array Items */
        .array-item { 
            background: #f9fafb; 
            border: 1px solid #e5e7eb; 
            border-radius: 4px; 
            padding: 12px; 
            margin-bottom: 10px; 
        }
        
        .array-item-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 8px; 
            font-size: 0.75rem; 
            font-weight: 600; 
            color: #9ca3af; 
            text-transform: uppercase;
        }
        
        /* Buttons */
        .btn { 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            padding: 6px 12px; 
            border-radius: 4px; 
            font-size: 0.85rem; 
            font-weight: 500; 
            cursor: pointer; 
            border: 1px solid transparent; 
            text-decoration: none; 
            transition: all 0.15s;
        }
        
        .btn-secondary { background: #fff; border-color: #d1d5db; color: #374151; }
        .btn-secondary:hover { background: #f9fafb; border-color: #9ca3af; }
        
        .btn-primary { background: #3b82f6; color: #fff; border: none; }
        .btn-primary:hover { background: #2563eb; }
        
        .btn-success { background: #10b981; color: #fff; border: none; }
        .btn-success:hover { background: #059669; }
        
        .btn-add { 
            background: #eff6ff; 
            color: #2563eb; 
            border: 1px dashed #bfdbfe; 
            width: 100%; 
            margin-top: 8px; 
        }
        .btn-add:hover { background: #dbeafe; border-color: #93c5fd; }
        
        .btn-remove { 
            background: #fff5f5; 
            color: #dc2626; 
            padding: 4px 10px; 
            font-size: 0.7rem; 
            border-radius: 4px;
            border: 1px solid #fecaca;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            transition: all 0.2s;
        }
        .btn-remove:hover { 
            background: #fee2e2; 
            color: #b91c1c;
            border-color: #fca5a5;
            box-shadow: 0 1px 2px rgba(220, 38, 38, 0.1);
        }
        
        /* Header Controls */
        .header-controls { display: flex; align-items: center; gap: 8px; }
        .btn-icon { background: none; border: none; font-size: 1rem; cursor: pointer; opacity: 0.5; padding: 4px; border-radius: 4px; }
        .btn-icon:hover { opacity: 1; background: rgba(0,0,0,0.05); }
        .btn-remove-section { color: #dc2626; }
        
        .drag-handle { color: #d1d5db; margin-right: 8px; cursor: grab; font-size: 1.1rem; }
        .drag-handle:hover { color: #9ca3af; }
        
        /* Toggle Switch */
        .toggle-wrapper { display: flex; align-items: center; gap: 6px; padding: 4px 8px; background: #f3f4f6; border-radius: 4px; border: 1px solid #e5e7eb; }
        .toggle-switch { position: relative; display: inline-block; width: 28px; height: 16px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #d1d5db; transition: .3s; border-radius: 16px; }
        .slider:before { position: absolute; content: ""; height: 12px; width: 12px; left: 2px; bottom: 2px; background-color: white; transition: .3s; border-radius: 50%; }
        input:checked + .slider { background-color: #ef4444; }
        input:checked + .slider:before { transform: translateX(12px); }
        .toggle-label { font-size: 0.7rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
        
        /* Add Section Area */
        #add-section-container { 
            background: #f9fafb; 
            border: 2px dashed #e5e7eb; 
            border-radius: 8px; 
            padding: 32px; 
            text-align: center; 
            margin-top: 32px; 
        }
        #add-section-container h4 { margin: 0 0 16px; color: #6b7280; font-size: 0.9rem; }
        
        /* Footer Actions */
        .footer-actions { 
            position: fixed; bottom: 0; left: 0; width: 100%; 
            background: rgba(255,255,255,0.9); backdrop-filter: blur(8px); 
            padding: 16px; text-align: center; border-top: 1px solid #e5e7eb; 
            z-index: 40;
        }
        .footer-actions .btn-success { padding: 8px 32px; font-size: 1rem; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2); }
        
        /* Image Preview */
        .image-preview-container { text-align: center; padding: 10px; background: #f9fafb; border-radius: 4px; border: 1px solid #e5e7eb; margin-bottom: 8px; }
        
        /* Hide delete and restore UI elements for now */
        .btn-remove-section { display: none !important; }
        #restore-controls { display: none !important; }
        #add-section-container h4:first-child { display: none; } /* "Restore Removed Section" header */
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📝 Content Editor</h1>
            <div class="header-actions">
                <a href="#" class="btn btn-secondary" onclick="openHistory(event)">📜 History</a>
                <a href="/" target="_blank" class="btn btn-secondary">View Site</a>
                <a href="?logout=1" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </div>
    
    <form id="editor-form">
        <div class="container">
            <!-- Global Settings Zone -->
            <h3>Global Settings</h3>
            <?php echo $renderedSections['siteMeta']; ?>
            <?php echo $renderedSections['customCode']; ?>
            <?php echo $renderedSections['header']; ?>
            <?php echo $renderedSections['footer']; ?>
            <?php echo $renderedSections['socialMedia']; ?>
            <?php echo $renderedSections['images']; ?>
            
            <!-- Page Content Zone -->
            <h3>Page Content (Drag to Reorder)</h3>
            <div id="content-sections">
                <?php 
                foreach ($currentOrder as $key) {
                    if (isset($renderedSections[$key])) {
                        echo $renderedSections[$key];
                    }
                }
                ?>
            </div>
            
            <!-- Add Section Area -->
            <div id="add-section-container">
                <div id="restore-controls" style="<?php echo empty($availableKeys) ? 'display:none' : 'margin-bottom:20px'; ?>">
                    <h4>Restore Removed Section</h4>
                    <select id="add-section-select" class="form-control" style="width:auto; display:inline-block; margin-right:8px;">
                        <option value="">-- Select Section --</option>
                        <?php foreach ($availableKeys as $key): ?>
                            <option value="<?php echo $key; ?>"><?php echo ucfirst($key); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-success" onclick="restoreSection()">+ Restore</button>
                </div>
                
                <div id="add-custom-container">
                    <h4>Create New Content Block</h4>
                    <button type="button" class="btn btn-primary" onclick="createNewSection()">+ Create Custom Section</button>
                </div>
            </div>
        </div>
        
        <!-- Hidden Inputs -->
        <input type="hidden" name="sectionOrder" id="input-sectionOrder" value="">
        <div id="labels-container">
            <?php foreach ($sectionLabels as $k => $v): ?>
                <input type="hidden" name="sectionLabels[<?php echo $k; ?>]" id="label-<?php echo $k; ?>" value="<?php echo htmlspecialchars($v); ?>">
            <?php endforeach; ?>
        </div>
        
        <div id="section-store" style="display:none">
            <?php 
            foreach ($availableKeys as $key) {
                if (isset($renderedSections[$key])) {
                    echo $renderedSections[$key];
                }
            }
            ?>
        </div>

        <div class="footer-actions">
            <button type="submit" class="btn btn-success">💾 Save All Changes</button>
        </div>
    </form>

    <script>
        // Sortable
        const contentSections = document.getElementById('content-sections');
        new Sortable(contentSections, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost'
        });

        // Accordion
        document.addEventListener('click', function(e) {
            if (e.target.closest('.section-header') && !e.target.closest('.btn-icon') && !e.target.closest('.toggle-wrapper') && !e.target.closest('.drag-handle')) {
                const section = e.target.closest('.form-section');
                section.classList.toggle('active');
            }
        });

        function updateOrderInput() {
            const order = [];
            document.querySelectorAll('#content-sections .form-section').forEach(el => {
                const id = el.id.replace('section-', '');
                order.push(id);
            });
            document.getElementById('input-sectionOrder').value = JSON.stringify(order);
        }

        function renameSection(key) {
            const labelSpan = document.querySelector(`#section-${key} .section-label`);
            const currentLabel = labelSpan.textContent;
            
            Swal.fire({
                title: 'Rename Section',
                input: 'text',
                inputValue: currentLabel,
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value || value.trim() === '') {
                        return 'You need to write something!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const newLabel = result.value;
                    labelSpan.textContent = newLabel;
                    let input = document.getElementById(`label-${key}`);
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `sectionLabels[${key}]`;
                        input.id = `label-${key}`;
                        document.getElementById('labels-container').appendChild(input);
                    }
                    input.value = newLabel;
                }
            });
        }

        // List of standard section keys (synced with PHP)
        const standardSectionKeys = <?php echo json_encode($defaultContentKeys); ?>;
        
        function removeSection(key) {
            const isStandard = standardSectionKeys.includes(key);
            const section = document.getElementById(`section-${key}`);
            
            if (!section) {
                Swal.fire('Error', 'Section element not found.', 'error');
                return;
            }
            
            if (isStandard) {
                // Standard Section: Move to restore list
                Swal.fire({
                    title: 'Remove Section?',
                    text: "Remove this section from the page? You can restore it later.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const store = document.getElementById('section-store');
                        const select = document.getElementById('add-section-select');
                        
                        // Move inputs to hidden store so they are preserved (not deleted)
                        store.appendChild(section);
                        section.classList.remove('active');
                        
                        // Add to restore dropdown
                        const opt = document.createElement('option');
                        opt.value = key;
                        opt.textContent = section.querySelector('.section-label').textContent;
                        select.appendChild(opt);
                        
                        document.getElementById('restore-controls').style.display = 'block';
                    }
                });
            } else {
                // Custom Section: Permanently delete
                Swal.fire({
                    title: 'Permanently Delete?',
                    text: "This cannot be undone after saving.",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // 1. Remove all inputs inside the section to ensure they are not submitted
                        const inputs = section.querySelectorAll('input, textarea, select');
                        inputs.forEach(input => input.disabled = true);
                        
                        // 2. Remove the section DOM entirely
                        section.remove();
                        
                        // 3. Remove label hidden input if exists
                        const labelInput = document.getElementById(`label-${key}`);
                        if (labelInput) labelInput.remove();
                        
                        // 4. Add a marker to track deleted custom sections (explicitly for JS handler)
                        const deletedInput = document.createElement('input');
                        deletedInput.type = 'hidden';
                        deletedInput.name = `_deletedSections[]`;
                        deletedInput.value = key;
                        document.getElementById('labels-container').appendChild(deletedInput);
                    }
                });
            }
        }

        function restoreSection() {
            const select = document.getElementById('add-section-select');
            const key = select.value;
            
            if (!key) {
                Swal.fire('Wait', 'Please select a section to restore.', 'info');
                return;
            }
            
            const section = document.getElementById(`section-${key}`);
            const container = document.getElementById('content-sections');
            
            if (section) {
                container.appendChild(section);
                section.classList.add('active');
                
                // Remove option robustly
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].value === key) {
                        select.remove(i);
                        break;
                    }
                }
                
                // Reset selection
                select.value = "";
                
                if (select.options.length <= 1) {
                    document.getElementById('restore-controls').style.display = 'none';
                }
            } else {
                Swal.fire('Error', 'Section data not found in page. Please reload.', 'error');
            }
        }

        function createNewSection() {
            Swal.fire({
                title: 'New Section ID',
                input: 'text',
                text: 'Enter a unique ID (e.g. "team", "news")',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) return 'You need to write something!';
                    const cleanId = value.replace(/[^a-zA-Z0-9]/g, '');
                    if (!cleanId) return 'Invalid ID (alphanumeric only)';
                    if (document.getElementById(`section-${cleanId}`)) return 'ID already exists!';
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const id = result.value;
                    const cleanId = id.replace(/[^a-zA-Z0-9]/g, '');
                    
                    Swal.fire({
                        title: 'Section Title',
                        input: 'text',
                        inputValue: 'New Section',
                        text: 'Enter display title:'
                    }).then((titleResult) => {
                        if (titleResult.isConfirmed) {
                            const title = titleResult.value || 'New Section';
                            
                            // Create DOM elements for new custom section
                            const html = `
                            <div class="form-section content-section active" id="section-${cleanId}">
                                <div class="section-header">
                                    <h2><span class="drag-handle">☰</span> <span class="section-label">${title} (Custom)</span></h2>
                                    <div class="header-controls">
                                        <div class="toggle-wrapper" title="Enable/Disable Section">
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="${cleanId}[disabled]" value="1">
                                                <span class="slider"></span>
                                            </label>
                                            <span class="toggle-label">Disabled</span>
                                        </div>
                                        <button type="button" class="btn-icon" onclick="renameSection('${cleanId}')" title="Rename">✏️</button>
                                        <button type="button" class="btn-icon btn-remove-section" onclick="removeSection('${cleanId}')" title="Remove">🗑️</button>
                                    </div>
                                </div>
                                <div class="section-content" style="display:block">
                                    <div class="form-group"><label>Section Title</label><input type="text" name="${cleanId}[title]" value="${title}"></div>
                                    <div class="form-group"><label>Sub Title</label><input type="text" name="${cleanId}[subText]" value=""></div>
                                    <div class="form-group"><label>Items</label>
                                        <div id="${cleanId}-container"></div>
                                        <button type="button" class="btn-add" onclick="addCustomItem('${cleanId}')">+ Add Item</button>
                                    </div>
                                </div>
                            </div>
                            `;
                            
                            document.getElementById('content-sections').insertAdjacentHTML('beforeend', html);
                            // Add label input
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = `sectionLabels[${cleanId}]`;
                            input.id = `label-${cleanId}`;
                            input.value = title;
                            document.getElementById('labels-container').appendChild(input);
                        }
                    });
                }
            });
        }

        function addCustomItem(sectionKey) {
            // We need a unique index. Use timestamp or count items.
            const container = document.getElementById(`${sectionKey}-container`);
            const i = container.children.length + Math.floor(Math.random() * 1000);
            
            const html = `
                <div class="array-item">
                    <div class="array-item-header"><span>Item (New)</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                    <div class="form-group"><label>Item Title</label><input type="text" name="${sectionKey}[items][${i}][title]"></div>
                    <div class="form-group"><label>Item Description</label><textarea name="${sectionKey}[items][${i}][description]"></textarea></div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        // Helpers
        function removeArrayItem(btn) { 
            Swal.fire({
                title: 'Remove item?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.closest('.array-item').remove();
                }
            });
        }
        let counters = {};
        function getCount(key) { if(!counters[key]) counters[key]=100; return counters[key]++; }
        
        // Add functions for standard sections
        function addNavLink() {
            const i = getCount('nav');
            const html = `<div class="array-item"><div class="array-item-header"><span>Link</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-row"><div class="form-group"><label>Text</label><input type="text" name="header[navLinks][${i}][text]"></div><div class="form-group"><label>URL</label><input type="text" name="header[navLinks][${i}][href]"></div></div></div>`;
            document.getElementById('navLinks-container').insertAdjacentHTML('beforeend', html);
        }
        function addService() {
             const i = getCount('services');
             const html = `<div class="array-item"><div class="array-item-header"><span>Service</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><label>Title</label><input type="text" name="services[items][${i}][title]"></div><div class="form-group"><label>Description</label><textarea name="services[items][${i}][description]"></textarea></div><div class="form-row"><div class="form-group"><label>Icon</label><input type="text" name="services[items][${i}][iconClass]"></div><div class="form-group"><label>Color Class</label><input type="text" name="services[items][${i}][colorClass]"></div></div></div>`;
            document.getElementById('services-container').insertAdjacentHTML('beforeend', html);
        }
        function addIndustry() {
            const i = getCount('ind');
            const html = `<div class="array-item"><div class="array-item-header"><span>Industry</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-row"><div class="form-group"><label>Name</label><input type="text" name="industries[items][${i}][name]"></div><div class="form-group"><label>Icon</label><input type="text" name="industries[items][${i}][iconClass]"></div></div><div class="form-group"><label>Description</label><textarea name="industries[items][${i}][description]"></textarea></div><div class="form-group"><label>Best Fit</label><input type="text" name="industries[items][${i}][bestFit]"></div><div class="form-group"><label>Color</label><input type="text" name="industries[items][${i}][color]"></div></div>`;
            document.getElementById('industries-container').insertAdjacentHTML('beforeend', html);
        }
        function addTrustItem() {
            const i = getCount('trust');
            const html = `<div class="array-item"><div class="array-item-header"><span>Item</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><input type="text" name="trust[items][${i}]"></div></div>`;
            document.getElementById('trust-container').insertAdjacentHTML('beforeend', html);
        }
        function addCaseStudy() {
            const i = getCount('cs');
            const html = `<div class="array-item"><div class="array-item-header"><span>CS</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><label>Title</label><input type="text" name="caseStudies[items][${i}][title]"></div><div class="form-group"><label>Description</label><textarea name="caseStudies[items][${i}][description]"></textarea></div><div class="form-row"><div class="form-group"><label>CTA Text</label><input type="text" name="caseStudies[items][${i}][ctaText]"></div><div class="form-group"><label>Icon</label><input type="text" name="caseStudies[items][${i}][iconClass]"></div></div><div class="form-group"><label>Preview Text</label><input type="text" name="caseStudies[items][${i}][previewText]"></div><div class="form-group"><label>Features</label><div id="cs-features-container-${i}"></div><button type="button" class="btn-add" onclick="addCaseStudyFeature(${i})">+ Add Feature</button></div><div class="form-group"><div class="checkbox-group"><input type="checkbox" name="caseStudies[items][${i}][reverseLayout]" value="1"><label>Reverse Layout</label></div></div></div>`;
            document.getElementById('caseStudies-container').insertAdjacentHTML('beforeend', html);
        }
        function addCaseStudyFeature(csI) {
            const fI = getCount('csf');
            const html = `<div class="array-item" style="padding:5px"><div class="form-row" style="margin:0"><textarea name="caseStudies[items][${csI}][features][${fI}]" rows="1" style="flex:1"></textarea><button type="button" class="btn-remove" onclick="removeArrayItem(this)">x</button></div></div>`;
            document.getElementById(`cs-features-container-${csI}`).insertAdjacentHTML('beforeend', html);
        }
        function addEngItem() {
            const i = getCount('eng');
            const html = `<div class="array-item"><div class="array-item-header"><span>Tile</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><label>Text</label><input type="text" name="engagement[items][${i}][text]"></div><div class="form-row"><div class="form-group"><label>Link Text</label><input type="text" name="engagement[items][${i}][linkText]"></div><div class="form-group"><label>URL</label><input type="text" name="engagement[items][${i}][link]"></div></div></div>`;
            document.getElementById('eng-container').insertAdjacentHTML('beforeend', html);
        }
        function addFaqItem() {
            const i = getCount('faq');
            const html = `<div class="array-item"><div class="array-item-header"><span>Item</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><label>Question</label><input type="text" name="faq[items][${i}][question]"></div><div class="form-group"><label>Answer</label><textarea name="faq[items][${i}][answer]"></textarea></div></div>`;
            document.getElementById('faq-container').insertAdjacentHTML('beforeend', html);
        }
        function addPrinciple() {
            const i = getCount('principles');
            const html = `<div class="array-item"><div class="array-item-header"><span>Principle</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><input type="text" name="about[principles][${i}]"></div></div>`;
            document.getElementById('about-principles-container').insertAdjacentHTML('beforeend', html);
        }
        
        function openHistory(e) {
            e.preventDefault();
            const historyWindow = window.open('', 'DataHistory', 'width=600,height=800,scrollbars=yes');
            const content = `<html><head><title>History</title><style>body{font-family:sans-serif;padding:20px;background:#f5f7fa}table{width:100%;background:white;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}</style></head><body><h1>Data Changes</h1><?php if(empty($changeFiles)): ?><p>No history.</p><?php else: ?><table><thead><tr><th>Date</th><th>File</th></tr></thead><tbody><?php foreach($changeFiles as $f): ?><tr><td><?php echo $f; ?></td><td><?php echo $f; ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></body></html>`;
            historyWindow.document.write(content);
            historyWindow.document.close();
        }

        document.getElementById('editor-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            updateOrderInput();
            
            const formData = new FormData(this);
            const data = {};
            
            // Nested object creation
            function setNestedValue(obj, keys, value) {
                let current = obj;
                for (let i = 0; i < keys.length - 1; i++) {
                    const key = keys[i];
                    const nextKey = keys[i + 1];
                    const isNextNumeric = !isNaN(nextKey) && nextKey !== '';
                    if (!current[key]) current[key] = isNextNumeric ? [] : {};
                    current = current[key];
                }
                const lastKey = keys[keys.length - 1];
                
                // Boolean checks
                if (lastKey === 'reverseLayout' || lastKey === 'disabled') {
                    // Checkbox value '1' sent if checked. 
                    // However, we need to handle UNCHECKED disabled (false).
                    // The easiest way is: if data.disabled is not set by this loop, it means it's unchecked (false).
                    // But we are constructing 'data' from scratch.
                    // So we only set true if value is present. Default undefined means false essentially.
                    current[lastKey] = (value === '1');
                } else if (lastKey === 'sectionOrder' && typeof value === 'string') {
                     try { current[lastKey] = JSON.parse(value); } catch(e) { current[lastKey] = []; }
                }
                else current[lastKey] = value;
            }

            // Collect deleted section keys
            const deletedSections = [];
            for (let [key, value] of formData.entries()) {
                if (key === '_deletedSections[]') {
                    deletedSections.push(value);
                    continue;
                }
                if (key === 'key') continue;
                const keys = key.split(/[\[\]]/).filter(k => k !== '');
                setNestedValue(data, keys, value);
            }
            
            // Remove deleted custom sections from data
            deletedSections.forEach(sectionKey => {
                delete data[sectionKey];
                // Also remove from sectionLabels if exists
                if (data.sectionLabels && data.sectionLabels[sectionKey]) {
                    delete data.sectionLabels[sectionKey];
                }
            });
            
            // Clean arrays
            function cleanArrays(obj) {
                for (let key in obj) {
                    if (Array.isArray(obj[key])) {
                        obj[key] = obj[key].filter(item => item !== undefined && item !== null);
                        obj[key].forEach(item => { if (typeof item === 'object') cleanArrays(item); });
                    } else if (typeof obj[key] === 'object' && obj[key] !== null) {
                        cleanArrays(obj[key]);
                    }
                }
            }
            cleanArrays(data);

            Swal.fire({ title: 'Saving...', didOpen: () => Swal.showLoading() });

            try {
                const response = await fetch('save_json.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ data: JSON.stringify(data, null, 2) })
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire({ icon: 'success', title: 'Saved!', timer: 1500, showConfirmButton: false });
                } else {
                    throw new Error(result.error);
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.message });
            }
        });
    </script>
</body>
</html>
