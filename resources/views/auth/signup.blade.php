@extends('frontend.master')

@section('title', 'Sign Up - ' . config('app.name'))

@section('content')
<section class="auth-section">
    {{-- Background decoration --}}
    <div class="auth-bg-decoration" aria-hidden="true"></div>

    <div class="auth-card">
        @if ($enable_registration)

        {{-- Header --}}
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #7C3AED, var(--color-primary)); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            </div>
            <h3>Create account</h3>
            <p class="auth-subtitle">Join us and start writing today</p>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
        <div style="background: rgba(239,68,68,.1); border: 1px solid var(--color-danger); color: var(--color-danger); padding: 12px 16px; border-radius: var(--radius-small); margin-bottom: 20px; font-size: 14px;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('auth.signup') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="name" style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px;">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label for="username" style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px;">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="johndoe" value="{{ old('username') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email" style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px;">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="{{ old('email') }}" required autocomplete="email">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="password" style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px;">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="password_confirmation" style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px;">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="••••••••" required autocomplete="new-password">
                </div>
            </div>

            <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 24px;">
                <input type="checkbox" name="agree" value="1" id="rememberMe" style="width: 16px; height: 16px; margin-top: 2px; accent-color: var(--color-primary); flex-shrink: 0;" required>
                <label for="rememberMe" style="font-size: 14px; color: var(--text-secondary); cursor: pointer;">
                    I agree to the <a href="#" style="color: var(--color-primary); font-weight: 500;">Terms &amp; Conditions</a>
                </label>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px;">
                Create Account
            </button>
        </form>

        <div class="auth-card-footer">
            Already have an account? <a href="{{ route('auth.login') }}">Log in</a>
        </div>

        @else
        <div style="text-align: center; padding: 32px 0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-danger)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 16px;"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
            <h3 style="font-size: 20px; margin-bottom: 8px;">Registration Closed</h3>
            <p style="color: var(--text-secondary);">User registration is currently not allowed.</p>
            <a href="{{ route('auth.login') }}" class="btn-primary" style="margin-top: 20px; display: inline-flex;">Back to Login</a>
        </div>
        @endif
    </div>
</section>
@endsection
