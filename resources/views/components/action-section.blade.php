<div {{ $attributes->merge(['class' => 'account-section']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="md:col-span-2">
        <div class="panel account-panel">
            {{ $content }}
        </div>
    </div>
</div>
