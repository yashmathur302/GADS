<x-app-layout>
    <x-slot name="header">
        <x-admin.breadcrumbs :items="[
            ['label' => 'Assets & Creative Library'],
            ['label' => $type->label(), 'url' => route($baseRoute)],
            ['label' => $industry->name],
        ]" />
        <h1 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">{{ $industry->name }}</h1>
    </x-slot>

    <div class="space-y-6">
        <x-admin.card :title="__('Add :noun', ['noun' => $type->nounSingular()])">
            <form method="POST" action="{{ route($baseRoute.'.store', $industry) }}" class="space-y-4">
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
                <p class="text-sm text-gray-500">{{ __('No :noun yet for this industry.', ['noun' => $type->nounPlural()]) }}</p>
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

                            <form method="POST" action="{{ route($baseRoute.'.destroy', [$industry, $entry]) }}" class="shrink-0" onsubmit="return confirm('{{ __('Remove this keyword?') }}');">
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
