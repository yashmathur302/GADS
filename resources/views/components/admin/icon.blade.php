@props(['name'])

<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
    @switch($name)
        @case('dashboard')
            <rect x="3" y="3" width="8" height="8" rx="1.5" />
            <rect x="13" y="3" width="8" height="8" rx="1.5" />
            <rect x="3" y="13" width="8" height="8" rx="1.5" />
            <rect x="13" y="13" width="8" height="8" rx="1.5" />
            @break

        @case('target')
            <circle cx="12" cy="12" r="8" />
            <circle cx="12" cy="12" r="4.5" />
            <circle cx="12" cy="12" r="1" fill="currentColor" stroke="none" />
            @break

        @case('bulb')
            <circle cx="12" cy="10" r="6" />
            <line x1="12" y1="16" x2="12" y2="18" />
            <line x1="9" y1="18" x2="15" y2="18" />
            <line x1="10" y1="21" x2="14" y2="21" />
            @break

        @case('wrench')
            <rect x="3" y="10" width="18" height="9" rx="1.5" />
            <path d="M8 10V7a4 4 0 0 1 8 0v3" />
            <line x1="3" y1="14.5" x2="21" y2="14.5" />
            @break

        @case('gear')
            <circle cx="12" cy="12" r="3.25" />
            <circle cx="12" cy="12" r="7.5" />
            <line x1="12" y1="2.5" x2="12" y2="4.5" />
            <line x1="12" y1="19.5" x2="12" y2="21.5" />
            <line x1="2.5" y1="12" x2="4.5" y2="12" />
            <line x1="19.5" y1="12" x2="21.5" y2="12" />
            <line x1="5.5" y1="5.5" x2="7" y2="7" />
            <line x1="17" y1="17" x2="18.5" y2="18.5" />
            <line x1="18.5" y1="5.5" x2="17" y2="7" />
            <line x1="7" y1="17" x2="5.5" y2="18.5" />
            @break

        @case('chevron-down')
            <polyline points="6 9 12 15 18 9" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <line x1="12" y1="7" x2="12" y2="12" />
            <line x1="12" y1="12" x2="15.5" y2="14" />
            @break
    @endswitch
</svg>
