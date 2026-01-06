<div class="card {{ $class ?? '' }}">
    @if(isset($title))
    <div class="card-title">
        <span>{{ $title }}</span>
        @if(isset($icon))
        <i class="{{ $icon }}"></i>
        @endif
    </div>
    @endif
    {{ $slot }}
</div>