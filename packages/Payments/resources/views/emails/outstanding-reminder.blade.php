<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Outstanding Payment Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2>Outstanding Payment Reminder</h2>
    <p>Hello {{ $payment->user?->name ?? 'Student' }},</p>

    <p>This is a reminder that payment is still outstanding for:</p>

    <p><strong>Course:</strong> {{ $payment->course?->title ?? 'Course' }}</p>
    <p><strong>Reference:</strong> {{ $payment->reference }}</p>
    <p><strong>Current status:</strong> {{ strtoupper($payment->status) }}</p>
    <p><strong>Amount due:</strong> {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</p>

    <p>Please open your checkout/payment page to complete payment or upload proof.</p>
</body>
</html>
