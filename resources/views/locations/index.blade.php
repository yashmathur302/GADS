<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Location') }}</h1>
    </x-slot>

    <x-admin.card>
        <x-admin.client-list
            :clients="$clients"
            count-key="locations_count"
            :count-label="__('No. of locations')"
            show-route="locations.show"
            context="location"
        />
    </x-admin.card>
</x-app-layout>
