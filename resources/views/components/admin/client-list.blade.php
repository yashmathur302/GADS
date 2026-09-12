@props(['clients', 'countKey', 'countLabel', 'showRoute', 'context'])

<div x-data="{ showForm: false }">
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">{{ __('Clients with a keyword list under this section.') }}</p>
        <button
            type="button"
            @click="showForm = !showForm"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-600 focus:ring-offset-2 transition"
        >
            {{ __('+ Add Client') }}
        </button>
    </div>

    <div x-show="showForm" x-transition style="display: none;" class="mb-6 border border-gray-200 rounded-md p-4 bg-gray-50">
        <form method="POST" action="{{ route('clients.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="context" value="{{ $context }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" :value="__('Client name')" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="industry_category" :value="__('Industry category')" />
                    <x-text-input id="industry_category" class="block mt-1 w-full" type="text" name="industry_category" :value="old('industry_category')" placeholder="e.g. Healthcare, Legal, Home Services" required />
                    <x-input-error :messages="$errors->get('industry_category')" class="mt-2" />
                </div>
            </div>

            <x-primary-button>{{ __('Add Client') }}</x-primary-button>
        </form>
    </div>

    @if ($clients->isEmpty())
        <p class="text-sm text-gray-500">{{ __('No clients yet. Add one above to get started.') }}</p>
    @else
        <div class="overflow-x-auto -mx-4 sm:-mx-6">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-4 sm:px-6 py-2">{{ __('Client name') }}</th>
                        <th class="px-4 py-2">{{ __('Category') }}</th>
                        <th class="px-4 py-2">{{ $countLabel }}</th>
                        <th class="px-4 sm:px-6 py-2"><span class="sr-only">{{ __('Actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($clients as $client)
                        <tr>
                            <td class="px-4 sm:px-6 py-2.5 font-medium text-gray-900">{{ $client->name }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $client->industry_category }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ number_format($client->{$countKey}) }}</td>
                            <td class="px-4 sm:px-6 py-2.5 text-right">
                                <a href="{{ route($showRoute, $client) }}" class="text-slate-700 hover:text-slate-900 font-medium">
                                    {{ __('View Details') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
