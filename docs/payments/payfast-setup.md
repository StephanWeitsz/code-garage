# PayFast Setup Guide

This is the operational guide for creating and connecting a PayFast merchant account to Code Garage.

## 1) Create and verify your merchant account

1. Sign up on PayFast merchant onboarding.
2. Complete FICA/business verification in your dashboard.
3. Ensure settlement bank account details are verified.

## 2) Enable developer credentials

1. Open PayFast Dashboard.
2. Go to **Settings -> Developer Settings**.
3. Copy:
- `Merchant ID`
- `Merchant Key`
4. Set a `Passphrase` (recommended) and store it securely.

## 3) Configure URLs in your app

Set these environment values:

```env
PAYFAST_MERCHANT_ID=...
PAYFAST_MERCHANT_KEY=...
PAYFAST_PASSPHRASE=...
PAYFAST_SANDBOX=true
PAYFAST_SANDBOX_URL="https://sandbox.payfast.co.za/eng/process"
PAYFAST_LIVE_URL="https://www.payfast.co.za/eng/process"
PAYFAST_SANDBOX_VALIDATE_URL="https://sandbox.payfast.co.za/eng/query/validate"
PAYFAST_LIVE_VALIDATE_URL="https://www.payfast.co.za/eng/query/validate"
```

## 4) Configure callback routes in PayFast

Use these routes from your deployed domain:

- Return URL: `/payments/payfast/return/{payment_reference}`
- Cancel URL: `/payments/payfast/cancel/{payment_reference}`
- Notify URL (ITN): `/payments/payfast/notify`

Note: `notify_url` must be public, reachable, and must return HTTP `200` directly (no redirect).

## 5) Whitelisting and network checks

PayFast publishes DNS records for API/site/IP endpoints. Ensure hosting firewalls allow required inbound/outbound traffic.

Reference (official support article):
- [What IP addresses does PayFast use?](https://support.payfast.help/portal/en/kb/articles/what-ip-addresses-does-payfast-use-20-9-2022)

## 6) Test in sandbox

1. Keep `PAYFAST_SANDBOX=true`.
2. Create a paid course checkout in Code Garage.
3. Complete a sandbox payment.
4. Verify ITN marks payment as `paid` and enrollment unlocks.

## 7) Go live

1. Set `PAYFAST_SANDBOX=false`.
2. Confirm live merchant credentials.
3. Re-test with a low-value real transaction.
4. Confirm payout and reconciliation in PayFast dashboard.

## 8) Enable SnapScan and Zapper through PayFast

1. In PayFast dashboard, open payment method settings.
2. Enable `SnapScan` and `Zapper` for your merchant profile.
3. Re-test checkout and confirm those methods appear in the hosted PayFast payment page.

Reference:
- [Navigate your Payfast Dashboard](https://support.payfast.help/portal/en/kb/articles/navigate-your-payfast-dashboard-20-9-2022)

## Troubleshooting

- If ITN is not received, verify the notify URL is reachable and does not 302.
- Verify signature/passphrase values match exactly.
- Verify app server can post back to PayFast validate endpoint.

Reference (official support article):
- [Why am I not receiving the ITN callback?](https://support.payfast.help/portal/en/kb/articles/why-am-i-not-receiving-the-itn-callback-20-9-2022)
