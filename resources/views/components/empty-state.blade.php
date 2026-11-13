@props(['icon' => 'search', 'title', 'description' => null])

<div class="empty-state">
    <span class="grid size-11 place-items-center rounded-full border border-ink/10 bg-white text-ink/40">
        <x-icon :name="$icon" class="size-5" />
    </span>
    <p class="mt-3 font-semibold text-ink">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 max-w-md text-sm leading-6 text-ink/50">{{ $description }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
