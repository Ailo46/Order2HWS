<x-filament-panels::page>

    <form
        wire:submit="importProducts"
        class="space-y-6"
    >
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button
                type="submit"
                icon="heroicon-o-arrow-up-tray"
            >
                Import Products
            </x-filament::button>

            <span
                wire:loading
                wire:target="importProducts"
                class="text-sm text-gray-500"
            >
                Import is running. Please wait...
            </span>
        </div>
    </form>

    @if ($importResult !== null)
        <x-filament::section class="mt-8">
            <x-slot name="heading">
                Latest Import Result
            </x-slot>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-5">

                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <div class="text-sm text-gray-500">
                        Created
                    </div>

                    <div class="mt-1 text-2xl font-semibold">
                        {{ (int) ($importResult['created'] ?? 0) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <div class="text-sm text-gray-500">
                        Updated
                    </div>

                    <div class="mt-1 text-2xl font-semibold">
                        {{ (int) ($importResult['updated'] ?? 0) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <div class="text-sm text-gray-500">
                        Skipped
                    </div>

                    <div class="mt-1 text-2xl font-semibold">
                        {{ (int) ($importResult['skipped'] ?? 0) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <div class="text-sm text-gray-500">
                        Images Imported
                    </div>

                    <div class="mt-1 text-2xl font-semibold">
                        {{ (int) ($importResult['images_imported'] ?? 0) }}
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                    <div class="text-sm text-gray-500">
                        Images Replaced
                    </div>

                    <div class="mt-1 text-2xl font-semibold">
                        {{ (int) ($importResult['images_replaced'] ?? 0) }}
                    </div>
                </div>

            </div>

            @if (! empty($importResult['errors']))
                <div class="mt-6">
                    <h3 class="mb-3 text-sm font-semibold">
                        Row Errors
                    </h3>

                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">
                                        Row
                                    </th>

                                    <th class="px-4 py-3 text-left font-medium">
                                        Product Code
                                    </th>

                                    <th class="px-4 py-3 text-left font-medium">
                                        Error
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($importResult['errors'] as $error)
                                    <tr class="border-t border-gray-200 dark:border-white/10">
                                        <td class="px-4 py-3">
                                            {{ $error['row'] ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $error['code'] ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            {{ $error['message'] ?? 'Unknown error' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if (! empty($importResult['image_warnings']))
                <div class="mt-6">
                    <h3 class="mb-3 text-sm font-semibold">
                        Image Warnings
                    </h3>

                    <div class="rounded-xl border border-warning-300 bg-warning-50 p-4 dark:border-warning-500/30 dark:bg-warning-500/10">
                        <ul class="space-y-2 text-sm">
                            @foreach ($importResult['image_warnings'] as $warning)
                                <li>
                                    {{ $warning }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </x-filament::section>
    @endif

    <x-filament::section
        class="mt-8"
        collapsible
        collapsed
    >
        <x-slot name="heading">
            Export
        </x-slot>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            Export features are reserved for Sprint G.
        </p>
    </x-filament::section>

</x-filament-panels::page>