<nav class="account-nav" aria-label="Account sections">
    <a href="{{ route('admin.bank-payments.create') }}" class="account-nav-link @if (request()->routeIs('admin.bank-payments.*')) active @endif">
        <span class="account-nav-icon"><i class="fas fa-hand-holding-usd"></i></span>
        <span>Make Payment</span>
    </a>
    <a href="{{ route('admin.bank-inter-transfers.create') }}" class="account-nav-link @if (request()->routeIs('admin.bank-inter-transfers.*')) active @endif">
        <span class="account-nav-icon"><i class="fas fa-exchange-alt"></i></span>
        <span>Inter Transfer</span>
    </a>
    <a href="{{ route('admin.account-heads.index') }}" class="account-nav-link @if (request()->routeIs('admin.account-heads.*')) active @endif">
        <span class="account-nav-icon"><i class="fas fa-list"></i></span>
        <span>Account Heads</span>
    </a>
    <a href="{{ route('admin.account-types.index') }}" class="account-nav-link @if (request()->routeIs('admin.account-types.*')) active @endif">
        <span class="account-nav-icon"><i class="fas fa-tags"></i></span>
        <span>Account Types</span>
    </a>
    <a href="{{ route('admin.account-expenses.index') }}" class="account-nav-link @if (request()->routeIs('admin.account-expenses.*')) active @endif">
        <span class="account-nav-icon"><i class="fas fa-receipt"></i></span>
        <span>Expenses</span>
    </a>
</nav>

@once
    @push('styles')
        @include('admin.account.partials.nav-styles')
    @endpush
@endonce
