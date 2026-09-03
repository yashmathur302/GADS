@php
    $navItems = [
        ['label' => __('Dashboard'), 'route' => 'dashboard'],
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
    class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-gray-200 transform transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    aria-label="{{ __('Primary') }}"
>
    <div class="h-16 flex items-center px-6 border-b border-gray-800">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold text-white">
            <x-application-logo class="h-8 w-auto fill-current text-white" />
            <span>{{ config('app.name') }}</span>
        </a>
    </div>

    <nav class="px-3 py-4 space-y-1">
        @foreach ($navItems as $item)
            <a
                href="{{ route($item['route']) }}"
                @if (request()->routeIs($item['route'])) aria-current="page" @endif
                class="block rounded-md px-3 py-2 text-sm font-medium transition {{ request()->routeIs($item['route']) ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</aside>
