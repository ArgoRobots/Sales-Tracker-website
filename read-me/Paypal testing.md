# Testing PayPal Payments

## Setup

Set environment to sandbox in `.env`:

```
APP_ENV=sandbox
```

## Test the Payment

1. Go to checkout page with PayPal selected:

```
https://dev.argorobots.com/upgrade/checkout/index.php?method=paypal
```

2. Click the PayPal button

3. Log in with PayPal sandbox test account:

   - Get test accounts from [PayPal Developer Dashboard](https://developer.paypal.com/dashboard/accounts)
   - Create a Personal (Buyer) account if you don't have one
   - Use the test account email and password
   - **If it asks for a verification code:** Click "Use a different method" and choose "Password" instead

4. Complete the payment in the PayPal popup

5. Check results:
   - Should redirect to thank you page with license key
   - Check email for license key

## Verify in PayPal Dashboard

1. Go to [PayPal Sandbox](https://www.sandbox.paypal.com/)
2. Make sure you're in **Sandbox Mode**
3. Check the Payments section for your test payment

## Switch to Production

When ready to go live:

1. Change `.env`: `APP_ENV=production`
2. Test with a real card (you'll be charged)
