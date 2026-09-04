@php
    $sections = config('nav.admin');
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
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
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
        @foreach ($sections as $section)
            @if (isset($section['children']))
                @php
                    $groupActive = collect($section['children'])->contains(fn ($child) => request()->routeIs($child['route']));
                    $groupId = 'nav-group-'.Illuminate\Support\Str::slug($section['label']);
                @endphp

                <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }">
                    <button
                        type="button"
                        @click="open = ! open"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md text-sm font-semibold text-gray-200 hover:bg-gray-800 hover:text-white transition"
                        :aria-expanded="open.toString()"
                        aria-controls="{{ $groupId }}"
                    >
                        <x-admin.icon :name="$section['icon']" class="w-4 h-4 shrink-0 text-gray-400" />
                        <span class="flex-1 text-left">{{ $section['label'] }}</span>
                        <x-admin.icon
                            name="chevron-down"
                            class="w-3.5 h-3.5 shrink-0 text-gray-500 transition-transform duration-200"
                            x-bind:class="{ 'rotate-180': open }"
                        />
                    </button>

                    <ul
                        id="{{ $groupId }}"
                        x-show="open"
                        x-transition
                        class="mt-0.5 ml-[1.625rem] pl-2.5 border-l border-gray-800 space-y-0.5"
                        style="{{ $groupActive ? '' : 'display: none;' }}"
                    >
                        @foreach ($section['children'] as $child)
                            <li>
                                <a
                                    href="{{ route($child['route']) }}"
                                    @if (request()->routeIs($child['route'])) aria-current="page" @endif
                                    class="block rounded-md px-2.5 py-1.5 text-sm transition {{ request()->routeIs($child['route']) ? 'bg-gray-800 text-white font-medium' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}"
                                >
                                    {{ $child['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <a
                    href="{{ route($section['route']) }}"
                    @if (request()->routeIs($section['route'])) aria-current="page" @endif
                    class="flex items-center gap-2.5 rounded-md px-3 py-2 text-sm font-medium transition {{ request()->routeIs($section['route']) ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                >
                    <x-admin.icon :name="$section['icon']" class="w-4 h-4 shrink-0" />
                    {{ $section['label'] }}
                </a>
            @endif
        @endforeach
    </nav>
</aside>
