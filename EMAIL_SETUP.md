# Email Setup Guide

This guide will help you configure Gmail SMTP for the contact form.

## Prerequisites

1. A Gmail account
2. 2-Step Verification enabled on your Gmail account
3. **PHPMailer library (REQUIRED for Gmail SMTP)** - The PHP `mail()` function cannot authenticate with Gmail

## Step 1: Enable 2-Step Verification

1. Go to your Google Account settings: https://myaccount.google.com/
2. Navigate to **Security** > **2-Step Verification**
3. Follow the prompts to enable 2-Step Verification

## Step 2: Generate App Password

1. Go to: https://myaccount.google.com/apppasswords
2. Select **Mail** as the app
3. Select **Other (Custom name)** as the device
4. Enter "Arinsol Website" as the name
5. Click **Generate**
6. Copy the 16-character password (you'll need this in the next step)

## Step 3: Configure Email Settings

1. Open `config/email_config.php`
2. Update the following values:

```php
$smtp_username = 'your-email@gmail.com'; // Your Gmail address
$smtp_password = 'xxxx xxxx xxxx xxxx'; // The 16-character App Password (with or without spaces)
$smtp_from_email = 'your-email@gmail.com'; // Usually same as username
$smtp_to_email = 'your-email@gmail.com'; // Where to receive contact form emails
```

## Step 4: Install PHPMailer (REQUIRED)

**IMPORTANT:** PHPMailer is required for Gmail SMTP. The PHP `mail()` function cannot authenticate with Gmail's SMTP server.

### Using Composer (Recommended):
```bash
composer install
```

Or if composer.json doesn't exist:
```bash
composer require phpmailer/phpmailer
```

### Manual Installation:
1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer/releases
2. Extract to `vendor/PHPMailer/PHPMailer/`
3. Ensure the autoloader is set up correctly
4. The email handler will automatically use PHPMailer if available

## Step 5: Test Email Configuration

### Option A: Use Test Script (Recommended)
1. Access `email/test_email.php` in your browser
2. Review the diagnostic output
3. **Delete the test file after testing for security!**

### Option B: Test via Contact Form
1. Fill out the contact form on your website
2. Answer the security question (math captcha)
3. Submit the form
4. Check your email inbox for the contact form submission
5. Check server error logs if emails don't arrive

## Troubleshooting

### Email not sending?
1. **Check if PHPMailer is installed:**
   - Run the test script: `email/test_email.php`
   - Or check if `vendor/autoload.php` exists
   - If not installed, run `composer install`

2. **Verify credentials:**
   - Gmail App Password is correct (16 characters, spaces optional)
   - 2-Step Verification is enabled on Gmail account
   - `config/email_config.php` has correct credentials

3. **Check server error logs:**
   - Look for "Email error:" messages
   - Check PHP error logs for SMTP connection issues
   - Common errors: "Authentication failed" = wrong App Password

4. **Common Issues:**
   - "PHPMailer not found" → Install with `composer install`
   - "Authentication failed" → Regenerate Gmail App Password
   - "Connection refused" → Server firewall blocking SMTP port 587

### Using environment variables instead:
You can also set environment variables instead of editing the config file:
- `GMAIL_USERNAME`
- `GMAIL_APP_PASSWORD`
- `GMAIL_FROM_EMAIL`
- `GMAIL_TO_EMAIL`

## Security Notes

- **Never commit** `config/email_config.php` to version control
- Add it to `.gitignore`
- Keep your App Password secure
- The math captcha helps prevent spam submissions

## Important Notes

**PHPMailer is REQUIRED for Gmail SMTP.** The PHP `mail()` function cannot authenticate with Gmail's SMTP server and will not work. If PHPMailer is not installed, the form will show an error message asking you to install it.

The system will:
- ✅ Use PHPMailer if available (recommended and required for Gmail)
- ❌ Show an error if PHPMailer is not installed (emails will not send)

