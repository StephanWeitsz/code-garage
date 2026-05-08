@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-5">
        <div class="sm:flex sm:items-start">
            <div class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full border sm:mx-0 sm:h-10 sm:w-10" style="border-color: rgba(248, 113, 113, 0.28); background: rgba(127, 29, 29, 0.28);">
                <svg class="h-6 w-6 text-red-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>

            <div class="mt-3 text-center sm:mt-0 sm:ms-4 sm:text-start">
                <h3 class="text-lg font-semibold text-slate-50">
                    {{ $title }}
                </h3>

                <div class="mt-4 text-sm text-slate-300">
                    {{ $content }}
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-row justify-end gap-3 border-t px-6 py-4 text-end" style="border-color: rgba(148, 163, 184, 0.16); background: rgba(15, 23, 42, 0.55);">
        {{ $footer }}
    </div>
</x-modal>
