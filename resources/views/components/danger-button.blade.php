<button {{ $attributes->merge(['type' => 'button', 'class' => 'button account-button-danger']) }}>
    {{ $slot }}
</button>
