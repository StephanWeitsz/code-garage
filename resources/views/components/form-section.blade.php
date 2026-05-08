@props(['submit'])

<div {{ $attributes->merge(['class' => 'account-section']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="md:col-span-2">
        <form wire:submit="{{ $submit }}" class="space-y-4">
            <div class="panel account-panel {{ isset($actions) ? 'account-panel-top' : '' }}">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div class="account-actions">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>
