@props(['breakdown'])

<div>
    <div class="flex h-3 w-full overflow-hidden rounded-full bg-gray-100">
        <div class="bg-indigo-500" style="width: {{ $breakdown->desktopPercent }}%"></div>
        <div class="bg-sky-400" style="width: {{ $breakdown->mobilePercent }}%"></div>
        <div class="bg-amber-400" style="width: {{ $breakdown->tabletPercent }}%"></div>
    </div>

    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-sm">
        <span class="flex items-center gap-1.5 text-gray-700">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
            {{ __('Desktop') }} <span class="text-gray-500">{{ number_format($breakdown->desktopPercent) }}%</span>
        </span>
        <span class="flex items-center gap-1.5 text-gray-700">
            <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>
            {{ __('Mobile') }} <span class="text-gray-500">{{ number_format($breakdown->mobilePercent) }}%</span>
        </span>
        <span class="flex items-center gap-1.5 text-gray-700">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
            {{ __('Tablet') }} <span class="text-gray-500">{{ number_format($breakdown->tabletPercent) }}%</span>
        </span>
    </div>
</div>
