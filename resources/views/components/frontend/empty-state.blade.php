@props([
    'icon' => 'file-text',
    'title' => 'No Data Found',
    'message' => 'There are no items to display at the moment.',
    'ctaText' => null,
    'ctaUrl' => null
])

<div class="empty-state-container" style="padding: var(--space-5) var(--space-3); text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-card); box-shadow: var(--shadow-sm); margin-top: var(--space-4);">
    <div class="empty-state-icon-wrapper" style="width: 64px; height: 64px; border-radius: 50%; background: var(--bg-secondary); color: var(--text-muted); display: flex; align-items: center; justify-content: center; margin-bottom: var(--space-2);">
        <x-icon name="{{ $icon }}" width="32" height="32" />
    </div>
    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-primary); margin: 0 0 8px 0;">{{ $title }}</h3>
    <p style="font-size: 14px; color: var(--text-secondary); max-width: 320px; margin: 0 0 var(--space-3) 0; line-height: 1.5;">{{ $message }}</p>
    
    @if ($ctaText && $ctaUrl)
        <a href="{{ $ctaUrl }}" class="btn-primary" style="padding: 10px 20px; font-size: 13px; font-weight: 600; text-decoration: none;">
            {{ $ctaText }}
        </a>
    @endif
</div>
