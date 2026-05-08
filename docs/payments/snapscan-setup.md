# SnapScan Setup Guide

This is the setup checklist for SnapScan enablement through PayFast (recommended) and direct integration fallback.

## 1) Open a SnapScan merchant account

1. Register business profile with SnapScan.
2. Complete compliance and settlement details.
3. Request API/integration access for online checkout.

## 2) Collect integration credentials

Document and securely store:

- Merchant account ID
- API keys/secrets
- Environment endpoints (test/live)
- Webhook signing details

## 3) Platform configuration (planned keys)

Add app env/config keys once API credentials are issued.

## 4) Webhook and callback setup

Register platform callback endpoints for:

- Payment success
- Payment failure/cancel
- Refund/chargeback updates (if supported)

## 5) Test and go live

1. Run end-to-end checkout tests in test mode.
2. Verify payment status writes to `payments` table.
3. Verify enrollment unlock logic.
4. Switch to live credentials.

## Status in Code Garage

- Current: use PayFast hosted checkout and enable SnapScan in PayFast payment methods.
- Optional later: direct SnapScan API integration only if you need separate settlement/reporting from PayFast.
