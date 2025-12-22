<?php
/**
 * Email Handler using Gmail SMTP
 * Supports PHPMailer (recommended) or fallback to mail() function
 */

// Set JSON response header
header('Content-Type: application/json');

// Get POST data
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

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

// Optional: Message validation (can be empty if other fields provide enough context, but usually good to keep)
// if (empty($message)) { $errors[] = 'Message is required'; }

// Validate captcha
if (empty($captchaAnswer) || intval($captchaAnswer) !== $captchaValue) {
    $errors[] = 'Invalid captcha answer. Please try again.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
    exit;
}

// Gmail SMTP Configuration
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_username = getenv('GMAIL_USERNAME') ?: ''; // Your Gmail address
$smtp_password = getenv('GMAIL_APP_PASSWORD') ?: ''; // Gmail App Password (not regular password)
$smtp_from_email = getenv('GMAIL_FROM_EMAIL') ?: $smtp_username;
$smtp_from_name = 'Arinsol.ai Website';
$smtp_to_email = getenv('GMAIL_TO_EMAIL') ?: $smtp_username; // Where to receive emails
$smtp_to_name = 'Arinsol.ai Team';

// Check if email config file exists
$configFile = __DIR__ . '/../config/email_config.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

// Validate SMTP credentials
if (empty($smtp_username) || empty($smtp_password)) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Email configuration not set. Please configure Gmail SMTP settings in config/email_config.php'
    ]);
    exit;
}

// Try to use PHPMailer if available
$phpmailerPath = __DIR__ . '/../vendor/autoload.php';
$usePHPMailer = false;

if (file_exists($phpmailerPath)) {
    require_once $phpmailerPath;
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $usePHPMailer = true;
    }
}

if ($usePHPMailer) {
    // Use PHPMailer for reliable email delivery
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp_port;
        $mail->CharSet = 'UTF-8';
        
        // Recipients
        $mail->setFrom($smtp_from_email, $smtp_from_name);
        $mail->addAddress($smtp_to_email, $smtp_to_name);
        $mail->addReplyTo($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Lead from Arinsol.ai: ' . ($product ?: 'General Inquiry');
        
        $emailBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 8px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; background: #fff; }
                .section-title { font-size: 16px; font-weight: bold; color: #667eea; margin-top: 20px; border-bottom: 2px solid #eee; padding-bottom: 5px; }
                .field { margin-bottom: 12px; }
                .label { font-weight: bold; font-size: 13px; color: #555; }
                .value { margin-top: 2px; font-size: 14px; }
                .highlight { background: #f0f7ff; padding: 10px; border-left: 3px solid #667eea; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Lead: " . htmlspecialchars($product ?: 'General Inquiry') . "</h2>
                </div>
                <div class='content'>
                    <div class='highlight'>
                        <div class='label'>Interested Product:</div>
                        <div class='value' style='font-size: 16px; font-weight: bold;'>" . htmlspecialchars($product ?: 'Not specified') . "</div>
                    </div>

                    <div class='section-title'>Contact Details</div>
                    <div class='field'>
                        <div class='label'>Name:</div>
                        <div class='value'>" . htmlspecialchars($name) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Email:</div>
                        <div class='value'>" . htmlspecialchars($email) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Company:</div>
                        <div class='value'>" . htmlspecialchars($company ?: 'Not provided') . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Role:</div>
                        <div class='value'>" . htmlspecialchars($role ?: 'Not provided') . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Country / Timezone:</div>
                        <div class='value'>" . htmlspecialchars($country ?: 'Not provided') . "</div>
                    </div>

                    <div class='section-title'>Qualification Details</div>
                    <div class='field'>
                        <div class='label'>Industry:</div>
                        <div class='value'>" . htmlspecialchars($industry ?: 'Not selected') . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Company Size:</div>
                        <div class='value'>" . htmlspecialchars($companySize ?: 'Not selected') . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Biggest Bottleneck:</div>
                        <div class='value'>" . htmlspecialchars($bottleneck ?: 'Not provided') . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Preferred Next Step:</div>
                        <div class='value'>" . htmlspecialchars($nextStep ?: 'Not selected') . "</div>
                    </div>

                    <div class='section-title'>Message</div>
                    <div class='field'>
                        <div class='value'>" . nl2br(htmlspecialchars($message ?: 'No message provided')) . "</div>
                    </div>
                </div>
            </div>
        </body>
        </html>";
        
        $mail->Body = $emailBody;
        $mail->AltBody = "New Lead: " . ($product ?: 'General Inquiry') . "\n\n" .
                         "Name: {$name}\nEmail: {$email}\nCompany: {$company}\nRole: {$role}\nCountry: {$country}\n\n" .
                         "Industry: {$industry}\nSize: {$companySize}\nBottleneck: {$bottleneck}\nNext Step: {$nextStep}\n\n" .
                         "Message:\n" . ($message ?: 'No message');
        
        $mail->send();
        
        echo json_encode([
            'success' => true,
            'message' => 'Thank you! Your message has been sent successfully. We will get back to you soon.'
        ]);
        
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to send email. Please try again later.'
        ]);
        error_log('Email error: ' . $mail->ErrorInfo);
    }
} else {
    // Fallback: Use basic mail() function with SMTP-like headers
    // Note: This is less reliable and may not work on all servers
    $to = $smtp_to_email;
    $subject = 'New Lead from Arinsol.ai: ' . ($product ?: 'General Inquiry');
    $emailBody = "New Lead: " . ($product ?: 'General Inquiry') . "\n\n";
    $emailBody .= "Name: {$name}\n";
    $emailBody .= "Email: {$email}\n";
    $emailBody .= "Company: {$company}\n";
    $emailBody .= "Role: {$role}\n";
    $emailBody .= "Country: {$country}\n\n";
    
    $emailBody .= "Industry: {$industry}\n";
    $emailBody .= "Size: {$companySize}\n";
    $emailBody .= "Bottleneck: {$bottleneck}\n";
    $emailBody .= "Next Step: {$nextStep}\n\n";
    
    $emailBody .= "Message:\n" . ($message ?: 'No message');
    
    $headers = "From: {$smtp_from_name} <{$smtp_from_email}>\r\n";
    $headers .= "Reply-To: {$name} <{$email}>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    if (mail($to, $subject, $emailBody, $headers)) {
        echo json_encode([
            'success' => true,
            'message' => 'Thank you! Your message has been sent successfully. We will get back to you soon.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to send email. Please install PHPMailer for reliable Gmail SMTP support. See EMAIL_SETUP.md for instructions.'
        ]);
        error_log('Email send failed using mail() function');
    }
}
