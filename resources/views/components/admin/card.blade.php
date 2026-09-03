@props(['title' => null])

<section {{ $attributes->class(['bg-white shadow-sm sm:rounded-lg']) }}>
    @if ($title || isset($actions))
        <div class="flex items-center justify-between px-4 py-4 sm:px-6 border-b border-gray-200">
            @if ($title)
                <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
            @endif

            @isset($actions)
                <div>{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="px-4 py-5 sm:p-6">
        {{ $slot }}
    </div>
</section>
