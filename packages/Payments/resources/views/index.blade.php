@extends('layouts.app', ['title' => 'Payments'])

@section('content')
    <section class="stack">
        <div class="hero-actions">
            <a href="{{ route('courses.index') }}" class="button button-secondary">Browse courses</a>
            @if ($canReview)
                <a href="{{ route('payments.review') }}" class="button button-secondary">Review queue</a>
            @endif
        </div>

        <article class="panel">
            <p class="eyebrow">Payments</p>
            <h1>Payment history</h1>
            <p class="muted">Track paid, pending, and rejected transactions.</p>
        </article>

        <section class="panel">
            @if ($payments->isEmpty())
                <p class="muted">No payment records yet.</p>
            @else
                <div class="stack">
                    @foreach ($payments as $payment)
                        <article class="panel" style="margin: 0;">
                            <div><strong>{{ $payment->course?->title ?? 'Course' }}</strong></div>
                            <div class="muted">Reference: {{ $payment->reference }}</div>
                            <div class="muted">Method: {{ strtoupper(str_replace('_', ' ', $payment->channel)) }}</div>
                            <div class="muted">Status: {{ strtoupper($payment->status) }}</div>
                            <div class="muted">Amount: {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</div>
                            @if ($payment->paid_at)
                                <div class="muted">Paid at: {{ $payment->paid_at->format('Y-m-d H:i') }}</div>
                            @endif
                            @if ($payment->verifier)
                                <div class="muted">Verified by: {{ $payment->verifier->name }}</div>
                            @endif
                            @if (filled(data_get($payment->metadata, 'proof_file_path')))
                                <a href="{{ route('payments.proof', $payment->id) }}" class="button button-secondary">Download proof file</a>
                            @endif
                            @if ($payment->course)
                                <a href="{{ route('payments.checkout', $payment->course_id) }}" class="button button-secondary">Open checkout</a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </section>
@endsection
