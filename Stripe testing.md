# Testing Stripe Payments

## Setup

Set environment to sandbox in `.env`:

```
APP_ENV=sandbox
```

## Test the Payment

1. Go to checkout page with Stripe selected:

```
https://dev.argorobots.com/upgrade/checkout/index.php?method=stripe
```

2. Fill in the form:

   - **Cardholder Name**: Any name
   - **Card Number**: `4242 4242 4242 4242`
   - **Expiry**: Any future date (e.g., 12/28)
   - **CVC**: Any 3 digits (e.g., 123)
   - **Email**: Your email address

3. Click "Pay $20.00 CAD"

4. Check results:
   - Should redirect to thank you page with license key
   - Check email for license key

## Other Test Cards

- **Success**: `4242 4242 4242 4242`
- **Requires 3D Secure**: `4000 0025 0000 3155`
- **Declined**: `4000 0000 0000 9995`

## Verify in Stripe Dashboard

1. Go to [Stripe Dashboard](https://dashboard.stripe.com/test/payments)
2. Make sure you're in **Test Mode** (toggle in top right)
3. Check the Payments section for your test payment

## Switch to Production

When ready to go live:

1. Change `.env`: `APP_ENV=production`
2. Test with a real card (you'll be charged)
