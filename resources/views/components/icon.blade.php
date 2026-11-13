@props(['name' => 'grid', 'class' => 'size-5'])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('grid')
            <rect x="3" y="3" width="7" height="7" rx="1.5" />
            <rect x="14" y="3" width="7" height="7" rx="1.5" />
            <rect x="3" y="14" width="7" height="7" rx="1.5" />
            <rect x="14" y="14" width="7" height="7" rx="1.5" />
            @break

        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            @break

        @case('layers')
            <path d="m12 2 9 5-9 5-9-5 9-5Z" />
            <path d="m3 12 9 5 9-5" />
            <path d="m3 17 9 5 9-5" />
            @break

        @case('book')
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
            @break

        @case('certificate')
            <rect x="5" y="3" width="14" height="12" rx="1.5" />
            <path d="m9 15-1 6 4-2 4 2-1-6" />
            <path d="M9 7h6M9 10h4" />
            @break

        @case('reissue')
            <path d="M3 12a9 9 0 0 1 15-6.7L21 8" />
            <path d="M21 3v5h-5" />
            <path d="M21 12a9 9 0 0 1-15 6.7L3 16" />
            <path d="M3 21v-5h5" />
            @break

        @case('chart')
            <path d="M3 3v18h18" />
            <path d="M7 15v3M12 10v8M17 6v12" />
            @break

        @case('archive')
            <rect x="3" y="4" width="18" height="4" rx="1" />
            <path d="M5 8v12h14V8" />
            <path d="M10 12h4" />
            @break

        @case('settings')
            <path d="M4 6h16M7 12h10M10 18h4" />
            <circle cx="8" cy="6" r="1.5" />
            <circle cx="15" cy="12" r="1.5" />
            <circle cx="11" cy="18" r="1.5" />
            @break

        @case('shield')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            <path d="m9 12 2 2 4-4" />
            @break

        @case('search')
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3.5-3.5" />
            @break

        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16" />
            @break

        @case('close')
            <path d="m6 6 12 12M18 6 6 18" />
            @break

        @case('arrow-right')
            <path d="M5 12h14" />
            <path d="m13 6 6 6-6 6" />
            @break

        @case('download')
            <path d="M12 3v12" />
            <path d="m7 10 5 5 5-5" />
            <path d="M5 21h14" />
            @break

        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break

        @case('check')
            <path d="m5 12 4 4L19 6" />
            @break

        @case('warning')
            <path d="M12 9v4" />
            <path d="M12 17h.01" />
            <path d="M10.3 3.7 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z" />
            @break

        @case('logout')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="m16 17 5-5-5-5" />
            <path d="M21 12H9" />
            @break

        @case('filter')
            <path d="M4 6h16M7 12h10M10 18h4" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
