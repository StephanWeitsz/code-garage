<?php

return [
    'payfast' => [
        'merchant_id' => env('PAYFAST_MERCHANT_ID'),
        'merchant_key' => env('PAYFAST_MERCHANT_KEY'),
        'passphrase' => env('PAYFAST_PASSPHRASE'),
        'sandbox' => env('PAYFAST_SANDBOX', true),
        'sandbox_url' => env('PAYFAST_SANDBOX_URL', 'https://sandbox.payfast.co.za/eng/process'),
        'live_url' => env('PAYFAST_LIVE_URL', 'https://www.payfast.co.za/eng/process'),
        'sandbox_validate_url' => env('PAYFAST_SANDBOX_VALIDATE_URL', 'https://sandbox.payfast.co.za/eng/query/validate'),
        'live_validate_url' => env('PAYFAST_LIVE_VALIDATE_URL', 'https://www.payfast.co.za/eng/query/validate'),
    ],

    'bank_transfer' => [
        'account_name' => env('PAYMENTS_BANK_ACCOUNT_NAME', 'Code Garage (Pty) Ltd'),
        'bank_name' => env('PAYMENTS_BANK_NAME', 'FNB'),
        'account_number' => env('PAYMENTS_BANK_ACCOUNT_NUMBER', '0000000000'),
        'branch_code' => env('PAYMENTS_BANK_BRANCH_CODE', '250655'),
        'reference_hint' => env('PAYMENTS_BANK_REFERENCE_HINT', 'Use student name + course'),
    ],

    'reminders' => [
        'schedule_time' => env('PAYMENTS_REMINDER_SCHEDULE_TIME', '08:00'),
        'min_payment_age_days' => (int) env('PAYMENTS_REMINDER_MIN_AGE_DAYS', 1),
        'min_interval_hours' => (int) env('PAYMENTS_REMINDER_MIN_INTERVAL_HOURS', 24),
        'max_emails' => (int) env('PAYMENTS_REMINDER_MAX_EMAILS', 5),
        'statuses' => ['pending', 'awaiting_verification', 'failed'],
    ],
];
