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

## Database Tables

### `license_keys`

Stores generated license keys and activation status.

**Key columns:**

- `license_key` - Unique license key
- `email` - Customer email
- `transaction_id` - Payment processor transaction ID
- `payment_method` - Stripe/PayPal/Square
- `activated` - 0 or 1
- `activation_date` - When payment completed

### `payment_transactions`

Audit log of all payment attempts.

**Key columns:**

- `transaction_id` - Unique transaction ID
- `email` - Customer email
- `amount` - Payment amount
- `currency` - CAD/USD/etc
- `payment_method` - Processor used
- `status` - Payment status
- `license_key` - Associated license key

---

## Security Best Practices

### API Keys

- **Never** commit .env to git
- **Never** expose secret keys in frontend code
- Rotate keys regularly (every 90 days)
