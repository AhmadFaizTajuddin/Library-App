@extends('layouts.app')
@section('title', 'Books')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Book catalogue</h1>
    @auth
        @if(auth()->user()->isAdmin())
            <a href="{{ route('books.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add book</a>
        @endif
    @endauth
</div>

<form method="GET" action="{{ route('books.index') }}" class="row g-2 align-items-end mb-4">
    <div class="col-md-5">
        <label for="q" class="form-label">Search</label>
        <input type="search" id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="Title or author">
    </div>
    <div class="col-md-3">
        <label for="category" class="form-label">Category</label>
        <select id="category" name="category" class="form-select">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="available" name="available" value="1" @checked(request()->boolean('available'))>
            <label class="form-check-label" for="available">In stock only</label>
        </div>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">Search</button>
        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="row g-3">
    @forelse($books as $book)
        <div class="col-lg-6">
            <article class="book-entry" style="--spine: {{ $book->spineColor() }}">
                @if($book->coverUrl())
                    <img src="{{ $book->coverUrl() }}" alt="Cover of {{ $book->title }}" class="cover">
                @else
                    <div class="cover cover-ph" aria-hidden="true">{{ mb_strtoupper(mb_substr($book->title, 0, 1)) }}</div>
                @endif
                <div class="flex-fill">
                    <h2 class="h5 mb-1"><a href="{{ route('books.show', $book) }}" class="text-reset text-decoration-none">{{ $book->title }}</a></h2>
                    <div class="text-body-secondary small mb-2">by {{ $book->author }} ({{ $book->year }})</div>
                    <div class="mb-2">
                        <span class="badge text-bg-light border">{{ $book->category->name }}</span>
                        @if($book->stock > 0)
                            <span class="text-success small ms-2">{{ $book->stock }} in stock</span>
                        @else
                            <span class="text-danger small ms-2">Out of stock</span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('books.show', $book) }}" class="btn btn-sm btn-outline-primary">Details</a>
                        @auth
                            @if($book->stock > 0)
                                <form method="POST" action="{{ route('loans.store') }}">
                                    @csrf
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    <button type="submit" class="btn btn-sm btn-primary">Borrow</button>
                                </form>
                            @endif
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('books.edit', $book) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Delete this book?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
            </article>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-light border mb-0">No books match your search. Try different words or reset the filters.</div>
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $books->links() }}</div>
@endsection
