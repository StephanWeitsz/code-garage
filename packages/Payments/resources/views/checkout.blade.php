@extends('layouts.app', ['title' => 'Checkout - '.$course->title])

@section('content')
    <section class="stack">
        <div class="hero-actions">
            <a href="{{ route('courses.show', $course->slug) }}" class="button button-secondary">Back to course</a>
            <a href="{{ route('payments.index') }}" class="button button-secondary">My payments</a>
            @if (auth()->user()?->can('payments.view'))
                <a href="{{ route('payments.review') }}" class="button button-secondary">Review payments</a>
            @endif
        </div>

        <article class="panel">
            <p class="eyebrow">Checkout</p>
            <h1>{{ $course->title }}</h1>
            <p class="muted">Amount due: <strong>{{ $course->pricing_currency }} {{ number_format((float) $course->pricing_amount, 2) }}</strong></p>

            @if ($hasPaidAccess)
                <div class="panel" style="margin-top: 1rem; border: 1px solid rgba(30, 41, 59, .2);">
                    <h2>Payment verified</h2>
                    <p class="muted">Your payment is already marked as paid. You can enroll now.</p>
                    <form method="POST" action="{{ route('enrollments.store') }}">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                        <button type="submit" class="button button-primary">Complete enrollment</button>
                    </form>
                </div>
            @endif
        </article>

        <section class="grid grid-cols-1 items-start gap-6 md:grid-cols-2 md:gap-8">
            <article class="panel">
                <h2>Payment portal</h2>
                <p class="muted">Use PayFast for online checkout. Enable SnapScan and Zapper inside your PayFast merchant dashboard to make them available during checkout.</p>

                <form method="POST" action="{{ route('payments.portal', $course->id) }}" class="stack">
                    @csrf
                    <input type="hidden" name="channel" value="payfast">
                    <button type="submit" class="button button-primary">Continue to PayFast</button>
                </form>
            </article>

            <article class="panel">
                <h2>Bank transfer (EFT)</h2>
                <p class="muted">Pay into the account below, then submit your transfer details for verification.</p>

                <div class="stack" style="margin-bottom: 1rem;">
                    <div><strong>Account name:</strong> {{ config('payments.bank_transfer.account_name') }}</div>
                    <div><strong>Bank:</strong> {{ config('payments.bank_transfer.bank_name') }}</div>
                    <div><strong>Account number:</strong> {{ config('payments.bank_transfer.account_number') }}</div>
                    <div><strong>Branch code:</strong> {{ config('payments.bank_transfer.branch_code') }}</div>
                    <div><strong>Reference hint:</strong> {{ config('payments.bank_transfer.reference_hint') }}</div>
                </div>

                <form method="POST" action="{{ route('payments.bank-transfer', $course->id) }}" class="stack" enctype="multipart/form-data">
                    @csrf
                    <label>
                        <span class="auth-label">Proof of payment file (optional PDF/JPG/PNG)</span>
                        <input type="file" name="proof_file" class="auth-input" accept=".pdf,.jpg,.jpeg,.png">
                    </label>
                    <label>
                        <span class="auth-label">Payer name</span>
                        <input type="text" name="payer_name" class="auth-input" required>
                    </label>
                    <label>
                        <span class="auth-label">Transfer reference</span>
                        <input type="text" name="transfer_reference" class="auth-input" required>
                    </label>
                    <label>
                        <span class="auth-label">Paid date/time (optional)</span>
                        <input type="datetime-local" name="paid_at" class="auth-input">
                    </label>
                    <label>
                        <span class="auth-label">Notes (optional)</span>
                        <textarea name="notes" rows="3" class="auth-input"></textarea>
                    </label>
                    <button type="submit" class="button button-primary">Submit transfer proof</button>
                </form>
            </article>
        </section>

        <section class="panel">
            <h2>Payment history for this course</h2>
            @if ($payments->isEmpty())
                <p class="muted">No payment records yet.</p>
            @else
                <div class="stack">
                    @foreach ($payments as $payment)
                        <article class="panel" style="margin: 0;">
                            <div><strong>{{ strtoupper(str_replace('_', ' ', $payment->channel)) }}</strong></div>
                            <div class="muted">Reference: {{ $payment->reference }}</div>
                            <div class="muted">Status: {{ strtoupper($payment->status) }}</div>
                            <div class="muted">Amount: {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</div>
                            @if ($payment->transfer_reference)
                                <div class="muted">Transfer ref: {{ $payment->transfer_reference }}</div>
                            @endif
                            @if (filled(data_get($payment->metadata, 'proof_file_path')))
                                <div>
                                    <a href="{{ route('payments.proof', $payment->id) }}" class="button button-secondary">Download proof</a>
                                </div>
                            @endif
                            @if ($payment->status !== 'paid')
                                <form method="POST" action="{{ route('payments.send-reminder', $payment->id) }}">
                                    @csrf
                                    <button type="submit" class="button button-secondary">Email outstanding reminder</button>
                                </form>
                            @endif
                            @if (auth()->user()?->can('payments.mark-paid') && $payment->status !== 'paid')
                                <div class="stack" style="margin-top: .5rem;">
                                    <form method="POST" action="{{ route('payments.mark-paid', $payment->id) }}">
                                        @csrf
                                        <button type="submit" class="button button-secondary">Mark paid</button>
                                    </form>
                                    <form method="POST" action="{{ route('payments.reject', $payment->id) }}" class="stack">
                                        @csrf
                                        <input type="text" name="review_note" class="auth-input" placeholder="Reason for rejection (optional)">
                                        <button type="submit" class="button button-secondary">Reject</button>
                                    </form>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </section>
@endsection
