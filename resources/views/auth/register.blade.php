@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div class="auth-card panel">
    <h1 class="h3 mb-3">Create your account</h1>
    <form method="POST" action="{{ url('/register') }}">
        @csrf
        <x-field name="name" label="Full name" required autofocus />
        <x-field name="email" label="Email" type="email" required />
        <x-field name="password" label="Password (at least 8 characters)" type="password" required />
        <x-field name="password_confirmation" label="Confirm password" type="password" required />
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
    <p class="mt-3 mb-0 small">Already registered? <a href="{{ route('login') }}">Log in</a>.</p>
</div>
@endsection
