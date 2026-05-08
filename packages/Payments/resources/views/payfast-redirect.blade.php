@extends('layouts.app', ['title' => 'Redirecting to PayFast'])

@section('content')
    <section class="stack">
        <article class="panel">
            <p class="eyebrow">Secure checkout</p>
            <h1>Redirecting to PayFast</h1>
            <p class="muted">If nothing happens, click continue below.</p>

            <form id="payfast-redirect-form" method="POST" action="{{ $payfastUrl }}">
                @foreach ($fields as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                <button type="submit" class="button button-primary">Continue to PayFast</button>
            </form>
        </article>
    </section>

    <script>
        document.getElementById('payfast-redirect-form')?.submit();
    </script>
@endsection
