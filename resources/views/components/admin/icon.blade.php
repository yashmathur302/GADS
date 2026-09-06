@props(['name'])

<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
    @switch($name)
        @case('search')
            <circle cx="10.5" cy="10.5" r="6.5" />
            <line x1="20" y1="20" x2="15.5" y2="15.5" />
            @break

        @case('chart')
            <polyline points="4 17 9 11 13 14 20 6" />
            <polyline points="14 6 20 6 20 12" />
            @break

        @case('info')
            <circle cx="12" cy="12" r="9" />
            <line x1="12" y1="11" x2="12" y2="16" />
            <circle cx="12" cy="7.75" r="0.75" fill="currentColor" stroke="none" />
            @break
    @endswitch
</svg>
