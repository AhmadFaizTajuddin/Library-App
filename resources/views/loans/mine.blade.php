@extends('layouts.app')
@section('title', 'My loans')
@section('content')
<h1 class="h3 mb-1">My loans</h1>
<p class="text-body-secondary">
    Books are lent for {{ \App\Models\Loan::LOAN_DAYS }} days, and you can hold up to {{ \App\Models\Loan::MAX_ACTIVE }} at a time.
    Late returns cost Rp {{ number_format(\App\Models\Loan::FINE_PER_DAY, 0, ',', '.') }} per day.
</p>

<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th><th>Fine</th><th></th></tr></thead>
        <tbody>
        @forelse($loans as $loan)
            <tr>
                <td><a href="{{ route('books.show', $loan->book) }}">{{ $loan->book->title }}</a></td>
                <td>{{ $loan->borrowed_at->format('d M Y') }}</td>
                <td>{{ $loan->due_at->format('d M Y') }}</td>
                <td>{{ $loan->returned_at?->format('d M Y') ?? '-' }}</td>
                <td>@include('partials.loan-status', ['loan' => $loan])</td>
                <td>Rp {{ number_format($loan->currentFine(), 0, ',', '.') }}</td>
                <td class="text-end">
                    @unless($loan->returned_at)
                        <form method="POST" action="{{ route('loans.return', $loan) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-primary">Return</button>
                        </form>
                    @endunless
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-body-secondary">You have not borrowed anything yet. <a href="{{ route('books.index') }}">Find a book</a>.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $loans->links() }}
@endsection
