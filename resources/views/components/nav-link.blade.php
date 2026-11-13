@props(['href', 'icon' => 'grid', 'active' => false])

<a href="{{ $href }}" @class(['nav-item', 'nav-item-active' => $active]) @if ($active) aria-current="page" @endif>
    <x-icon :name="$icon" class="size-[1.15rem] shrink-0" />
    <span>{{ $slot }}</span>
</a>
