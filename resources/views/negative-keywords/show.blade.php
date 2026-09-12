<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Negative Keywords') }}</h1>
    </x-slot>

    <x-admin.keyword-list-detail
        :client="$client"
        :keywords="$keywords"
        import-route="negative-keywords.import"
        export-route="negative-keywords.export"
        index-route="negative-keywords.index"
        :index-label="__('Back to Negative Keywords')"
    />
</x-app-layout>
