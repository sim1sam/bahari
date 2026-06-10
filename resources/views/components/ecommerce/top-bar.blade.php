@php
    $topBarText = $siteSettings->top_bar_text ?? 'Free shipping on dress orders over $50 ✦ New styles every week';
    $topBarMobile = $siteSettings->top_bar_text_mobile ?? 'Free shipping on orders $50+';
@endphp
<div class="bg-brand-900 text-white text-sm">
    <div class="container-store flex items-center justify-between py-2">
        <p class="hidden sm:block">{{ $topBarText }}</p>
        <p class="sm:hidden text-center flex-1">{{ $topBarMobile }}</p>
        <div class="hidden md:flex items-center gap-4 text-brand-100">
            @if ($siteSettings->contact_email)
                <a href="mailto:{{ $siteSettings->contact_email }}" class="hover:text-white transition-colors">Contact</a>
            @endif
            <a href="{{ route('account.orders') }}" class="hover:text-white transition-colors">Track Order</a>
        </div>
    </div>
</div>
