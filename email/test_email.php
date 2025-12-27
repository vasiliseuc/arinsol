<?php
/**
 * Email Configuration Test Script
 * 
 * This script helps diagnose email configuration issues.
 * Access it via: your-domain.com/email/test_email.php
 * 
 * IMPORTANT: Delete this file after testing for security!
 */

// Check if config file exists
$configFile = __DIR__ . '/../config/email_config.php';
$configExists = file_exists($configFile);

echo "<h1>Email Configuration Test</h1>";
echo "<pre>";

echo "=== Configuration File Check ===\n";
echo "Config file path: " . $configFile . "\n";
echo "Config file exists: " . ($configExists ? "YES" : "NO") . "\n\n";

if ($configExists) {
    require_once $configFile;
    echo "Config file loaded successfully\n\n";
} else {
    echo "ERROR: Config file not found!\n\n";
}

// Check credentials
echo "=== Credentials Check ===\n";
$smtp_username = getenv('GMAIL_USERNAME') ?: (isset($smtp_username) ? $smtp_username : '');
$smtp_password = getenv('GMAIL_APP_PASSWORD') ?: (isset($smtp_password) ? $smtp_password : '');
$smtp_from_email = getenv('GMAIL_FROM_EMAIL') ?: (isset($smtp_from_email) ? $smtp_from_email : $smtp_username);
$smtp_to_email = getenv('GMAIL_TO_EMAIL') ?: (isset($smtp_to_email) ? $smtp_to_email : $smtp_username);

echo "SMTP Username: " . (empty($smtp_username) ? "NOT SET" : substr($smtp_username, 0, 3) . "***") . "\n";
echo "SMTP Password: " . (empty($smtp_password) ? "NOT SET" : "SET (" . strlen($smtp_password) . " characters)") . "\n";
echo "From Email: " . ($smtp_from_email ?: "NOT SET") . "\n";
echo "To Email: " . ($smtp_to_email ?: "NOT SET") . "\n\n";

// Check PHPMailer
echo "=== PHPMailer Check ===\n";
$phpmailerPath = __DIR__ . '/../vendor/autoload.php';
$phpmailerExists = file_exists($phpmailerPath);
echo "PHPMailer path: " . $phpmailerPath . "\n";
echo "PHPMailer exists: " . ($phpmailerExists ? "YES" : "NO") . "\n";

if ($phpmailerExists) {
    require_once $phpmailerPath;
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "PHPMailer class: AVAILABLE\n";
        $usePHPMailer = true;
    } else {
        echo "PHPMailer class: NOT FOUND (autoload file exists but class missing)\n";
        $usePHPMailer = false;
    }
} else {
    echo "PHPMailer: NOT INSTALLED\n";
    echo "Install with: composer require phpmailer/phpmailer\n";
    $usePHPMailer = false;
}
echo "\n";

// Test SMTP connection (if PHPMailer is available)
if ($usePHPMailer && !empty($smtp_username) && !empty($smtp_password)) {
    echo "=== SMTP Connection Test ===\n";
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 2; // Enable verbose debug output
        $mail->Debugoutput = function($str, $level) {
            echo "  " . trim($str) . "\n";
        };
        
        echo "Attempting SMTP connection...\n";
        $mail->smtpConnect();
        echo "SUCCESS: SMTP connection established!\n";
        $mail->smtpClose();
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "PHPMailer Error Info: " . $mail->ErrorInfo . "\n";
    }
} else {
    echo "=== SMTP Connection Test ===\n";
    echo "SKIPPED: PHPMailer not available or credentials not set\n";
}

echo "\n=== Summary ===\n";
if ($configExists && !empty($smtp_username) && !empty($smtp_password) && $usePHPMailer) {
    echo "✓ Configuration looks good!\n";
    echo "If emails still don't send, check:\n";
    echo "  1. Gmail App Password is correct (16 characters)\n";
    echo "  2. 2-Step Verification is enabled on Gmail account\n";
    echo "  3. Server error logs for detailed error messages\n";
} else {
    echo "✗ Issues found:\n";
    if (!$configExists) echo "  - Config file missing\n";
    if (empty($smtp_username)) echo "  - SMTP Username not set\n";
    if (empty($smtp_password)) echo "  - SMTP Password not set\n";
    if (!$usePHPMailer) echo "  - PHPMailer not installed\n";
}

echo "</pre>";
echo "<p><strong>Security Note:</strong> Delete this file after testing!</p>";

