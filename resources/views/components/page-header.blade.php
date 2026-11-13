@props(['eyebrow' => null, 'title', 'subtitle' => null])

<header class="page-header">
    <div>
        @if ($eyebrow)
            <p class="eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 @class(['page-title', 'mt-2' => $eyebrow])>{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</header>
