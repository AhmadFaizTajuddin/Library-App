<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Catalogue') | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Literata:wght@500;700&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/library.css') }}" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-library">
    <div class="container">
        <a class="navbar-brand" href="{{ route('books.index') }}"><i class="bi bi-book-half me-2"></i>{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('books.*') ? 'active' : '' }}" href="{{ route('books.index') }}">Books</a></li>
                @auth
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('loans.mine') ? 'active' : '' }}" href="{{ route('loans.mine') }}">My loans</a></li>
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Categories</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('loans.index') ? 'active' : '' }}" href="{{ route('loans.index') }}">All loans</a></li>
                    @endif
                @endauth
            </ul>
            <div class="d-flex align-items-center gap-2">
                @auth
                    <span class="navbar-text text-white me-1">
                        {{ auth()->user()->name }}
                        @if(auth()->user()->isAdmin()) <span class="badge text-bg-light ms-1">Admin</span> @endif
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-sm btn-light">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @error('alert')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror

    @yield('content')
</main>

<footer class="container pb-4">
    {{ config('app.name') }}: Laravel CRUD exam project.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
