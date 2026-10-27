@extends('layouts.app')
@section('title', 'All loans')
@section('content')
<h1 class="h3 mb-3">All loans</h1>

<ul class="nav nav-pills mb-3">
    @foreach(['' => 'All', 'active' => 'On loan', 'overdue' => 'Overdue', 'returned' => 'Returned'] as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ (string) $status === (string) $key ? 'active' : '' }}"
               href="{{ route('loans.index', $key === '' ? [] : ['status' => $key]) }}">{{ $label }}</a>
        </li>
    @endforeach
</ul>

<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Member</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th><th>Fine</th><th></th></tr></thead>
        <tbody>
        @forelse($loans as $loan)
            <tr>
                <td>{{ $loan->user->name }}</td>
                <td><a href="{{ route('books.show', $loan->book) }}">{{ $loan->book->title }}</a></td>
                <td>{{ $loan->borrowed_at->format('d M Y') }}</td>
                <td>{{ $loan->due_at->format('d M Y') }}</td>
                <td>{{ $loan->returned_at?->format('d M Y') ?? '-' }}</td>
                <td>@include('partials.loan-status', ['loan' => $loan])</td>
                <td>Rp {{ number_format($loan->currentFine(), 0, ',', '.') }}</td>
                <td class="text-end">
                    <div class="d-inline-flex gap-2">
                        @unless($loan->returned_at)
                            <form method="POST" action="{{ route('loans.return', $loan) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-outline-primary">Mark returned</button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('loans.destroy', $loan) }}" onsubmit="return confirm('Delete this loan record?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-body-secondary">No loans match this filter.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $loans->links() }}
@endsection
