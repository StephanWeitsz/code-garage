<div class="mx-auto flex min-h-screen w-full max-w-2xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="w-full space-y-6">
        <div class="text-center">
            {{ $logo }}
        </div>

        <div class="auth-panel auth-panel-form w-full max-w-none">
            {{ $slot }}
        </div>
    </div>
</div>
