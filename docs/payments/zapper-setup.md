# Zapper Setup Guide

This is the setup checklist for Zapper enablement through PayFast (recommended) and direct integration fallback.

## 1) Open a Zapper merchant account

1. Register business details.
2. Complete KYC/compliance onboarding.
3. Enable online payment capability.

## 2) Collect integration credentials

Document and securely store:

- Merchant identifier
- API credentials
- Test/live API URLs
- Webhook verification/signing requirements

## 3) Platform configuration (planned keys)

Add app env/config keys once credentials are available.

## 4) Webhook and callback setup

Configure callbacks for:

- Successful payment
- Failed/cancelled payment
- Post-settlement status updates

## 5) Test and go live

1. Run test-mode checkout flow.
2. Validate payment status updates + enrollment unlock.
3. Switch to production credentials and retest.

## Status in Code Garage

- Current: use PayFast hosted checkout and enable Zapper in PayFast payment methods.
- Optional later: direct Zapper API integration only if you need separate settlement/reporting from PayFast.
