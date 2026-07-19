@if ($socialmedia->count() > 0)
<div class="widget">
    <div class="widget-title">
        <h5>Stay Connected</h5>
    </div>
    <div class="widget-social-grid">
        @foreach ($socialmedia as $media)
        @php
            $safeLink = $media->link;
            if (str_contains(strtolower($safeLink), 'kontol') || !filter_var($safeLink, FILTER_VALIDATE_URL)) {
                $safeLink = 'https://' . strtolower($media->platform ?? $media->title ?? 'facebook') . '.com';
            }
        @endphp
        <a href="{{ $safeLink }}" target="_blank" class="widget-social-item">
            <x-icon name="{{ strtolower($media->title) }}" width="16" height="16" />
            <span>{{ $media->title }}</span>
        </a>
        @endforeach
    </div>
</div>
@endif
