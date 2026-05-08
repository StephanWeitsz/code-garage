@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-5">
        <div class="text-lg font-semibold text-slate-50">
            {{ $title }}
        </div>

        <div class="mt-4 text-sm text-slate-300">
            {{ $content }}
        </div>
    </div>

    <div class="flex flex-row justify-end gap-3 border-t px-6 py-4 text-end" style="border-color: rgba(148, 163, 184, 0.16); background: rgba(15, 23, 42, 0.55);">
        {{ $footer }}
    </div>
</x-modal>
