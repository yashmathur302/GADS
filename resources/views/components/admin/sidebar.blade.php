@php
    $navItems = [
        ['label' => __('Discover New Keywords'), 'route' => 'discover.index', 'icon' => 'search'],
        ['label' => __('Keyword Planner'), 'route' => 'planner.index', 'icon' => 'chart'],
        ['label' => __('Keywords'), 'route' => 'keywords.index', 'icon' => 'tag', 'match' => 'keywords.*'],
        ['label' => __('Negative Keywords'), 'route' => 'negative-keywords.index', 'icon' => 'tag-off', 'match' => 'negative-keywords.*'],
        ['label' => __('Location'), 'route' => 'locations.index', 'icon' => 'map-pin', 'match' => 'locations.*'],
    ];
@endphp

<!-- Mobile backdrop -->
<div
    x-show="sidebarOpen"
    x-transition.opacity
    class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
    style="display: none;"
    @click="sidebarOpen = false"
    aria-hidden="true"
></div>

<aside
    class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col bg-gray-900 text-gray-200 transform transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    aria-label="{{ __('Primary') }}"
>
    <div class="h-16 flex items-center gap-2.5 px-6 border-b border-gray-800 shrink-0">
        <a href="{{ route('discover.index') }}" class="flex items-center gap-2.5">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white/10 text-white shrink-0">
                <x-brand-mark class="w-5 h-5" />
            </span>
            <span class="leading-tight">
                <span class="block font-bold text-white tracking-tight">GADS</span>
                <span class="block text-[11px] font-medium text-gray-400 uppercase tracking-wider">{{ __('Analysis Hub') }}</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @foreach ($navItems as $item)
            @php $isActive = request()->routeIs($item['match'] ?? $item['route']); @endphp
            <a
                href="{{ route($item['route']) }}"
                @if ($isActive) aria-current="page" @endif
                class="flex items-center gap-2.5 rounded-md px-3 py-2 text-sm font-medium transition {{ $isActive ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
            >
                <x-admin.icon :name="$item['icon']" class="w-4 h-4 shrink-0" />
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</aside>
