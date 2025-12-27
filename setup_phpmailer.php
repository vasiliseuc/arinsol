<?php
/**
 * PHPMailer Auto-Installer for Shared Hosting
 * 
 * Usage:
 * 1. Upload this file to your public_html folder
 * 2. Visit your-site.com/setup_phpmailer.php
 * 3. Delete this file after success
 */

header('Content-Type: text/plain');
set_time_limit(300); // 5 minutes

echo "=== PHPMailer Installer ===\n\n";

$vendorDir = __DIR__ . '/vendor';
$zipFile = __DIR__ . '/phpmailer.zip';
$extractPath = __DIR__ . '/temp_extract';

// 1. Create vendor directory
if (!file_exists($vendorDir)) {
    mkdir($vendorDir, 0755, true);
    echo "Created vendor directory.\n";
}

// 2. Download PHPMailer
echo "Downloading PHPMailer...\n";
$url = 'https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.zip';
$fp = fopen($zipFile, 'w+');
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_TIMEOUT, 50);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);
curl_close($ch);
fclose($fp);

if (!file_exists($zipFile) || filesize($zipFile) < 1000) {
    die("Error: Failed to download PHPMailer.\n");
}
echo "Download complete.\n";

// 3. Extract
echo "Extracting files...\n";
$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($extractPath);
    $zip->close();
    echo "Extraction complete.\n";
} else {
    die("Error: Could not extract zip file.\n");
}

// 4. Move files
$source = $extractPath . '/PHPMailer-6.9.1/src';
$dest = $vendorDir . '/phpmailer/phpmailer/src';

if (!file_exists(dirname($dest))) {
    mkdir(dirname($dest), 0755, true);
}

// Copy files recursively
function recurseCopy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurseCopy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

recurseCopy($source, $dest);
echo "Files installed to vendor/phpmailer/phpmailer/src\n";

// 5. Create autoloader
$autoloadContent = "<?php
spl_autoload_register(function (\$class) {
    if (strpos(\$class, 'PHPMailer\\\\PHPMailer\\\\') === 0) {
        \$file = __DIR__ . '/phpmailer/phpmailer/src/' . substr(\$class, 20) . '.php';
        if (file_exists(\$file)) {
            require \$file;
        }
    }
});
";

file_put_contents($vendorDir . '/autoload.php', $autoloadContent);
echo "Created vendor/autoload.php\n";

// 6. Cleanup
unlink($zipFile);
// Simple recursive delete for temp dir
function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        return;
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}
deleteDir($extractPath);

echo "\nSUCCESS! PHPMailer is installed.\n";
echo "You can now run test_email.php to verify.\n";
echo "IMPORTANT: Delete setup_phpmailer.php from your server.";

