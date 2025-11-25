# Payment System - Admin Guide

## Payment Processor Fees

**Note:** Fees vary by country, account type, and agreement. These are typical rates for Canadian online transactions. Check your actual merchant agreements for exact rates.

### Stripe

- **Standard Rate:** 2.9% + $0.30 CAD per transaction
- **International Cards:** Additional 1.5%
- **Currency Conversion:** Additional 1%
- **No monthly fees**

### PayPal

- **Standard Rate:** 2.9% + $0.30 CAD per transaction
- **PayPal Account Payments:** Same rate
- **International:** 4.4% + fixed fee
- **No monthly fees**

### Square

- **Online Payments:** 2.9% + $0.30 CAD per transaction
- **Card on File:** Same rate
- **International:** Additional 1.5%
- **No monthly fees**

**For a $20 CAD transaction:**

- Stripe: $20.00 - $0.88 = **$19.12 net**
- PayPal: $20.00 - $0.88 = **$19.12 net**
- Square: $20.00 - $0.88 = **$19.12 net**

---

## Environment Modes

### Sandbox Mode (Testing)

```
APP_ENV=sandbox
```

- Uses test API keys
- No real money processed
- Use test cards/accounts
- Perfect for development and testing

### Production Mode (Live)

```
APP_ENV=production
```

- Uses live API keys
- Real money processed
- Real cards charged
- Only use when ready to go live

**To switch:** Change `APP_ENV` in `.env` file and restart PHP.

---

## Key Rotation

### When to Rotate Keys

- **Regular schedule:** Every 90 days (recommended)
- **After breach:** Immediately if compromised
- **Staff changes:** When developers leave

### How to Rotate

**Stripe:**

1. Dashboard → Developers → API keys
2. Find "Secret key" row
3. Click the **"..."** (three dots) next to the secret key
4. Click **"Roll key"**
5. Copy the new secret key immediately (shown only once)
6. Update `STRIPE_LIVE_SECRET_KEY` in .env

**PayPal:**

1. Dashboard → Apps & Credentials → Your App → Generate new secret
2. Update `PAYPAL_LIVE_CLIENT_SECRET` in .env

**Square:**

1. Dashboard → Your App → Credentials → Replace (access token)
2. Update `SQUARE_LIVE_ACCESS_TOKEN` in .env

**After rotation:** Always ensure the website sever has the latest .env

---

## Admin Scripts

**Contact Evan Di Placido to obtain these scripts.**

### create_admin.php
Creates admin users

- Place in `/admin` directory
- Visit: `www.argorobots.com/admin/create_admin.php`

### reset_admin_password.php
Resets admin passwords

- Place in `/admin` directory
- Visit: `www.argorobots.com/reset_admin.php`

### create_community_admin.php
Creates admin users for the community system

- Place in `/community/users` directory
- Visit: `www.argorobots.com/community/users/create_community_admin.php`

**Important:** Delete all admin creation scripts immediately after use for security.

---

## Subscription Renewal Cron Job

The subscription renewal process checks for AI subscriptions due for renewal and processes payments automatically.

### Automatic Scheduling

To run the renewal process automatically every day at 3:00 PM, add this cron entry:

```bash
0 15 * * * /usr/bin/php /path/to/your/website/cron/subscription_renewal.php
```

This will check for subscriptions due within 24 hours and process their renewals automatically.

### What the Renewal Process Does

1. Finds active subscriptions due for renewal within 24 hours
2. Processes credit-based renewals first (no charge if credit covers it)
3. Charges payment methods (Stripe/Square) for remaining balance
4. Sends email receipts for successful renewals
5. Sends failure notifications for failed payments
6. Suspends subscriptions after 3 consecutive failures
7. Marks non-auto-renew subscriptions as expired

### Manual Execution

**Via CLI:**
```bash
php /path/to/your/website/cron/subscription_renewal.php
```

**Via Web (with secret key):**
```
https://argorobots.com/cron/subscription_renewal.php?key=YOUR_CRON_SECRET
```

**Via Management UI:**
Visit `/cron/` and authenticate with TOTP to access the renewal management dashboard.

### Logs

Logs are stored in: `/cron/logs/subscription_renewal_YYYY-MM-DD.log`

---

## Security Best Practices

### API Keys

- **Never** commit .env to git
- **Never** expose secret keys in frontend code
- Rotate keys regularly (every 90 days)
