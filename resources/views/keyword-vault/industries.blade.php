<x-app-layout>
    <x-slot name="header">
        <x-admin.breadcrumbs :items="[['label' => 'Assets & Creative Library'], ['label' => $type->label()]]" />
        <h1 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">{{ $type->label() }}</h1>
    </x-slot>

    <x-admin.card>
        <p class="text-sm text-gray-500 mb-5">
            {{ __('Choose an industry to view or add its :noun.', ['noun' => $type->nounPlural()]) }}
        </p>

        <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($industries as $industry)
                <li>
                    <a
                        href="{{ route($baseRoute.'.show', $industry) }}"
                        class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-4 py-3 hover:border-gray-300 hover:bg-gray-50 transition"
                    >
                        <span class="text-sm font-medium text-gray-900">{{ $industry->name }}</span>
                        <span class="shrink-0 inline-flex items-center justify-center min-w-[1.75rem] h-7 px-2 rounded-full bg-gray-100 text-xs font-semibold text-gray-600">
                            {{ $industry->keyword_vault_entries_count }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </x-admin.card>
</x-app-layout>
