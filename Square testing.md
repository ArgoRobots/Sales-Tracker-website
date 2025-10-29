# Testing Square Payments

## Setup

Set environment to sandbox in `.env`:

```
APP_ENV=sandbox
```

## Test the Payment

1. Go to checkout page with Square selected:

```
https://dev.argorobots.com/upgrade/checkout/index.php?method=square
```

2. Fill in the form:

   - **Cardholder Name**: Any name
   - **Card Number**: `4111 1111 1111 1111` (Visa)
   - **Expiry**: Any future date (e.g., 12/28)
   - **CVV**: Any 3 digits (e.g., 111)
   - **Postal Code**: Any valid postal code
   - **Email**: Your email address

3. Click "Pay $20.00 CAD"

4. Check results:
   - Should redirect to thank you page with license key
   - Check email for license key

## Other Test Cards

**Success:**

- Visa: `4111 1111 1111 1111`
- Mastercard: `5105 1051 0510 5100`
- Amex: `3782 822463 10005`

**Declined:**

- `4000 0000 0000 0002` (generic decline)

**More test cards:** [Square Testing Guide](https://developer.squareup.com/docs/devtools/sandbox/payments)

## Verify in Square Dashboard

1. Go to [Square Developer Dashboard](https://developer.squareup.com/apps)
2. Make sure you're in **Sandbox** mode
3. Click on your application
4. Check the test payments

## Switch to Production

When ready to go live:

1. Change `.env`: `APP_ENV=production`
2. Test with a real card (you'll be charged)
