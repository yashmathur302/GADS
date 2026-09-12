<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Negative Keywords') }}</h1>
    </x-slot>

    <x-admin.card>
        <x-admin.client-list
            :clients="$clients"
            count-key="negative_keywords_count"
            :count-label="__('No. of negative keywords')"
            show-route="negative-keywords.show"
            context="negative-keywords"
        />
    </x-admin.card>
</x-app-layout>
