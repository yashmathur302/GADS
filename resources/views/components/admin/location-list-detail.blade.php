@props(['client', 'locations', 'importRoute', 'exportRoute', 'indexRoute', 'indexLabel'])

<div class="space-y-6">
    <div>
        <a href="{{ route($indexRoute) }}" class="text-sm text-slate-600 hover:text-slate-900">&larr; {{ $indexLabel }}</a>
        <h2 class="mt-1 text-lg font-semibold text-gray-900">{{ $client->name }}</h2>
        <p class="text-sm text-gray-500">{{ $client->industry_category }}</p>
    </div>

    <x-admin.card :title="__('Import / Export')">
        <div class="flex flex-wrap items-end gap-6">
            <form method="POST" action="{{ route($importRoute, $client) }}" enctype="multipart/form-data" class="flex items-end gap-3">
                @csrf
                <div>
                    <x-input-label for="file" :value="__('Import CSV')" />
                    <input
                        id="file"
                        type="file"
                        name="file"
                        accept=".csv,.txt,text/csv"
                        required
                        class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                    >
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                </div>
                <x-primary-button>{{ __('Import') }}</x-primary-button>
            </form>

            @if ($locations->isNotEmpty())
                <a
                    href="{{ route($exportRoute, $client) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                >
                    <x-admin.icon name="download" class="w-3.5 h-3.5" />
                    {{ __('Export to CSV') }}
                </a>
            @endif
        </div>
        <p class="mt-3 text-xs text-gray-500">
            {{ __('One location per row (e.g. "New York, NY"). Duplicate locations already on this list are skipped automatically.') }}
        </p>
    </x-admin.card>

    <x-admin.card :title="__('Locations')">
        @if ($locations->isEmpty())
            <p class="text-sm text-gray-500">{{ __('No locations yet. Import a CSV above to add some.') }}</p>
        @else
            <div class="overflow-x-auto -mx-4 sm:-mx-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <th class="px-4 sm:px-6 py-2">{{ __('Location') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($locations as $location)
                            <tr>
                                <td class="px-4 sm:px-6 py-2.5 font-medium text-gray-900">{{ $location->location }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>
</div>
