<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Invoice</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2>Payment Invoice</h2>
    <p>Hello {{ $payment->user?->name ?? 'Student' }},</p>
    <p>Your payment has been recorded as paid.</p>

    <p><strong>Course:</strong> {{ $payment->course?->title ?? 'Course' }}</p>
    <p><strong>Reference:</strong> {{ $payment->reference }}</p>
    <p><strong>Method:</strong> {{ strtoupper(str_replace('_', ' ', $payment->channel)) }}</p>
    <p><strong>Amount:</strong> {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</p>
    <p><strong>Paid at:</strong> {{ optional($payment->paid_at)->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i') }}</p>

    <p>You can continue your course from your student dashboard.</p>
</body>
</html>
