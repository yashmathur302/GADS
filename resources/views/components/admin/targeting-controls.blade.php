@props(['context'])

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="language" :value="__('Language')" />
        <select id="language" name="language" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            @foreach (\App\Services\GoogleAds\Support\Languages::options() as $code => $label)
                <option value="{{ $code }}" @selected($context->language === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="location" :value="__('Location')" />
        <select id="location" name="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            @foreach (\App\Services\GoogleAds\Support\Locations::options() as $code => $label)
                <option value="{{ $code }}" @selected($context->location === $code)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label :value="__('Network')" />
        <label class="mt-1 flex items-center gap-2 text-sm text-gray-700 h-[38px]">
            <input type="hidden" name="include_search_partners" value="0">
            <input
                type="checkbox"
                name="include_search_partners"
                value="1"
                @checked($context->includeSearchPartners)
                class="rounded border-gray-300 text-slate-700 focus:ring-slate-600"
            >
            {{ __('Include Google Search partners') }}
        </label>
    </div>
</div>
