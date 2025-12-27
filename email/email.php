<?php
/**
 * Simple Email Handler using PHP mail()
 * Optimized for Shared Hosting (SiteGround, Bluehost, etc.)
 */

// Set JSON response header
header('Content-Type: application/json');

// 1. Get POST data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$product = isset($_POST['product']) ? trim($_POST['product']) : '';
$company = isset($_POST['company']) ? trim($_POST['company']) : '';
$role = isset($_POST['role']) ? trim($_POST['role']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$industry = isset($_POST['industry']) ? trim($_POST['industry']) : '';
$companySize = isset($_POST['company_size']) ? trim($_POST['company_size']) : '';
$bottleneck = isset($_POST['bottleneck']) ? trim($_POST['bottleneck']) : '';
$nextStep = isset($_POST['next_step']) ? trim($_POST['next_step']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$captchaAnswer = isset($_POST['captcha_answer']) ? trim($_POST['captcha_answer']) : '';
$captchaValue = isset($_POST['captcha_value']) ? intval($_POST['captcha_value']) : 0;

// 2. Validation
$errors = [];
if (empty($name)) $errors[] = 'Name is required';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';

// Validate captcha
if (empty($captchaAnswer) || intval($captchaAnswer) !== $captchaValue) {
    $errors[] = 'Invalid security question answer.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
    exit;
}

// 3. Configuration
// Load destination email from config if available, otherwise default
$to_email = 'vasilis_av@hotmail.com'; // Default fallback

$configFile = __DIR__ . '/../config/email_config.php';
if (file_exists($configFile)) {
    include $configFile;
    if (isset($smtp_to_email) && !empty($smtp_to_email)) {
        $to_email = $smtp_to_email;
    }
}

// 4. Construct Email
$subject = 'New Lead from Arinsol.ai: ' . ($product ?: 'General Inquiry');

// Important: "From" address must match the domain to avoid spam filters on shared hosting
$domain = $_SERVER['SERVER_NAME'] ?? 'arinsol.ai';
$from_email = "noreply@$domain"; 
$from_name = "Arinsol Website";

$headers = [];
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-type: text/html; charset=UTF-8";
$headers[] = "From: $from_name <$from_email>";
$headers[] = "Reply-To: $name <$email>";
$headers[] = "X-Mailer: PHP/" . phpversion();

$emailBody = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; }
        .header { background: #667eea; color: white; padding: 15px; text-align: center; }
        .field { margin-bottom: 10px; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; }
        .label { font-weight: bold; color: #555; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>New Lead: " . htmlspecialchars($product ?: 'General Inquiry') . "</h2>
        </div>
        <div style='padding: 20px;'>
            <div class='field'><span class='label'>Name:</span> " . htmlspecialchars($name) . "</div>
            <div class='field'><span class='label'>Email:</span> " . htmlspecialchars($email) . "</div>
            <div class='field'><span class='label'>Company:</span> " . htmlspecialchars($company ?: '-') . "</div>
            <div class='field'><span class='label'>Role:</span> " . htmlspecialchars($role ?: '-') . "</div>
            <div class='field'><span class='label'>Country:</span> " . htmlspecialchars($country ?: '-') . "</div>
            <div class='field'><span class='label'>Industry:</span> " . htmlspecialchars($industry ?: '-') . "</div>
            <div class='field'><span class='label'>Company Size:</span> " . htmlspecialchars($companySize ?: '-') . "</div>
            <div class='field'><span class='label'>Bottleneck:</span> " . htmlspecialchars($bottleneck ?: '-') . "</div>
            <div class='field'><span class='label'>Next Step:</span> " . htmlspecialchars($nextStep ?: '-') . "</div>
            <div class='field'>
                <div class='label'>Message:</div>
                <div style='margin-top: 5px; background: #f9f9f9; padding: 10px;'>" . nl2br(htmlspecialchars($message ?: 'No message')) . "</div>
            </div>
        </div>
    </div>
</body>
</html>";

// 5. Send Email
try {
    $mailSent = mail($to_email, $subject, $emailBody, implode("\r\n", $headers));

    if ($mailSent) {
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you! Your message has been sent successfully.'
        ]);
    } else {
        throw new Exception("Mail function returned false");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to send email. Please try again later.'
    ]);
    error_log("Email sending failed: " . $e->getMessage());
}
