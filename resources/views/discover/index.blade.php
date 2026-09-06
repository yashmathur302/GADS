<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Discover New Keywords') }}</h1>
    </x-slot>

    <div class="space-y-6">
        <x-admin.sample-data-notice />

        <x-admin.card>
            <div x-data="{ mode: {{ old('website_url') ? "'website'" : "'keywords'" }} }">
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

                <form method="POST" action="{{ route('discover.search') }}" class="space-y-4">
                    @csrf

                    <div x-show="mode === 'keywords'">
                        <x-input-label for="seed_keywords" :value="__('Enter keywords, separated by commas')" />
                        <x-text-input id="seed_keywords" class="block mt-1 w-full" type="text" name="seed_keywords" :value="old('seed_keywords')" placeholder="e.g. emergency plumber, drain cleaning" />
                        <x-input-error :messages="$errors->get('seed_keywords')" class="mt-2" />
                    </div>

                    <div x-show="mode === 'website'" style="display: none;">
                        <x-input-label for="website_url" :value="__('Enter your website URL')" />
                        <x-text-input id="website_url" class="block mt-1 w-full" type="url" name="website_url" :value="old('website_url')" placeholder="https://example.com/plumbing-services" />
                        <x-input-error :messages="$errors->get('website_url')" class="mt-2" />
                    </div>

                    <x-primary-button>{{ __('Get results') }}</x-primary-button>
                </form>
            </div>
        </x-admin.card>

        @if ($results !== null)
            <x-admin.card :title="__('Keyword ideas')">
                @if ($results->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No keyword ideas found. Try different keywords or a different page.') }}</p>
                @else
                    <div class="overflow-x-auto -mx-4 sm:-mx-6">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    <th class="px-4 sm:px-6 py-2">{{ __('Keyword') }}</th>
                                    <th class="px-4 py-2">{{ __('Avg. monthly searches') }}</th>
                                    <th class="px-4 py-2">{{ __('Competition') }}</th>
                                    <th class="px-4 py-2">{{ __('Competition index') }}</th>
                                    <th class="px-4 py-2">{{ __('Low range CPC') }}</th>
                                    <th class="px-4 sm:px-6 py-2">{{ __('High range CPC') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($results as $idea)
                                    <tr>
                                        <td class="px-4 sm:px-6 py-2.5 font-medium text-gray-900">{{ $idea->keyword }}</td>
                                        <td class="px-4 py-2.5 text-gray-600">{{ number_format($idea->avgMonthlySearches) }}</td>
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
