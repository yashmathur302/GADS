<x-app-layout>
    <x-slot name="header">
        <x-admin.breadcrumbs :items="[['label' => $group], ['label' => $title]]" />
        <h1 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h1>
    </x-slot>

    <x-admin.card>
        <div class="text-center py-10">
            <div class="mx-auto flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 text-slate-500 mb-4">
                <x-admin.icon name="clock" class="w-6 h-6" />
            </div>
            <h2 class="text-base font-semibold text-gray-900">{{ __('Coming soon') }}</h2>
            <p class="mt-1.5 text-sm text-gray-500 max-w-md mx-auto">{{ $description }}</p>
        </div>
    </x-admin.card>
</x-app-layout>
