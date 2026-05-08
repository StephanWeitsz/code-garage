@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'auth-alert auth-alert-error']) }}>
        <div class="font-semibold">{{ __('Whoops! Something went wrong.') }}</div>

        <ul class="mt-3 list-disc list-inside space-y-1 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
