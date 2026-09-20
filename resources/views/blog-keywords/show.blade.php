<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Blog Keywords') }}</h1>
    </x-slot>

    <x-admin.blog-keyword-list-detail
        :client="$client"
        :blog-keywords="$blogKeywords"
        import-route="blog-keywords.import"
        export-route="blog-keywords.export"
        delete-route="blog-keywords.destroy"
        index-route="blog-keywords.index"
        :index-label="__('Back to Blog Keywords')"
    />
</x-app-layout>
