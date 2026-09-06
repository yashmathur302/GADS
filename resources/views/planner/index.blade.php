<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Keyword Planner') }}</h1>
    </x-slot>

    <div class="space-y-6">
        <x-admin.sample-data-notice />

        <x-admin.card :title="__('Get forecasts')">
            <form method="POST" action="{{ route('planner.forecast') }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="keywords" :value="__('Keywords (one per line, up to 20)')" />
                    <textarea
                        id="keywords"
                        name="keywords"
                        rows="5"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono"
                        placeholder="emergency plumber&#10;drain cleaning service&#10;24 hour plumber"
                    >{{ old('keywords', $keywordsInput) }}</textarea>
                    <x-input-error :messages="$errors->get('keywords')" class="mt-2" />
                </div>

                <div class="max-w-xs">
                    <x-input-label for="max_cpc_bid" :value="__('Max CPC bid (USD)')" />
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">$</span>
                        <x-text-input
                            id="max_cpc_bid"
                            class="block w-full pl-6"
                            type="number"
                            step="0.01"
                            min="0.01"
                            max="1000"
                            name="max_cpc_bid"
                            :value="old('max_cpc_bid', $maxCpcBid)"
                            required
                        />
                    </div>
                    <x-input-error :messages="$errors->get('max_cpc_bid')" class="mt-2" />
                </div>

                <x-primary-button>{{ __('Get forecast') }}</x-primary-button>
            </form>
        </x-admin.card>

        @if ($results !== null)
            @php
                $totalImpressions = $results->sum('impressions');
                $totalClicks = $results->sum('clicks');
                $totalCost = $results->sum('cost');
                $blendedCpc = $totalClicks > 0 ? $totalCost / $totalClicks : 0;
            @endphp

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-admin.card>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Total clicks') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($totalClicks) }}</p>
                </x-admin.card>
                <x-admin.card>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Total impressions') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($totalImpressions) }}</p>
                </x-admin.card>
                <x-admin.card>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Total cost') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($totalCost, 2) }}</p>
                </x-admin.card>
                <x-admin.card>
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Avg. CPC (blended)') }}</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">${{ number_format($blendedCpc, 2) }}</p>
                </x-admin.card>
            </div>

            <x-admin.card :title="__('By keyword')">
                @if ($results->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('Enter at least one keyword above to see a forecast.') }}</p>
                @else
                    <div class="overflow-x-auto -mx-4 sm:-mx-6">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    <th class="px-4 sm:px-6 py-2">{{ __('Keyword') }}</th>
                                    <th class="px-4 py-2">{{ __('Impressions') }}</th>
                                    <th class="px-4 py-2">{{ __('Clicks') }}</th>
                                    <th class="px-4 py-2">{{ __('Avg. CPC') }}</th>
                                    <th class="px-4 sm:px-6 py-2">{{ __('Cost') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($results as $forecast)
                                    <tr>
                                        <td class="px-4 sm:px-6 py-2.5 font-medium text-gray-900">{{ $forecast->keyword }}</td>
                                        <td class="px-4 py-2.5 text-gray-600">{{ number_format($forecast->impressions) }}</td>
                                        <td class="px-4 py-2.5 text-gray-600">{{ number_format($forecast->clicks) }}</td>
                                        <td class="px-4 py-2.5 text-gray-600">${{ number_format($forecast->avgCpc, 2) }}</td>
                                        <td class="px-4 sm:px-6 py-2.5 text-gray-600">${{ number_format($forecast->cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.card>
        @endif
    </div>
</x-app-layout>
