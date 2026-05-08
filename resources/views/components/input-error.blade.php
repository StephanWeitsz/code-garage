@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'auth-alert auth-alert-error !mb-0 !px-3 !py-2']) }}>{{ $message }}</p>
@enderror
