<x-app-layout>
    <x-slot name="header">
        <x-admin.breadcrumbs :items="[['label' => 'Assets & Creative Library'], ['label' => $type->label()]]" />
        <h1 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">{{ $type->label() }}</h1>
    </x-slot>

    <div class="space-y-6">
        <x-admin.card :title="__('Add industry')">
            <form method="POST" action="{{ route('assets.industries.store') }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
                @csrf

                <div class="flex-1">
                    <x-input-label for="name" :value="__('Industry name')" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" placeholder="e.g. Veterinary Clinics" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <x-primary-button>{{ __('Add industry') }}</x-primary-button>
            </form>
        </x-admin.card>

        <x-admin.card>
            <p class="text-sm text-gray-500 mb-5">
                {{ __('Choose an industry to view its sub-categories and :noun.', ['noun' => $type->nounPlural()]) }}
            </p>

            <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($industries as $industry)
                    <li class="flex items-center gap-2">
                        <a
                            href="{{ route($baseRoute.'.industry', $industry) }}"
                            class="flex-1 min-w-0 flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-4 py-3 hover:border-gray-300 hover:bg-gray-50 transition"
                        >
                            <span class="text-sm font-medium text-gray-900 truncate">{{ $industry->name }}</span>
                            <span class="shrink-0 inline-flex items-center justify-center min-w-[1.75rem] h-7 px-2 rounded-full bg-gray-100 text-xs font-semibold text-gray-600">
                                {{ $industry->keyword_vault_entries_count }}
                            </span>
                        </a>

                        <form
                            method="POST"
                            action="{{ route('assets.industries.destroy', $industry) }}"
                            onsubmit="return confirm('{{ __('Delete :name and all of its sub-categories and keywords? This cannot be undone.', ['name' => $industry->name]) }}');"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                                aria-label="{{ __('Delete :name', ['name' => $industry->name]) }}"
                            >
                                <x-admin.icon name="trash" class="w-4 h-4" />
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    </div>
</x-app-layout>
