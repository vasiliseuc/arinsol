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
    // Rename Case Studies to Products in Label
    if ($key === 'caseStudies' && !isset($sectionLabels[$key])) return 'Products';
    return $sectionLabels[$key] ?? $default;
}

// RENDER HELPERS
function renderToggle($key, $data) {
    $checked = isset($data['disabled']) && $data['disabled'] === true ? 'checked' : '';
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
<div class="form-section" id="section-siteMeta">
    <div class="section-header"><h2>🌐 Site Meta</h2></div>
    <div class="section-content">
        <div class="form-group"><label>Site Title</label><input type="text" name="siteMeta[title]" value="<?php echo htmlspecialchars($jsonArray['siteMeta']['title'] ?? ''); ?>"></div>
        <div class="form-group"><label>Google Analytics ID</label><input type="text" name="siteMeta[googleAnalytics]" value="<?php echo htmlspecialchars($jsonArray['siteMeta']['googleAnalytics'] ?? ''); ?>"></div>
    </div>
</div>
<?php $renderedSections['siteMeta'] = ob_get_clean();

// --- CUSTOM CODE ---
ob_start(); ?>
<div class="form-section" id="section-customCode">
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
<div class="form-section" id="section-header">
    <div class="section-header"><h2>📋 Header</h2></div>
    <div class="section-content">
        <div class="form-row">
            <div class="form-group"><label>Logo Text</label><input type="text" name="header[logoText]" value="<?php echo htmlspecialchars($jsonArray['header']['logoText'] ?? ''); ?>"></div>
            <div class="form-group"><label>CTA Text</label><input type="text" name="header[ctaText]" value="<?php echo htmlspecialchars($jsonArray['header']['ctaText'] ?? ''); ?>"></div>
        </div>
        <div class="form-group"><label>Navigation Links</label><div id="navLinks-container">
            <?php foreach ($jsonArray['header']['navLinks'] ?? [] as $i => $link): ?>
                <div class="array-item">
                    <div class="array-item-header">
                        <span class="nav-handle" style="cursor:grab;margin-right:10px;">☰</span>
                        <span>Link</span>
                        <button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Text</label><input type="text" name="header[navLinks][<?php echo $i; ?>][text]" value="<?php echo htmlspecialchars($link['text'] ?? ''); ?>"></div>
                        <div class="form-group">
                            <label>URL (Select Section or Type Custom)</label>
                            <input type="text" list="section-urls" name="header[navLinks][<?php echo $i; ?>][href]" value="<?php echo htmlspecialchars($link['href'] ?? ''); ?>" placeholder="#section">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div><button type="button" class="btn-add" onclick="addNavLink()">+ Add Link</button></div>

        <!-- Datalist for Sections -->
        <datalist id="section-urls">
            <?php 
                // Default Sections
                foreach ($defaultContentKeys as $k) {
                    echo '<option value="#'.$k.'">'.ucfirst($k).'</option>';
                }
                // Custom Sections
                foreach ($customKeys as $k) {
                    echo '<option value="#'.$k.'">'.ucfirst($k).'</option>';
                }
            ?>
        </datalist>
    </div>
</div>
<?php $renderedSections['header'] = ob_get_clean();

// MERGE ALL KEYS TO RENDER
$keysToRender = array_unique(array_merge($currentOrder, $defaultContentKeys));

// --- CONTENT SECTIONS ---
foreach ($keysToRender as $key) {
    $data = $jsonArray[$key] ?? []; 
    if (!in_array($key, $defaultContentKeys) && !isset($jsonArray[$key])) continue;
    
    ob_start();
    $label = getLabel($key, ucfirst($key));
    if ($key === 'caseStudies' && $label === 'CaseStudies') $label = 'Products'; // Force default label
    
    // Check if Standard or Custom
    if (in_array($key, $defaultContentKeys)) {
        // RENDER STANDARD SECTIONS
        ?>
        <div class="form-section content-section" id="section-<?php echo $key; ?>">
            <div class="section-header">
                <h2><span class="section-label"><?php echo htmlspecialchars($label); ?></span></h2>
                <div class="header-controls">
                    <?php echo renderToggle($key, $data); ?>
                    <button type="button" class="btn-icon" onclick="renameSection('<?php echo $key; ?>')" title="Rename">✏️</button>
                    <button type="button" class="btn-icon btn-remove-section" onclick="removeSection('<?php echo $key; ?>')" title="Remove from list">🗑️</button>
                </div>
            </div>
            <div class="section-content">
                <?php if ($key === 'hero'): ?>
                    <div class="form-group"><label>Badge</label><input type="text" name="hero[badge]" value="<?php echo htmlspecialchars($data['badge'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Title</label><input type="text" name="hero[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    <div class="form-group"><label>Description</label><textarea name="hero[description]"><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea></div>
                    
                    <!-- Video Upload -->
                    <div class="form-group">
                        <label>Hero Video (MP4/WebM)</label>
                        <div class="image-upload-wrapper">
                            <input type="file" class="form-control" onchange="uploadAsset(this, null, 'hero_video_input')" accept="video/mp4,video/webm">
                            <input type="hidden" name="hero[video]" id="hero_video_input" value="<?php echo htmlspecialchars($data['video'] ?? ''); ?>">
                            <div class="current-file" style="margin-top:5px;font-size:0.85rem;color:#666">
                                Current: <?php echo htmlspecialchars($data['video'] ?? 'assets/banner_video.mp4 (Default)'); ?>
                            </div>
                        </div>
                    </div>

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
                            <div class="array-item"><div class="array-item-header"><span>Product #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                            <div class="form-group"><label>Title</label><input type="text" name="caseStudies[items][<?php echo $i; ?>][title]" value="<?php echo htmlspecialchars($item['title'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Description</label><textarea name="caseStudies[items][<?php echo $i; ?>][description]" rows="6"><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea></div>
                            
                            <!-- Product Image Upload -->
                            <div class="form-group">
                                <label>Product Image</label>
                                <div class="image-upload-wrapper">
                                    <input type="file" class="form-control" onchange="uploadAsset(this, 'preview_<?php echo $key.$i; ?>', 'input_<?php echo $key.$i; ?>')">
                                    <input type="hidden" name="caseStudies[items][<?php echo $i; ?>][image]" id="input_<?php echo $key.$i; ?>" value="<?php echo htmlspecialchars($item['image'] ?? ''); ?>">
                                    <?php if(!empty($item['image'])): ?>
                                        <div class="image-preview-container" style="margin-top:5px;max-width:100px;">
                                            <img id="preview_<?php echo $key.$i; ?>" src="assets/<?php echo htmlspecialchars($item['image']); ?>" style="width:100%;border-radius:4px;">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-row"><div class="form-group"><label>CTA Text</label><input type="text" name="caseStudies[items][<?php echo $i; ?>][ctaText]" value="<?php echo htmlspecialchars($item['ctaText'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Icon</label><input type="text" name="caseStudies[items][<?php echo $i; ?>][iconClass]" value="<?php echo htmlspecialchars($item['iconClass'] ?? ''); ?>"></div></div>
                            <div class="form-group"><label>Preview Text</label><input type="text" name="caseStudies[items][<?php echo $i; ?>][previewText]" value="<?php echo htmlspecialchars($item['previewText'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Features</label><div id="cs-features-container-<?php echo $i; ?>">
                                <?php foreach ($item['features'] ?? [] as $fi => $f): ?>
                                    <div class="array-item" style="padding:5px"><div style="display:flex;align-items:center;gap:10px;"><textarea name="caseStudies[items][<?php echo $i; ?>][features][<?php echo $fi; ?>]" rows="6" style="flex:1"><?php echo htmlspecialchars($f); ?></textarea><button type="button" class="btn-remove" style="padding:5px 10px;width:auto;flex-shrink:0;" onclick="removeArrayItem(this)">x</button></div></div>
                                <?php endforeach; ?>
                            </div><button type="button" class="btn-add" onclick="addCaseStudyFeature(<?php echo $i; ?>)">+ Add Feature</button></div>
                            <div class="form-group" style="display:none;"><div class="checkbox-group"><input type="checkbox" name="caseStudies[items][<?php echo $i; ?>][reverseLayout]" value="1" <?php echo ($item['reverseLayout'] ?? false) ? 'checked' : ''; ?>><label>Reverse Layout</label></div></div></div>
                        <?php endforeach; ?>
                    </div><button type="button" class="btn-add" onclick="addCaseStudy()">+ Add Product</button></div>
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
                    
                    <div class="form-group">
                        <label>What Happens Next (Sidebar Steps)</label>
                        <div id="contact-whatNext-container">
                            <?php foreach ($data['whatNextSteps'] ?? [] as $i => $item): ?>
                                <div class="array-item">
                                    <div class="array-item-header"><span>Step #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                                    <div class="form-group"><input type="text" name="contact[whatNextSteps][<?php echo $i; ?>]" value="<?php echo htmlspecialchars($item); ?>"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addContactWhatNext()">+ Add Step</button>
                    </div>

                    <div class="form-group">
                        <label>Product Interest Options</label>
                        <div id="contact-products-container">
                            <?php foreach ($data['products'] ?? [] as $i => $item): ?>
                                <div class="array-item">
                                    <div class="array-item-header"><span>Option #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                                    <div class="form-group"><input type="text" name="contact[products][<?php echo $i; ?>]" value="<?php echo htmlspecialchars($item); ?>"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addContactProduct()">+ Add Option</button>
                    </div>

                    <div class="form-group">
                        <label>Stats (Sidebar)</label>
                        <div id="contact-stats-container">
                            <?php foreach ($data['stats'] ?? [] as $i => $item): ?>
                                <div class="array-item">
                                    <div class="array-item-header"><span>Stat #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                                    <div class="form-row">
                                        <div class="form-group"><label>Number</label><input type="text" name="contact[stats][<?php echo $i; ?>][number]" value="<?php echo htmlspecialchars($item['number'] ?? ''); ?>"></div>
                                        <div class="form-group"><label>Label</label><input type="text" name="contact[stats][<?php echo $i; ?>][label]" value="<?php echo htmlspecialchars($item['label'] ?? ''); ?>"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addContactStat()">+ Add Stat</button>
                    </div>

                    <div class="form-group">
                        <label>Trust Lines (Footer)</label>
                        <div id="contact-trustLines-container">
                            <?php foreach ($data['trustLines'] ?? [] as $i => $item): ?>
                                <div class="array-item">
                                    <div class="array-item-header"><span>Line #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                                    <div class="form-group"><input type="text" name="contact[trustLines][<?php echo $i; ?>]" value="<?php echo htmlspecialchars($item); ?>"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addContactTrustLine()">+ Add Line</button>
                    </div>
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
            $type = $data['type'] ?? 'generic';
            ?>
            <div class="form-section content-section" id="section-<?php echo $key; ?>">
                <div class="section-header">
                    <h2><span class="section-label"><?php echo htmlspecialchars($label); ?> (<?php echo ucfirst($type); ?>)</span></h2>
                    <div class="header-controls">
                        <?php echo renderToggle($key, $data); ?>
                        <button type="button" class="btn-icon" onclick="renameSection('<?php echo $key; ?>')" title="Rename">✏️</button>
                        <button type="button" class="btn-icon btn-remove-section" onclick="removeSection('<?php echo $key; ?>')" title="Remove">🗑️</button>
                    </div>
                </div>
                <div class="section-content">
                    <!-- Hidden Type Field -->
                    <input type="hidden" name="<?php echo $key; ?>[type]" value="<?php echo htmlspecialchars($type); ?>">
                    
                    <div class="form-group"><label>Section Title</label><input type="text" name="<?php echo $key; ?>[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                    
                    <?php if ($type === 'richtext'): ?>
                        <div class="form-group">
                            <label>Content (HTML Allowed)</label>
                            <textarea name="<?php echo $key; ?>[content]" rows="10" class="code-editor"><?php echo htmlspecialchars($data['content'] ?? ''); ?></textarea>
                        </div>
                    <?php elseif ($type === 'video'): ?>
                         <div class="form-group"><label>Video URL (YouTube/Vimeo)</label><input type="text" name="<?php echo $key; ?>[videoUrl]" value="<?php echo htmlspecialchars($data['videoUrl'] ?? ''); ?>"></div>
                         <div class="form-group"><label>Caption</label><input type="text" name="<?php echo $key; ?>[caption]" value="<?php echo htmlspecialchars($data['caption'] ?? ''); ?>"></div>
                    <?php elseif ($type === 'testimonials'): ?>
                        <div class="form-group"><label>Items</label>
                            <div id="<?php echo $key; ?>-container">
                                <?php foreach ($data['items'] ?? [] as $i => $item): ?>
                                    <div class="array-item"><div class="array-item-header"><span>Testimonial</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                                    <div class="form-row">
                                        <div class="form-group"><label>Name</label><input type="text" name="<?php echo $key; ?>[items][<?php echo $i; ?>][name]" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"></div>
                                        <div class="form-group"><label>Role/Company</label><input type="text" name="<?php echo $key; ?>[items][<?php echo $i; ?>][role]" value="<?php echo htmlspecialchars($item['role'] ?? ''); ?>"></div>
                                    </div>
                                    <div class="form-group"><label>Quote</label><textarea name="<?php echo $key; ?>[items][<?php echo $i; ?>][quote]" rows="3"><?php echo htmlspecialchars($item['quote'] ?? ''); ?></textarea></div>
                                    <div class="form-group"><label>Image URL (Optional)</label><input type="text" name="<?php echo $key; ?>[items][<?php echo $i; ?>][image]" value="<?php echo htmlspecialchars($item['image'] ?? ''); ?>"></div></div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn-add" onclick="addTestimonialItem('<?php echo $key; ?>')">+ Add Testimonial</button>
                        </div>
                    <?php elseif ($type === 'cta'): ?>
                        <div class="form-group"><label>Heading</label><input type="text" name="<?php echo $key; ?>[title]" value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"></div>
                        <div class="form-group"><label>Sub Text</label><textarea name="<?php echo $key; ?>[subText]"><?php echo htmlspecialchars($data['subText'] ?? ''); ?></textarea></div>
                        <div class="form-row">
                            <div class="form-group"><label>Button Text</label><input type="text" name="<?php echo $key; ?>[btnText]" value="<?php echo htmlspecialchars($data['btnText'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Button Link</label><input type="text" name="<?php echo $key; ?>[btnLink]" value="<?php echo htmlspecialchars($data['btnLink'] ?? ''); ?>"></div>
                        </div>
                        <div class="form-group"><label>Background Color (Hex)</label><input type="text" name="<?php echo $key; ?>[bgColor]" value="<?php echo htmlspecialchars($data['bgColor'] ?? ''); ?>"></div>
                    <?php elseif ($type === 'team'): ?>
                        <div class="form-group"><label>Sub Title</label><input type="text" name="<?php echo $key; ?>[subText]" value="<?php echo htmlspecialchars($data['subText'] ?? ''); ?>"></div>
                        <div class="form-group"><label>Team Members</label>
                            <div id="<?php echo $key; ?>-container">
                                <?php foreach ($data['items'] ?? [] as $i => $item): ?>
                                    <div class="array-item"><div class="array-item-header"><span>Member</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                                    <div class="form-row">
                                        <div class="form-group"><label>Name</label><input type="text" name="<?php echo $key; ?>[items][<?php echo $i; ?>][name]" value="<?php echo htmlspecialchars($item['name'] ?? ''); ?>"></div>
                                        <div class="form-group"><label>Role</label><input type="text" name="<?php echo $key; ?>[items][<?php echo $i; ?>][role]" value="<?php echo htmlspecialchars($item['role'] ?? ''); ?>"></div>
                                    </div>
                                    <div class="form-group"><label>Bio</label><textarea name="<?php echo $key; ?>[items][<?php echo $i; ?>][bio]" rows="2"><?php echo htmlspecialchars($item['bio'] ?? ''); ?></textarea></div>
                                    <div class="form-group"><label>Photo URL</label><input type="text" name="<?php echo $key; ?>[items][<?php echo $i; ?>][photo]" value="<?php echo htmlspecialchars($item['photo'] ?? ''); ?>"></div>
                                    <div class="form-group"><label>LinkedIn URL</label><input type="text" name="<?php echo $key; ?>[items][<?php echo $i; ?>][linkedin]" value="<?php echo htmlspecialchars($item['linkedin'] ?? ''); ?>"></div></div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn-add" onclick="addTeamMember('<?php echo $key; ?>')">+ Add Member</button>
                        </div>
                    <?php else: // Generic ?>
                        <div class="form-group"><label>Sub Title</label><input type="text" name="<?php echo $key; ?>[subText]" value="<?php echo htmlspecialchars($data['subText'] ?? ''); ?>"></div>
                        <div class="form-group"><label>Items</label><div id="<?php echo $key; ?>-container">
                            <?php foreach ($data['items'] ?? [] as $i => $item): 
                                $iTitle = is_array($item) ? ($item['title'] ?? '') : '';
                                $iDesc = is_array($item) ? ($item['description'] ?? '') : (is_string($item) ? $item : '');
                            ?>
                                <div class="array-item"><div class="array-item-header"><span>Item #<?php echo $i+1; ?></span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div>
                                <div class="form-group"><label>Item Title</label><input type="text" name="<?php echo $key; ?>[items][<?php echo $i; ?>][title]" value="<?php echo htmlspecialchars($iTitle); ?>"></div>
                                <div class="form-group"><label>Item Description</label><textarea name="<?php echo $key; ?>[items][<?php echo $i; ?>][description]"><?php echo htmlspecialchars($iDesc); ?></textarea></div></div>
                            <?php endforeach; ?>
                        </div><button type="button" class="btn-add" onclick="addCustomItem('<?php echo $key; ?>')">+ Add Item</button></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    
    $renderedSections[$key] = ob_get_clean();
}

// --- IMAGES (Logo) ---
ob_start(); ?>
<div class="form-section" id="section-images">
    <div class="section-header"><h2>🖼️ Logo</h2></div>
    <div class="section-content">
         <div class="form-group">
             <label>Upload Logo</label>
             <div class="image-upload-wrapper">
                 <input type="file" class="form-control" onchange="uploadAsset(this, 'logo_preview', null, 'logo.png')" accept="image/*">
                 <div class="image-preview-container" style="margin-top:10px;text-align:center;background:#f9fafb;padding:15px;border:1px dashed #d1d5db;border-radius:6px;">
                     <img id="logo_preview" src="assets/logo.png?v=<?php echo time(); ?>" style="max-height:100px;max-width:100%">
                 </div>
                 <p style="font-size:0.8rem;color:#666;margin-top:5px;">This overwrites the existing logo.png used by the site.</p>
             </div>
         </div>
    </div>
</div>
<?php $renderedSections['images'] = ob_get_clean();

// --- SOCIAL ---
ob_start(); ?>
<div class="form-section" id="section-socialMedia">
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
<div class="form-section" id="section-footer">
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
        /* Modern Sidebar Layout */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f3f4f6; color: #111827; margin: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden; font-size: 14px; }
        
        /* Top Header */
        .top-header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 0 20px; height: 60px; display: flex; align-items: center; justify-content: space-between; z-index: 50; flex-shrink: 0; }
        .top-header h1 { font-size: 1.25rem; font-weight: 600; margin: 0; color: #111827; }
        
        .editor-wrapper { display: flex; flex: 1; overflow: hidden; }
        
        /* Sidebar */
        .sidebar { width: 280px; background: #fff; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-content { flex: 1; overflow-y: auto; padding: 20px 0; }
        .sidebar-group-title { 
            padding: 0 20px; margin-bottom: 10px; font-size: 0.75rem; 
            text-transform: uppercase; color: #6b7280; font-weight: 600; letter-spacing: 0.05em; 
            margin-top: 20px;
        }
        .sidebar-group-title.first { margin-top: 0; }
        
        .nav-item { 
            padding: 10px 20px; cursor: pointer; border-left: 3px solid transparent; 
            display: flex; align-items: center; gap: 10px; transition: background 0.15s;
            color: #374151; font-weight: 500;
        }
        .nav-item:hover { background: #f9fafb; color: #111827; }
        .nav-item.active { background: #eff6ff; border-left-color: #3b82f6; color: #2563eb; }
        .nav-icon { opacity: 0.5; }
        .nav-handle { cursor: grab; opacity: 0.3; margin-right: 5px; }
        .nav-handle:hover { opacity: 1; }
        
        .sidebar-footer { padding: 20px; border-top: 1px solid #e5e7eb; background: #f9fafb; }
        
        /* Main Content */
        .main-content { flex: 1; overflow-y: auto; padding: 40px; scroll-behavior: smooth; }
        .main-container { max-width: 800px; margin: 0 auto; padding-bottom: 100px; }
        .main-container.page-editor-active { max-width: 100%; padding: 20px; }
        
        /* Form Sections */
        .form-section { display: none; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .form-section.active-section { display: block; animation: fadeIn 0.2s; }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        .section-header { 
            background: #f9fafb; padding: 15px 20px; border-bottom: 1px solid #e5e7eb; 
            display: flex; justify-content: space-between; align-items: center; 
        }
        .section-header h2 { margin: 0; font-size: 1.1rem; font-weight: 600; }
        .section-content { padding: 30px; }
        
        /* Common Elements */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 500; color: #374151; }
        .form-group input, .form-group textarea, .form-control { 
            width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; 
            font-size: 0.95rem; box-sizing: border-box; 
        }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .code-editor { font-family: monospace; background: #fafafa; font-size: 0.9rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 8px 16px; border-radius: 6px; font-weight: 500; cursor: pointer; border: none; text-decoration: none; font-size: 0.9rem; transition: all 0.2s; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-success { background: #10b981; color: white; width: 100%; }
        .btn-success:hover { background: #059669; }
        .btn-secondary { background: white; border: 1px solid #d1d5db; color: #374151; }
        .btn-secondary:hover { background: #f9fafb; border-color: #9ca3af; }
        
        .btn-add { width: 100%; background: #eff6ff; border: 1px dashed #bfdbfe; color: #2563eb; padding: 10px; margin-top: 10px; }
        .btn-add:hover { background: #dbeafe; border-color: #93c5fd; }
        
        .btn-remove { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 4px 10px; font-size: 0.75rem; border-radius: 4px; }
        .btn-remove:hover { background: #fee2e2; }
        
        /* Array Items */
        .array-item { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
        .array-item-header { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.8rem; font-weight: 600; color: #9ca3af; text-transform: uppercase; }
        
        /* Controls */
        .header-controls { display: flex; gap: 10px; align-items: center; }
        .btn-icon { background: none; border: none; font-size: 1.1rem; opacity: 0.6; cursor: pointer; padding: 5px; }
        .btn-icon:hover { opacity: 1; background: rgba(0,0,0,0.05); border-radius: 4px; }
        .btn-remove-section { color: #dc2626; }
        
        /* Toggle */
        .toggle-wrapper { display: flex; align-items: center; gap: 8px; margin-right: 10px; }
        .toggle-switch { position: relative; display: inline-block; width: 32px; height: 18px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #d1d5db; transition: .3s; border-radius: 18px; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 2px; bottom: 2px; background-color: white; transition: .3s; border-radius: 50%; }
        input:checked + .slider { background-color: #ef4444; }
        input:checked + .slider:before { transform: translateX(14px); }
        .toggle-label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }

        /* Hide logic */
        #restore-controls { display: none !important; }
        .sidebar-restore-area { margin-top: 15px; padding-top: 15px; border-top: 1px dashed #e5e7eb; }
        .sidebar-restore-area h4 { font-size: 0.7rem; text-transform: uppercase; color: #9ca3af; margin: 0 0 10px 0; }
    </style>
</head>
<body>
    <div class="top-header">
        <h1>📝 Content Editor</h1>
        <div class="header-actions">
            <a href="#" class="btn btn-secondary" onclick="openHistory(event)">📜 History</a>
            <a href="/" target="_blank" class="btn btn-secondary">View Site</a>
            <a href="?logout=1" class="btn btn-secondary">Logout</a>
        </div>
    </div>
    
    <form id="editor-form" class="editor-wrapper">
        <aside class="sidebar">
            <div class="sidebar-content">
                <div class="sidebar-group-title first">Global Settings</div>
                <div class="nav-item" onclick="activateSection('siteMeta', this)">🌐 Site Meta</div>
                <div class="nav-item" onclick="activateSection('customCode', this)">💻 Custom Code</div>
                <div class="nav-item" onclick="activateSection('header', this)">📋 Menu/Header</div>
                <div class="nav-item" onclick="activateSection('images', this)">🖼️ Logo</div>
                <div class="nav-item" onclick="activateSection('socialMedia', this)">📱 Social Media</div>
                <div class="nav-item" onclick="activateSection('footer', this)">📄 Footer</div>
                
                <div class="sidebar-group-title">Legal Pages</div>
                <div class="nav-item" onclick="activatePageEdit('privacy-policy.php', this)">🔒 Privacy Policy</div>
                <div class="nav-item" onclick="activatePageEdit('terms-conditions.php', this)">⚖️ Terms & Conditions</div>
                
                <div class="sidebar-group-title">Page Content</div>
                <div id="sidebar-sortable">
                    <?php foreach ($currentOrder as $key): 
                        if (!isset($renderedSections[$key])) continue;
                        $label = getLabel($key, ucfirst($key));
                    ?>
                        <div class="nav-item" data-key="<?php echo $key; ?>" onclick="activateSection('<?php echo $key; ?>', this)">
                            <span class="nav-handle">☰</span>
                            <span class="nav-label"><?php echo htmlspecialchars($label); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="sidebar-restore-area" id="restore-controls" style="<?php echo empty($availableKeys) ? 'display:none' : ''; ?>">
                    <h4>Restore Section</h4>
                    <select id="add-section-select" class="form-control" style="margin-bottom:8px; font-size:0.8rem;">
                        <option value="">-- Select --</option>
                        <?php foreach ($availableKeys as $key): ?>
                            <option value="<?php echo $key; ?>"><?php echo ucfirst($key); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-secondary" style="width:100%; font-size:0.8rem;" onclick="restoreSection()">Restore</button>
                </div>
            </div>
            <div class="sidebar-footer">
                <button type="button" class="btn btn-primary" style="width:100%; margin-bottom:10px;" onclick="createNewSection()">+ New Section</button>
                <button type="submit" class="btn btn-success">💾 Save Changes</button>
            </div>
        </aside>
        
        <main class="main-content">
            <div class="main-container">
                <!-- Render All Sections (Hidden by default) -->
                <?php 
                echo $renderedSections['siteMeta'];
                echo $renderedSections['customCode'];
                echo $renderedSections['header'];
                echo $renderedSections['images'];
                echo $renderedSections['socialMedia'];
                echo $renderedSections['footer'];
                
                // Content sections
                foreach ($currentOrder as $key) {
                    if (isset($renderedSections[$key])) {
                        echo $renderedSections[$key];
                    }
                }
                ?>
                
                <!-- Store for removed sections -->
                <div id="section-store" style="display:none">
                    <?php 
                    foreach ($availableKeys as $key) {
                        if (isset($renderedSections[$key])) {
                            echo $renderedSections[$key];
                        }
                    }
                    ?>
                </div>

                <!-- Page Editor (Hidden by default) -->
                <div id="page-editor-container" style="display:none; background:#fff; border-radius:8px; border:1px solid #e5e7eb; overflow:hidden; width:100%;">
                    <div class="section-header" style="margin-bottom:0; padding:15px 20px;">
                        <h2 id="page-editor-title" style="margin:0;">Edit Page</h2>
                    </div>
                    <div style="display:flex; height:calc(100vh - 180px); min-height:700px;">
                        <div style="flex:1; display:flex; flex-direction:column; border-right:1px solid #e5e7eb; min-width:0;">
                            <div style="padding:15px 20px; background:#f9fafb; border-bottom:1px solid #e5e7eb; font-size:0.85rem; font-weight:600; color:#6b7280;">
                                📝 Editor
                            </div>
                            <div style="flex:1; padding:20px; overflow-y:auto;">
                                <textarea id="page-editor-content" rows="30" class="code-editor" style="width:100%; height:100%; font-family:monospace; border:none; resize:none; outline:none;"></textarea>
                            </div>
                            <div style="padding:15px 20px; border-top:1px solid #e5e7eb; background:#f9fafb;">
                                <input type="hidden" id="current-page-file">
                                <button type="button" class="btn btn-success" onclick="savePageContent()">💾 Save Page</button>
                            </div>
                        </div>
                        <div style="flex:1; display:flex; flex-direction:column; min-width:0;">
                            <div style="padding:15px 20px; background:#f9fafb; border-bottom:1px solid #e5e7eb; font-size:0.85rem; font-weight:600; color:#6b7280;">
                                👁️ Preview
                            </div>
                            <iframe id="page-preview-frame" style="flex:1; width:100%; border:none; background:#fff;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Hidden Inputs -->
        <input type="hidden" name="sectionOrder" id="input-sectionOrder" value="">
        <div id="labels-container">
            <?php foreach ($sectionLabels as $k => $v): ?>
                <input type="hidden" name="sectionLabels[<?php echo $k; ?>]" id="label-<?php echo $k; ?>" value="<?php echo htmlspecialchars($v); ?>">
            <?php endforeach; ?>
        </div>
    </form>

    <script>
        // Init: Hide standard delete buttons
        document.addEventListener('DOMContentLoaded', function() {
            const standardKeys = <?php echo json_encode($defaultContentKeys); ?>;
            standardKeys.forEach(key => {
                const btn = document.querySelector(`#section-${key} .btn-remove-section`);
                if(btn) btn.style.display = 'none';
            });
            
            // Activate first section by default
            const firstNav = document.querySelector('.nav-item');
            if(firstNav) firstNav.click();
        });

        // Navigation Logic
        function activateSection(key, navEl) {
            // Update Nav UI
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            if(navEl) navEl.classList.add('active');
            
            // Hide Page Editor if open and restore container width
            document.getElementById('page-editor-container').style.display = 'none';
            const mainContainer = document.querySelector('.main-container');
            mainContainer.classList.remove('page-editor-active');

            // Update Content UI
            document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active-section'));
            const target = document.getElementById(`section-${key}`);
            if(target) target.classList.add('active-section');
        }

        // Page Editing Logic
        async function activatePageEdit(filename, navEl) {
             // Update Nav UI
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            if(navEl) navEl.classList.add('active');
            
            // Hide standard sections
            document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active-section'));
            
            // Expand main container for page editor
            const mainContainer = document.querySelector('.main-container');
            mainContainer.classList.add('page-editor-active');
            
            // Show Page Editor
            const container = document.getElementById('page-editor-container');
            container.style.display = 'block';
            
            const titleMap = {
                'privacy-policy.php': '🔒 Edit Privacy Policy',
                'terms-conditions.php': '⚖️ Edit Terms & Conditions'
            };
            document.getElementById('page-editor-title').textContent = titleMap[filename] || 'Edit Page';
            document.getElementById('current-page-file').value = filename;
            
            // Load content
            const textarea = document.getElementById('page-editor-content');
            textarea.value = 'Loading...';
            
            try {
                // We'll just fetch the file content directly since we are in same dir
                // Add timestamp to prevent caching
                const response = await fetch(filename + '?v=' + new Date().getTime());
                const text = await response.text();
                textarea.value = text;
                
                // Update preview
                updatePagePreview(text);
                
                // Add event listener for live preview (remove old one first to avoid duplicates)
                textarea.removeEventListener('input', textarea._previewHandler);
                textarea._previewHandler = function() {
                    updatePagePreview(this.value);
                };
                textarea.addEventListener('input', textarea._previewHandler);
            } catch(e) {
                textarea.value = 'Error loading file: ' + e.message;
            }
        }

        function updatePagePreview(htmlContent) {
            const previewFrame = document.getElementById('page-preview-frame');
            const doc = previewFrame.contentDocument || previewFrame.contentWindow.document;
            doc.open();
            doc.write(htmlContent);
            doc.close();
        }

        async function savePageContent() {
            const filename = document.getElementById('current-page-file').value;
            const content = document.getElementById('page-editor-content').value;
            
            if(!filename) return;

            Swal.fire({ title: 'Saving Page...', didOpen: () => Swal.showLoading() });
            
            try {
                const formData = new FormData();
                formData.append('file', filename);
                formData.append('content', content);
                
                const response = await fetch('save_pages.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({ icon: 'success', title: 'Page Saved!', timer: 1500, showConfirmButton: false });
                } else {
                    throw new Error(result.error || 'Save failed');
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: e.message });
            }
        }

        // Upload Asset Function
        async function uploadAsset(input, previewId, valueInputId, fixedFilename = null) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const formData = new FormData();
            formData.append('image', file);
            if (fixedFilename) formData.append('fixedFilename', fixedFilename);

            Swal.fire({
                title: 'Uploading...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch('upload_image.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    Swal.fire({ icon: 'success', title: 'Uploaded!', timer: 1000, showConfirmButton: false });
                    
                    // Update preview if ID provided
                    if (previewId) {
                        const preview = document.getElementById(previewId);
                        if (preview) {
                            preview.src = result.url + '?v=' + new Date().getTime();
                            preview.parentElement.style.display = 'block';
                        }
                    }
                    
                    // Update value input if ID provided (stores the filename)
                    if (valueInputId) {
                        document.getElementById(valueInputId).value = result.filename;
                    }
                } else {
                    throw new Error(result.error || 'Upload failed');
                }
            } catch (e) {
                Swal.fire('Error', e.message, 'error');
                input.value = ''; // Reset input
            }
        }

        // Sortable Sidebar
        const sidebarList = document.getElementById('sidebar-sortable');
        new Sortable(sidebarList, {
            animation: 150,
            handle: '.nav-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                updateOrderInput();
            }
        });

        // Sortable Nav Links
        const navLinkList = document.getElementById('navLinks-container');
        if(navLinkList) {
            new Sortable(navLinkList, {
                animation: 150,
                handle: '.nav-handle',
                ghostClass: 'sortable-ghost'
            });
        }

        function updateOrderInput() {
            const order = [];
            document.querySelectorAll('#sidebar-sortable .nav-item').forEach(el => {
                const key = el.getAttribute('data-key');
                if(key) order.push(key);
            });
            document.getElementById('input-sectionOrder').value = JSON.stringify(order);
        }

        function renameSection(key) {
            const labelSpan = document.querySelector(`#section-${key} .section-label`);
            // Also find label in sidebar
            const sidebarLabel = document.querySelector(`.nav-item[data-key="${key}"] .nav-label`);
            
            const currentLabel = labelSpan.textContent;
            
            Swal.fire({
                title: 'Rename Section',
                input: 'text',
                inputValue: currentLabel,
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const newLabel = result.value;
                    if(labelSpan) labelSpan.textContent = newLabel;
                    if(sidebarLabel) sidebarLabel.textContent = newLabel;
                    
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

        const standardSectionKeys = <?php echo json_encode($defaultContentKeys); ?>;
        
        function removeSection(key) {
            const isStandard = standardSectionKeys.includes(key);
            
            if (isStandard) {
                // Move to restore
                const navItem = document.querySelector(`.nav-item[data-key="${key}"]`);
                const section = document.getElementById(`section-${key}`);
                const store = document.getElementById('section-store');
                const select = document.getElementById('add-section-select');
                
                if(navItem) navItem.remove();
                if(section) {
                    section.classList.remove('active-section');
                    store.appendChild(section);
                }
                
                // Add to restore select
                const opt = document.createElement('option');
                opt.value = key;
                opt.textContent = document.getElementById(`label-${key}`)?.value || key; // simplified
                select.appendChild(opt);
                
                document.getElementById('restore-controls').style.display = 'block';
                
            } else {
                // Custom: Delete
                Swal.fire({
                    title: 'Delete Section?',
                    text: "Permanently delete this section?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const navItem = document.querySelector(`.nav-item[data-key="${key}"]`);
                        const section = document.getElementById(`section-${key}`);
                        
                        if(navItem) navItem.remove();
                        if(section) {
                            // Disable inputs to prevent submission
                            section.querySelectorAll('input,textarea,select').forEach(i => i.disabled = true);
                            section.style.display = 'none'; // Hide instead of remove to keep form clean? No, remove is fine if we add deleted marker.
                            
                            const deletedInput = document.createElement('input');
                            deletedInput.type = 'hidden';
                            deletedInput.name = `_deletedSections[]`;
                            deletedInput.value = key;
                            document.getElementById('labels-container').appendChild(deletedInput);
                        }
                    }
                });
            }
        }

        function restoreSection() {
            const select = document.getElementById('add-section-select');
            const key = select.value;
            if(!key) return;
            
            const section = document.getElementById(`section-${key}`);
            const mainContainer = document.querySelector('.main-container');
            
            if(section) {
                mainContainer.appendChild(section);
                
                // Re-create sidebar item
                const title = section.querySelector('.section-label').textContent;
                const navHtml = `
                    <div class="nav-item" data-key="${key}" onclick="activateSection('${key}', this)">
                        <span class="nav-handle">☰</span>
                        <span class="nav-label">${title}</span>
                    </div>
                `;
                document.getElementById('sidebar-sortable').insertAdjacentHTML('beforeend', navHtml);
                
                // Remove from select
                select.querySelector(`option[value="${key}"]`).remove();
                if(select.options.length <= 1) document.getElementById('restore-controls').style.display = 'none';
                
                // Activate it
                const newNav = document.querySelector(`.nav-item[data-key="${key}"]`);
                if(newNav) newNav.click();
            }
        }

        function createNewSection() {
            Swal.fire({
                title: 'New Section ID',
                input: 'text',
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const id = result.value.replace(/[^a-zA-Z0-9]/g, '');
                    if(!id || document.getElementById(`section-${id}`)) {
                        Swal.fire('Error', 'Invalid or duplicate ID', 'error');
                        return;
                    }
                    
                    // Type selection
                    Swal.fire({
                        title: 'Section Type',
                        input: 'select',
                        inputOptions: {
                            'generic': 'Generic',
                            'richtext': 'Rich Text',
                            'video': 'Video',
                            'testimonials': 'Testimonials',
                            'cta': 'CTA Banner',
                            'team': 'Team'
                        },
                        showCancelButton: true
                    }).then((typeRes) => {
                        if(typeRes.isConfirmed) {
                            const type = typeRes.value || 'generic';
                            const title = 'New Section'; // Default title
                            
                            // Generate Content HTML (Simplified for brevity, copying logic)
                            let contentFields = '';
                            if(type === 'richtext') {
                                contentFields = `<div class="form-group"><label>Content</label><textarea name="${id}[content]" class="code-editor" rows="10"></textarea></div>`;
                            } else if(type === 'video') {
                                contentFields = `<div class="form-group"><label>URL</label><input type="text" name="${id}[videoUrl]"></div>`;
                            } else if(type === 'cta') {
                                contentFields = `<div class="form-group"><label>Btn Text</label><input type="text" name="${id}[btnText]"></div>`;
                            } else {
                                contentFields = `<div class="form-group"><label>Items</label><div id="${id}-container"></div><button type="button" class="btn-add" onclick="addCustomItem('${id}')">+ Item</button></div>`;
                            }
                            
                            const html = `
                                <div class="form-section content-section" id="section-${id}">
                                    <div class="section-header">
                                        <h2><span class="section-label">${title}</span> (${type})</h2>
                                        <div class="header-controls">
                                            <div class="toggle-wrapper"><label class="toggle-switch"><input type="checkbox" name="${id}[disabled]" value="1"><span class="slider"></span></label></div>
                                            <button type="button" class="btn-icon" onclick="renameSection('${id}')">✏️</button>
                                            <button type="button" class="btn-icon btn-remove-section" onclick="removeSection('${id}')">🗑️</button>
                                        </div>
                                    </div>
                                    <div class="section-content">
                                        <input type="hidden" name="${id}[type]" value="${type}">
                                        <div class="form-group"><label>Title</label><input type="text" name="${id}[title]" value="${title}"></div>
                                        ${contentFields}
                                    </div>
                                </div>
                            `;
                            
                            document.querySelector('.main-container').insertAdjacentHTML('beforeend', html);
                            
                            // Add Sidebar Item
                            const navHtml = `
                                <div class="nav-item" data-key="${id}" onclick="activateSection('${id}', this)">
                                    <span class="nav-handle">☰</span>
                                    <span class="nav-label">${title}</span>
                                </div>
                            `;
                            document.getElementById('sidebar-sortable').insertAdjacentHTML('beforeend', navHtml);
                            
                            // Add Label Input
                            const l = document.createElement('input');
                            l.type='hidden'; l.name=`sectionLabels[${id}]`; l.id=`label-${id}`; l.value=title;
                            document.getElementById('labels-container').appendChild(l);
                            
                            // Activate
                            const newNav = document.querySelector(`.nav-item[data-key="${id}"]`);
                            if(newNav) newNav.click();
                        }
                    });
                }
            });
        }

        // Helpers for adding items (same as before)
        function addCustomItem(key) { 
            const i = Math.floor(Math.random()*10000);
            const h = `<div class="array-item"><div class="array-item-header"><span>Item</span><button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">Remove</button></div><div class="form-group"><label>Title</label><input type="text" name="${key}[items][${i}][title]"></div><div class="form-group"><label>Desc</label><textarea name="${key}[items][${i}][description]"></textarea></div></div>`;
            document.getElementById(`${key}-container`).insertAdjacentHTML('beforeend', h);
        }
        function addTestimonialItem(key) { addCustomItem(key); /* simplified for now, user can refine if needed */ }
        function addTeamMember(key) { addCustomItem(key); }
        function removeArrayItem(btn) { btn.closest('.array-item').remove(); }
        
        // Standard Adders
        let c = 100;
        function addNavLink() { document.getElementById('navLinks-container').insertAdjacentHTML('beforeend', `<div class="array-item"><div class="array-item-header"><span>Link</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-row"><div class="form-group"><input name="header[navLinks][${c}][text]" placeholder="Text"></div><div class="form-group"><input name="header[navLinks][${c}][href]" placeholder="URL"></div></div></div>`); c++; }
        function addService() { document.getElementById('services-container').insertAdjacentHTML('beforeend', `<div class="array-item"><button type="button" class="btn-remove" onclick="removeArrayItem(this)" style="float:right">x</button><input name="services[items][${c}][title]" placeholder="Title"><textarea name="services[items][${c}][description]" placeholder="Desc"></textarea></div>`); c++; }
        // ... (Keep other adders if needed, they are preserved in HTML logic if simple, else copy full logic)
        // Re-injecting full adders to be safe:
        function addIndustry() {
            const i = c++;
            const html = `<div class="array-item"><div class="array-item-header"><span>Industry</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-row"><div class="form-group"><label>Name</label><input type="text" name="industries[items][${i}][name]"></div><div class="form-group"><label>Icon</label><input type="text" name="industries[items][${i}][iconClass]"></div></div><div class="form-group"><label>Description</label><textarea name="industries[items][${i}][description]"></textarea></div><div class="form-group"><label>Best Fit</label><input type="text" name="industries[items][${i}][bestFit]"></div><div class="form-group"><label>Color</label><input type="text" name="industries[items][${i}][color]"></div></div>`;
            document.getElementById('industries-container').insertAdjacentHTML('beforeend', html);
        }
        function addTrustItem() {
            const i = c++;
            const html = `<div class="array-item"><div class="array-item-header"><span>Item</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><input type="text" name="trust[items][${i}]"></div></div>`;
            document.getElementById('trust-container').insertAdjacentHTML('beforeend', html);
        }
        function addCaseStudy() {
            const i = c++;
            const html = `<div class="array-item"><div class="array-item-header"><span>Product</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><label>Title</label><input type="text" name="caseStudies[items][${i}][title]"></div><div class="form-group"><label>Description</label><textarea name="caseStudies[items][${i}][description]"></textarea></div><div class="form-row"><div class="form-group"><label>CTA Text</label><input type="text" name="caseStudies[items][${i}][ctaText]"></div><div class="form-group"><label>Icon</label><input type="text" name="caseStudies[items][${i}][iconClass]"></div></div><div class="form-group"><label>Preview Text</label><input type="text" name="caseStudies[items][${i}][previewText]"></div><div class="form-group"><label>Product Image</label><div class="image-upload-wrapper"><input type="file" class="form-control" onchange="uploadAsset(this, 'preview_cs${i}', 'input_cs${i}')"><input type="hidden" name="caseStudies[items][${i}][image]" id="input_cs${i}"><div class="image-preview-container" style="margin-top:5px;max-width:100px;display:none;"><img id="preview_cs${i}" style="width:100%;border-radius:4px;"></div></div></div><div class="form-group"><label>Features</label><div id="cs-features-container-${i}"></div><button type="button" class="btn-add" onclick="addCaseStudyFeature(${i})">+ Add Feature</button></div><div class="form-group"><div class="checkbox-group"><input type="checkbox" name="caseStudies[items][${i}][reverseLayout]" value="1"><label>Reverse Layout</label></div></div></div>`;
            document.getElementById('caseStudies-container').insertAdjacentHTML('beforeend', html);
        }
        function addCaseStudyFeature(csI) {
            const fI = c++;
            const html = `<div class="array-item" style="padding:5px"><div style="display:flex;align-items:center;gap:10px;"><textarea name="caseStudies[items][${csI}][features][${fI}]" rows="6" style="flex:1"></textarea><button type="button" class="btn-remove" style="padding:5px 10px;width:auto;flex-shrink:0;" onclick="removeArrayItem(this)">x</button></div></div>`;
            document.getElementById(`cs-features-container-${csI}`).insertAdjacentHTML('beforeend', html);
        }
        function addEngItem() {
            const i = c++;
            const html = `<div class="array-item"><div class="array-item-header"><span>Tile</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><label>Text</label><input type="text" name="engagement[items][${i}][text]"></div><div class="form-row"><div class="form-group"><label>Link Text</label><input type="text" name="engagement[items][${i}][linkText]"></div><div class="form-group"><label>URL</label><input type="text" name="engagement[items][${i}][link]"></div></div></div>`;
            document.getElementById('eng-container').insertAdjacentHTML('beforeend', html);
        }
        function addFaqItem() {
            const i = c++;
            const html = `<div class="array-item"><div class="array-item-header"><span>Item</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><label>Question</label><input type="text" name="faq[items][${i}][question]"></div><div class="form-group"><label>Answer</label><textarea name="faq[items][${i}][answer]"></textarea></div></div>`;
            document.getElementById('faq-container').insertAdjacentHTML('beforeend', html);
        }
        function addContactProduct() {
            const i = c++;
            const html = `<div class="array-item"><div class="array-item-header"><span>Option</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><input type="text" name="contact[products][${i}]"></div></div>`;
            document.getElementById('contact-products-container').insertAdjacentHTML('beforeend', html);
        }
        function addContactWhatNext() {
            const i = c++;
            const html = `<div class="array-item"><div class="array-item-header"><span>Step</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><input type="text" name="contact[whatNextSteps][${i}]"></div></div>`;
            document.getElementById('contact-whatNext-container').insertAdjacentHTML('beforeend', html);
        }
        function addContactStat() {
            const i = c++;
            const html = `<div class="array-item"><div class="array-item-header"><span>Stat</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-row"><div class="form-group"><label>Number</label><input type="text" name="contact[stats][${i}][number]"></div><div class="form-group"><label>Label</label><input type="text" name="contact[stats][${i}][label]"></div></div></div>`;
            document.getElementById('contact-stats-container').insertAdjacentHTML('beforeend', html);
        }
        function addContactTrustLine() {
            const i = c++;
            const html = `<div class="array-item"><div class="array-item-header"><span>Line</span><button type="button" class="btn-remove" onclick="removeArrayItem(this)">Remove</button></div><div class="form-group"><input type="text" name="contact[trustLines][${i}]"></div></div>`;
            document.getElementById('contact-trustLines-container').insertAdjacentHTML('beforeend', html);
        }
        function addPrinciple() {
            const i = c++;
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

        // Submit Handler
        document.getElementById('editor-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            updateOrderInput();
            const formData = new FormData(this);
            const data = {};
            
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
                if (lastKey === 'reverseLayout' || lastKey === 'disabled') {
                    current[lastKey] = (value === '1');
                } else if (lastKey === 'sectionOrder' && typeof value === 'string') {
                     try { current[lastKey] = JSON.parse(value); } catch(e) { current[lastKey] = []; }
                }
                else current[lastKey] = value;
            }

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
            
            deletedSections.forEach(sectionKey => {
                delete data[sectionKey];
                if (data.sectionLabels && data.sectionLabels[sectionKey]) delete data.sectionLabels[sectionKey];
            });
            
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