<div class="stat-card {{ $type ?? '' }}">
    <p class="stat-label">{{ $label }}</p>
    <div class="stat-value">
        <h2>{{ $value }}</h2>
        @if(isset($unit))
        <span class="currency">{{ $unit }}</span>
        @endif
    </div>
    @if(isset($footer))
    <div class="stat-footer">
        {{ $footer }}
    </div>
    @endif
</div>