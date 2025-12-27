# Email Setup Guide (Simple Method)

This website uses the standard PHP `mail()` function, which works out-of-the-box on most hosting providers (including SiteGround, Bluehost, GoDaddy).

## Configuration

The only configuration needed is the **destination email address** where you want to receive leads.

1. Open `config/email_config.php`
2. Update the `$smtp_to_email` variable:

```php
$smtp_to_email = 'your-email@example.com'; // Where to receive contact form emails
```

(You can ignore the Gmail SMTP settings in that file; they are not used by this simple method).

## How it works

- The system sends emails using your website's domain (e.g., `noreply@arinsol.ai`) to ensure delivery.
- The "Reply-To" header is set to the visitor's email address, so you can simply hit "Reply" in your email client to answer them.

## Troubleshooting

If emails are not arriving:

1. **Check Spam/Junk Folder:** This is the most common reason.
2. **Whitelist the sender:** Add `noreply@arinsol.ai` (or your domain) to your contacts.
3. **Check Server Logs:** In your hosting control panel, check the Error Logs.

## Note for Gmail Users

If you are not receiving emails to your Gmail address:
- Gmail has strict spam filters.
- Ensure the email is not in the "Promotions" tab.
- If issues persist, consider using a domain-based email (like `info@arinsol.ai`) to receive the leads.
