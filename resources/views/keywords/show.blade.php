<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Keywords') }}</h1>
    </x-slot>

    <x-admin.keyword-list-detail
        :client="$client"
        :keywords="$keywords"
        import-route="keywords.import"
        export-route="keywords.export"
        index-route="keywords.index"
        :index-label="__('Back to Keywords')"
    />
</x-app-layout>
