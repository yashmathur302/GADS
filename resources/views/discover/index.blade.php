<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Discover New Keywords') }}</h1>
    </x-slot>

    <div class="space-y-6">
        <x-admin.sample-data-notice />

        <x-admin.card>
            <div x-data="{ mode: {{ $websiteUrlInput ? "'website'" : "'keywords'" }} }">
                <div class="flex gap-1 border-b border-gray-200 mb-6" role="tablist">
                    <button
                        type="button"
                        role="tab"
                        @click="mode = 'keywords'"
                        :aria-selected="(mode === 'keywords').toString()"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition"
                        :class="mode === 'keywords' ? 'border-slate-800 text-slate-900' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    >
                        {{ __('Start with keywords') }}
                    </button>
                    <button
                        type="button"
                        role="tab"
                        @click="mode = 'website'"
                        :aria-selected="(mode === 'website').toString()"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition"
                        :class="mode === 'website' ? 'border-slate-800 text-slate-900' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    >
                        {{ __('Start with a website') }}
                    </button>
                </div>

                <form method="POST" action="{{ route('discover.search') }}" id="discover-form" class="space-y-4">
                    @csrf

                    <div x-show="mode === 'keywords'">
                        <x-input-label for="seed_keywords" :value="__('Enter keywords, separated by commas')" />
                        <x-text-input id="seed_keywords" class="block mt-1 w-full" type="text" name="seed_keywords" :value="$seedKeywordsInput" placeholder="e.g. emergency plumber, drain cleaning" />
                        <x-input-error :messages="$errors->get('seed_keywords')" class="mt-2" />
                    </div>

                    <div x-show="mode === 'website'" style="display: none;">
                        <x-input-label for="website_url" :value="__('Enter your website URL')" />
                        <x-text-input id="website_url" class="block mt-1 w-full" type="url" name="website_url" :value="$websiteUrlInput" placeholder="https://example.com/plumbing-services" />
                        <x-input-error :messages="$errors->get('website_url')" class="mt-2" />
                    </div>

                    <x-admin.targeting-controls :context="$context" />

                    <div class="max-w-xs">
                        <x-input-label for="date_range_months" :value="__('Date range')" />
                        <select id="date_range_months" name="date_range_months" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach ([12 => 'Past 12 months', 24 => 'Past 24 months', 36 => 'Past 36 months'] as $value => $label)
                                <option value="{{ $value }}" @selected($dateRangeMonths === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if ($results !== null)
                        <div class="border-t border-gray-200 pt-4">
                            <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">
                                <x-admin.icon name="sliders" class="w-3.5 h-3.5" />
                                {{ __('Refine results') }}
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="max-w-xs">
                                    <x-input-label for="min_searches" :value="__('Min. avg. monthly searches')" />
                                    <x-text-input id="min_searches" class="block mt-1 w-full" type="number" min="0" name="min_searches" :value="$minSearches ?: null" placeholder="0" />
                                </div>

                                <div>
                                    <x-input-label :value="__('Competition')" />
                                    <div class="mt-1.5 flex items-center gap-4">
                                        @foreach (['Low', 'Medium', 'High'] as $level)
                                            <label class="flex items-center gap-1.5 text-sm text-gray-700">
                                                <input type="checkbox" name="competition[]" value="{{ $level }}" @checked(in_array($level, $competitionFilter, true)) class="rounded border-gray-300 text-slate-700 focus:ring-slate-600">
                                                {{ __($level) }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <x-primary-button>{{ __($results !== null ? 'Update results' : 'Get results') }}</x-primary-button>

                        @if ($results !== null && $results->isNotEmpty())
                            <button
                                type="submit"
                                form="discover-form"
                                formaction="{{ route('discover.export') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                            >
                                <x-admin.icon name="download" class="w-3.5 h-3.5" />
                                {{ __('Export to CSV') }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </x-admin.card>

        @if ($trend !== null)
            <x-admin.card :title="__('Search interest over time')">
                <x-admin.trend-chart :labels="$trend['labels']" :values="$trend['values']" />
            </x-admin.card>
        @endif

        @if ($results !== null)
            <x-admin.card :title="__('Keyword ideas')">
                @if ($results->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No keyword ideas match your filters. Try widening the search volume or competition filters.') }}</p>
                @else
                    <div class="overflow-x-auto -mx-4 sm:-mx-6">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs">
                                    <x-admin.sortable-th column="keyword" :sort="$sort" :dir="$dir" form="discover-form" class="px-4 sm:px-6">{{ __('Keyword') }}</x-admin.sortable-th>
                                    <x-admin.sortable-th column="avg_monthly_searches" :sort="$sort" :dir="$dir" form="discover-form">{{ __('Avg. monthly searches') }}</x-admin.sortable-th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('3 mo. change') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('YoY change') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Competition') }}</th>
                                    <x-admin.sortable-th column="competition_index" :sort="$sort" :dir="$dir" form="discover-form">{{ __('Competition index') }}</x-admin.sortable-th>
                                    <x-admin.sortable-th column="low_range_cpc" :sort="$sort" :dir="$dir" form="discover-form">{{ __('Low range CPC') }}</x-admin.sortable-th>
                                    <x-admin.sortable-th column="high_range_cpc" :sort="$sort" :dir="$dir" form="discover-form" class="px-4 sm:px-6">{{ __('High range CPC') }}</x-admin.sortable-th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($results as $idea)
                                    <tr>
                                        <td class="px-4 sm:px-6 py-2.5 font-medium text-gray-900">{{ $idea->keyword }}</td>
                                        <td class="px-4 py-2.5 text-gray-600">{{ number_format($idea->avgMonthlySearches) }}</td>
                                        <td class="px-4 py-2.5">
                                            <span class="{{ $idea->threeMonthChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $idea->threeMonthChange >= 0 ? '+' : '' }}{{ $idea->threeMonthChange }}%
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="{{ $idea->yoyChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $idea->yoyChange >= 0 ? '+' : '' }}{{ $idea->yoyChange }}%
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span @class([
                                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                                'bg-green-100 text-green-700' => $idea->competition === 'Low',
                                                'bg-amber-100 text-amber-700' => $idea->competition === 'Medium',
                                                'bg-red-100 text-red-700' => $idea->competition === 'High',
                                            ])>{{ $idea->competition }}</span>
                                        </td>
                                        <td class="px-4 py-2.5 text-gray-600">{{ $idea->competitionIndex }}</td>
                                        <td class="px-4 py-2.5 text-gray-600">${{ number_format($idea->lowRangeCpc, 2) }}</td>
                                        <td class="px-4 sm:px-6 py-2.5 text-gray-600">${{ number_format($idea->highRangeCpc, 2) }}</td>
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
