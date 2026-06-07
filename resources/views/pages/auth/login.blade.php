@extends('layouts.auth')

@section('title', 'Sign In')

@section('brand_heading')
    <h2>Your style journey starts here</h2>
@endsection

@section('brand_text')
    <p>Sign in to track orders, save favorites, and enjoy a faster checkout experience.</p>
@endsection

@section('brand_features')
    <div class="brand-feature">
        <span class="brand-feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </span>
        Track your orders in real time
    </div>
    <div class="brand-feature">
        <span class="brand-feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </span>
        Save items to your wishlist
    </div>
    <div class="brand-feature">
        <span class="brand-feature-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </span>
        Faster checkout on every purchase
    </div>
@endsection

@section('heading', 'Welcome back')
@section('subheading', 'Sign in to your account')

@section('content')
    <form action="{{ route('login.submit') }}" method="POST">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Email address</label>
            <div class="input-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    required
                    autofocus
                    class="form-control @error('email') is-invalid @enderror"
                >
            </div>
            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-wrap">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                    class="form-control @error('password') is-invalid @enderror"
                >
            </div>
            @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        <div class="checkbox-row">
            <label class="checkbox-wrap">
                <input type="checkbox" name="remember">
                Remember me
            </label>
        </div>

        <button type="submit" class="btn-primary">Sign in</button>
    </form>
@endsection

@section('footer')
    Don't have an account? <a href="{{ route('register') }}">Create one</a>
@endsection
