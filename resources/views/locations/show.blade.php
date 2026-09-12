<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Location') }}</h1>
    </x-slot>

    <x-admin.location-list-detail
        :client="$client"
        :locations="$locations"
        import-route="locations.import"
        export-route="locations.export"
        index-route="locations.index"
        :index-label="__('Back to Location')"
    />
</x-app-layout>
