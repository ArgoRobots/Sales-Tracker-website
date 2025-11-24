# Local Email Setup with MailHog

When running the Argo Books website locally on XAMPP, PHP's `mail()` function won't work without a mail server. MailHog is a fake SMTP server that catches all emails locally and displays them in a web interface - no emails are actually sent.

## Setup Instructions

### 1. Download MailHog

1. Go to https://github.com/mailhog/MailHog/releases
2. Download `MailHog_windows_386.exe` (or `MailHog_windows_amd64.exe`)
3. Save it somewhere convenient

### 2. Configure php.ini

Open `C:\xampp\php\php.ini` and find the `[mail function]` section. Update these settings:

```ini
[mail function]
SMTP=localhost
smtp_port=1025
sendmail_from = noreply@localhost
sendmail_path = "C:\xampp\sendmail\sendmail.exe -t"
```

> **Note:** The `sendmail_path` comment says "For Unix only" but XAMPP includes a Windows sendmail wrapper, so this works.

### 3. Configure sendmail.ini

Open `C:\xampp\sendmail\sendmail.ini` and update these settings:

```ini
smtp_server=localhost
smtp_port=1025
smtp_ssl=none
force_sender=noreply@localhost
error_logfile=error.log
```

> **Important:** `smtp_ssl` must be `none` because MailHog doesn't support TLS/SSL.

### 4. Run MailHog

Double-click `MailHog_windows_386.exe`. A console window will open. Keep it running (you can minimize it).

### 5. Restart Apache

In XAMPP Control Panel, stop and start Apache.

### 6. View Emails

Open http://localhost:8025 in your browser. All captured emails will appear here.

## How It Works

```
PHP mail() → sendmail.exe → MailHog (port 1025) → Web UI (port 8025)
```

## Troubleshooting

### Emails not appearing in MailHog

1. **Check MailHog is running by opening CMD and running:**
   ```
   netstat -an | findstr 1025
   ```
   You should see `0.0.0.0:1025` in LISTENING state.

2. **Check sendmail error log:** `C:\xampp\sendmail\error.log`

3. **Check Apache error log:** `C:\xampp\apache\logs\error.log`

4. **Test PHP mail directly:** Create `test_mail.php` in htdocs:
   ```php
   <?php
   $result = mail("test@example.com", "Test", "Test body");
   var_dump($result);
   ```
   Should output `bool(true)`.