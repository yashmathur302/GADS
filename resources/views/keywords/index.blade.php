<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Keywords') }}</h1>
    </x-slot>

    <x-admin.card>
        <x-admin.client-list
            :clients="$clients"
            count-key="keywords_count"
            :count-label="__('No. of keywords')"
            show-route="keywords.show"
            context="keywords"
        />
    </x-admin.card>
</x-app-layout>
