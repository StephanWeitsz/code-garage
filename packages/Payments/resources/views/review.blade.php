@extends('layouts.app', ['title' => 'Payment Review'])

@section('content')
    <section class="stack">
        <div class="hero-actions">
            <a href="{{ route('payments.index') }}" class="button button-secondary">Back to payments</a>
        </div>

        <article class="panel">
            <p class="eyebrow">Operations</p>
            <h1>Payment review queue</h1>
            <p class="muted">Verify EFT proofs, handle gateway exceptions, and capture manual payments.</p>
        </article>

        <section class="panel">
            <h2>Record manual cash payment</h2>
            <p class="muted">Use when a student paid directly to lecturer/admin in person.</p>

            <form method="POST" action="{{ $courses->isNotEmpty() ? route('payments.collect', $courses->first()->id) : '#' }}" id="manual-payment-form" class="stack">
                @csrf
                <label>
                    <span class="auth-label">Course</span>
                    <select id="manual-course-select" name="course_id" class="auth-input" required>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" data-action="{{ route('payments.collect', $course->id) }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="auth-label">Student</span>
                    <select name="student_user_id" class="auth-input" required>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="auth-label">Notes (optional)</span>
                    <textarea name="notes" class="auth-input" rows="3" placeholder="Cash received at office, receipt #..."></textarea>
                </label>

                <button type="submit" class="button button-primary" @disabled($courses->isEmpty())>Record manual payment</button>
            </form>

            <script>
                const manualCourseSelect = document.getElementById('manual-course-select');
                const manualPaymentForm = document.getElementById('manual-payment-form');

                if (manualCourseSelect && manualPaymentForm) {
                    manualCourseSelect.addEventListener('change', (event) => {
                        const selected = event.target.selectedOptions?.[0];
                        const action = selected?.getAttribute('data-action');

                        if (action) {
                            manualPaymentForm.setAttribute('action', action);
                        }
                    });
                }
            </script>
        </section>

        <section class="panel">
            <h2>Review records</h2>
            @if ($payments->isEmpty())
                <p class="muted">No payment records to review.</p>
            @else
                <div class="stack">
                    @foreach ($payments as $payment)
                        <article class="panel" style="margin: 0;">
                            <div><strong>{{ $payment->course?->title ?? 'Course' }}</strong></div>
                            <div class="muted">Student: {{ $payment->user?->name ?? 'Unknown user' }}</div>
                            <div class="muted">Reference: {{ $payment->reference }}</div>
                            <div class="muted">Method: {{ strtoupper(str_replace('_', ' ', $payment->channel)) }}</div>
                            <div class="muted">Status: {{ strtoupper($payment->status) }}</div>
                            <div class="muted">Amount: {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</div>
                            @if ($payment->transfer_reference)
                                <div class="muted">Transfer ref: {{ $payment->transfer_reference }}</div>
                            @endif
                            @if ($payment->notes)
                                <div class="muted">Notes: {{ $payment->notes }}</div>
                            @endif

                            <div class="stack" style="margin-top: .5rem;">
                                @if (filled(data_get($payment->metadata, 'proof_file_path')))
                                    <a href="{{ route('payments.proof', $payment->id) }}" class="button button-secondary">Download proof</a>
                                @endif

                                @if ($payment->status !== 'paid')
                                    <form method="POST" action="{{ route('payments.send-reminder', $payment->id) }}">
                                        @csrf
                                        <button type="submit" class="button button-secondary">Send outstanding reminder</button>
                                    </form>
                                    <form method="POST" action="{{ route('payments.mark-paid', $payment->id) }}">
                                        @csrf
                                        <button type="submit" class="button button-secondary">Mark paid</button>
                                    </form>
                                @endif

                                @if (! in_array($payment->status, ['paid', 'rejected'], true))
                                    <form method="POST" action="{{ route('payments.reject', $payment->id) }}" class="stack">
                                        @csrf
                                        <input type="text" name="review_note" class="auth-input" placeholder="Reason for rejection (optional)">
                                        <button type="submit" class="button button-secondary">Reject payment</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </section>
@endsection
