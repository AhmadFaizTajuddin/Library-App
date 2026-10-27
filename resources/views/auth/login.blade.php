@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<div class="auth-card panel">
    <h1 class="h3 mb-3">Log in</h1>
    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <x-field name="email" label="Email" type="email" required autofocus />
        <x-field name="password" label="Password" type="password" required />
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
            <label class="form-check-label" for="remember">Keep me logged in</label>
        </div>
        <button type="submit" class="btn btn-primary w-100">Log in</button>
    </form>
    <p class="mt-3 mb-0 small">No account yet? <a href="{{ route('register') }}">Register as a member</a>.</p>
</div>
@endsection
