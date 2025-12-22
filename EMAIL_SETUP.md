# Email Setup Guide

This guide will help you configure Gmail SMTP for the contact form.

## Prerequisites

1. A Gmail account
2. 2-Step Verification enabled on your Gmail account
3. PHPMailer library (optional, but recommended)

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

## Step 4: Install PHPMailer (Optional but Recommended)

### Using Composer:
```bash
composer require phpmailer/phpmailer
```

### Manual Installation:
1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer
2. Extract to `vendor/PHPMailer/PHPMailer/`
3. The email handler will automatically use PHPMailer if available

## Step 5: Test the Form

1. Fill out the contact form on your website
2. Answer the security question (math captcha)
3. Submit the form
4. Check your email inbox for the contact form submission

## Troubleshooting

### Email not sending?
- Verify your Gmail App Password is correct (16 characters, no spaces needed)
- Check that 2-Step Verification is enabled
- Ensure `config/email_config.php` has correct credentials
- Check server error logs for detailed error messages

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

## Fallback

If PHPMailer is not available, the system will use PHP's built-in `mail()` function, which is less reliable but will still work on most servers.

