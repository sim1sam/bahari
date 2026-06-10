@php
    $topBarText = $siteSettings->top_bar_text ?? 'Free shipping on dress orders over $50 ✦ New styles every week';
    $topBarMobile = $siteSettings->top_bar_text_mobile ?? 'Free shipping on orders $50+';
    $bgColor = $siteSettings->top_bar_bg_color ?? '#164e63';
    $textColor = $siteSettings->top_bar_text_color ?? '#ffffff';
    $linkColor = $siteSettings->top_bar_link_color ?? '#cffafe';
@endphp
@if ($topBarText || $topBarMobile)
<div class="text-sm" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
    <div class="container-store flex items-center justify-between py-2">
        @if ($topBarText)
            <p class="hidden sm:block">{{ $topBarText }}</p>
        @endif
        @if ($topBarMobile)
            <p class="sm:hidden text-center flex-1">{{ $topBarMobile }}</p>
        @endif
        <div class="hidden md:flex items-center gap-4" style="color: {{ $linkColor }};">
            @if ($siteSettings->contact_email)
                <a href="mailto:{{ $siteSettings->contact_email }}" class="transition-opacity hover:opacity-80" style="color: inherit;">Contact</a>
            @endif
            <a href="{{ route('order.track') }}" class="transition-opacity hover:opacity-80" style="color: inherit;">Track Order</a>
        </div>
    </div>
</div>
@endif
