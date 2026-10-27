@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h1 class="h3 mb-3">{{ auth()->user()->isAdmin() ? 'Library overview' : 'Hello, '.auth()->user()->name }}</h1>

<div class="row g-3 mb-4">
    @foreach($stats as $label => $value)
        <div class="col-6 col-md-3">
            <div class="stat">
                <div class="stat-num">{{ $value }}</div>
                <div class="stat-label">{{ $label }}</div>
            </div>
        </div>
    @endforeach
</div>

@if(auth()->user()->isAdmin())
    <div class="row g-4">
        <div class="col-lg-8">
            <h2 class="h5">Latest loans</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Member</th><th>Book</th><th>Due</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($recentLoans as $loan)
                        <tr>
                            <td>{{ $loan->user->name }}</td>
                            <td><a href="{{ route('books.show', $loan->book) }}">{{ $loan->book->title }}</a></td>
                            <td>{{ $loan->due_at->format('d M Y') }}</td>
                            <td>@include('partials.loan-status', ['loan' => $loan])</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-body-secondary">No loans yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-4">
            <h2 class="h5">Most borrowed</h2>
            <div class="panel">
                @forelse($popularBooks as $book)
                    <div class="d-flex justify-content-between py-1">
                        <a href="{{ route('books.show', $book) }}">{{ $book->title }}</a>
                        <span class="text-body-secondary">{{ $book->loans_count }}x</span>
                    </div>
                @empty
                    <span class="text-body-secondary">Nothing has been borrowed yet.</span>
                @endforelse
            </div>
        </div>
    </div>
@else
    <h2 class="h5">Books you have on loan</h2>
    @if($activeLoans->isEmpty())
        <div class="alert alert-light border">You have no books on loan. <a href="{{ route('books.index') }}">Browse the catalogue</a> to borrow one.</div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Book</th><th>Due</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($activeLoans as $loan)
                    <tr>
                        <td><a href="{{ route('books.show', $loan->book) }}">{{ $loan->book->title }}</a></td>
                        <td>{{ $loan->due_at->format('d M Y') }}</td>
                        <td>@include('partials.loan-status', ['loan' => $loan])</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('loans.return', $loan) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-primary">Return</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endif
@endsection
