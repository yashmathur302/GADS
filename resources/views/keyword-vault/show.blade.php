<x-app-layout>
    <x-slot name="header">
        <x-admin.breadcrumbs :items="[
            ['label' => 'Assets & Creative Library'],
            ['label' => $type->label(), 'url' => route($baseRoute)],
            ['label' => $industry->name, 'url' => route($baseRoute.'.industry', $industry)],
            ['label' => $niche->name],
        ]" />
        <h1 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">{{ $niche->name }}</h1>
    </x-slot>

    <div class="space-y-6">
        <x-admin.card :title="__('Import / Export')">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
                <div>
                    <p class="text-sm text-gray-500 mb-2">{{ __('Download this list as an Excel file.') }}</p>
                    <a
                        href="{{ route($baseRoute.'.export', [$industry, $niche]) }}"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        {{ __('Export to Excel') }}
                    </a>
                </div>

                <div class="sm:text-right">
                    <p class="text-sm text-gray-500 mb-2">{{ __('Upload a .xlsx, .xls, or .csv file with a "Keyword" column (and optional "Notes").') }}</p>
                    <form method="POST" action="{{ route($baseRoute.'.import', [$industry, $niche]) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-2 sm:justify-end">
                        @csrf
                        <input
                            type="file"
                            name="file"
                            accept=".xlsx,.xls,.csv"
                            required
                            class="block w-full sm:w-auto text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:uppercase file:tracking-widest file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                        >
                        <x-primary-button>{{ __('Import') }}</x-primary-button>
                    </form>
                    <x-input-error :messages="$errors->get('file')" class="mt-2 sm:text-right" />
                </div>
            </div>
        </x-admin.card>

        <x-admin.card :title="__('Add :noun', ['noun' => $type->nounSingular()])">
            <form method="POST" action="{{ route($baseRoute.'.store', [$industry, $niche]) }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="keyword" :value="__('Keyword')" />
                    <x-text-input id="keyword" class="block mt-1 w-full" type="text" name="keyword" :value="old('keyword')" required autofocus />
                    <x-input-error :messages="$errors->get('keyword')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notes" :value="__('Notes (optional)')" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <x-primary-button>{{ __('Add') }}</x-primary-button>
            </form>
        </x-admin.card>

        <x-admin.card>
            @if ($entries->isEmpty())
                <p class="text-sm text-gray-500">{{ __('No :noun yet for this sub-category.', ['noun' => $type->nounPlural()]) }}</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($entries as $entry)
                        <li class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 break-words">{{ $entry->keyword }}</p>
                                @if ($entry->notes)
                                    <p class="mt-0.5 text-sm text-gray-500 break-words">{{ $entry->notes }}</p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route($baseRoute.'.destroy', [$industry, $niche, $entry]) }}" class="shrink-0" onsubmit="return confirm('{{ __('Remove this keyword?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800 rounded focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    {{ __('Remove') }}
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </div>
</x-app-layout>
