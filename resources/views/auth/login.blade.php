@extends('frontend.master')

@section('title', 'Log In - ' . config('app.name'))

@section('content')
<section class="auth-section">
    {{-- Background decoration --}}
    <div class="auth-bg-decoration" aria-hidden="true"></div>

    <div class="auth-card">
        {{-- Header --}}
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, var(--color-primary), #7C3AED); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
            </div>
            <h3>Welcome back</h3>
            <p class="auth-subtitle">Log in to continue to your account</p>
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
        <form action="{{ route('auth.login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email_or_username" style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px;">Email or Username</label>
                <input
                    type="text"
                    id="email_or_username"
                    name="email_or_username"
                    class="form-control"
                    placeholder="you@example.com"
                    value="{{ old('email_or_username') }}"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password" style="display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px;">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >
            </div>

            <div style="display: flex; align-items: center; margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: var(--text-secondary);">
                    <input type="checkbox" name="remember" value="1" id="rememberMe" style="width: 16px; height: 16px; accent-color: var(--color-primary);">
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px;">
                Log In
            </button>
        </form>

        <div class="auth-card-footer">
            Don't have an account? <a href="{{ route('auth.signup') }}">Create one</a>
        </div>
    </div>
</section>
@endsection
