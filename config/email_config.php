<?php
/**
 * Gmail SMTP Configuration
 * 
 * IMPORTANT: 
 * 1. Use Gmail App Password, not your regular Gmail password
 * 2. To generate App Password: Google Account > Security > 2-Step Verification > App passwords
 * 3. Keep this file secure and never commit it to version control
 */

// Gmail SMTP Settings
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_username = 'arinsol.cy@gmail.com'; // Your Gmail address
$smtp_password = 'xcfp eidd rjre mioa'; // Gmail App Password (16 characters)
$smtp_from_email = 'arinsol.cy@gmail.com'; // Usually same as username
$smtp_from_name = 'Arinsol.ai Website';
$smtp_to_email = 'info@arinsol.ai'; // Where to receive contact form emails
$smtp_to_name = 'Arinsol.ai Team';

// Set environment variables (alternative to hardcoding)
if (!getenv('GMAIL_USERNAME')) {
    putenv("GMAIL_USERNAME={$smtp_username}");
}
if (!getenv('GMAIL_APP_PASSWORD')) {
    putenv("GMAIL_APP_PASSWORD={$smtp_password}");
}
if (!getenv('GMAIL_FROM_EMAIL')) {
    putenv("GMAIL_FROM_EMAIL={$smtp_from_email}");
}
if (!getenv('GMAIL_TO_EMAIL')) {
    putenv("GMAIL_TO_EMAIL={$smtp_to_email}");
}

