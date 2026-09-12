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

        @case('chevron-down')
            <polyline points="6 9 12 15 18 9" />
            @break

        @case('chevron-up')
            <polyline points="6 15 12 9 18 15" />
            @break

        @case('download')
            <path d="M12 3v12" />
            <polyline points="7 10 12 15 17 10" />
            <path d="M4 19h16" />
            @break

        @case('sliders')
            <line x1="4" y1="6" x2="20" y2="6" />
            <circle cx="9" cy="6" r="2" fill="currentColor" stroke="none" />
            <line x1="4" y1="12" x2="20" y2="12" />
            <circle cx="15" cy="12" r="2" fill="currentColor" stroke="none" />
            <line x1="4" y1="18" x2="20" y2="18" />
            <circle cx="11" cy="18" r="2" fill="currentColor" stroke="none" />
            @break

        @case('tag')
            <path d="M20.59 13.41 12 22 2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82Z" />
            <circle cx="7" cy="7" r="1.25" fill="currentColor" stroke="none" />
            @break

        @case('tag-off')
            <path d="M20.59 13.41 12 22 2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82Z" />
            <line x1="4" y1="4" x2="20" y2="20" />
            @break

        @case('upload')
            <path d="M12 21V9" />
            <polyline points="7 14 12 9 17 14" />
            <path d="M4 19h16" />
            @break
    @endswitch
</svg>
