@extends('layouts.app')
@section('title', $book->title)
@section('content')
<a href="{{ route('books.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to the catalogue</a>

<div class="row g-4 mt-1">
    <div class="col-md-4 col-lg-3" style="--spine: {{ $book->spineColor() }}">
        @if($book->coverUrl())
            <img src="{{ $book->coverUrl() }}" alt="Cover of {{ $book->title }}" class="cover cover-lg">
        @else
            <div class="cover cover-ph cover-lg" aria-hidden="true">{{ mb_strtoupper(mb_substr($book->title, 0, 1)) }}</div>
        @endif
    </div>
    <div class="col-md-8 col-lg-9">
        <h1 class="h2 mb-1">{{ $book->title }}</h1>
        <p class="text-body-secondary">by {{ $book->author }}</p>

        <dl class="row mb-3">
            <dt class="col-sm-3">Category</dt><dd class="col-sm-9">{{ $book->category->name }}</dd>
            <dt class="col-sm-3">ISBN</dt><dd class="col-sm-9">{{ $book->isbn }}</dd>
            <dt class="col-sm-3">Year</dt><dd class="col-sm-9">{{ $book->year }}</dd>
            <dt class="col-sm-3">Copies in stock</dt>
            <dd class="col-sm-9">
                @if($book->stock > 0) <span class="text-success">{{ $book->stock }}</span>
                @else <span class="text-danger">0 (out of stock)</span> @endif
            </dd>
        </dl>

        @if($book->description)
            <p>{!! nl2br(e($book->description)) !!}</p>
        @endif

        <div class="d-flex flex-wrap gap-2 mt-4">
            @guest
                <a href="{{ route('login') }}" class="btn btn-primary">Log in to borrow</a>
            @else
                @if($activeLoan)
                    <div class="alert alert-info mb-0">
                        You have this book on loan. Due {{ $activeLoan->due_at->format('d M Y') }}.
                    </div>
                @elseif($book->stock > 0)
                    <form method="POST" action="{{ route('loans.store') }}">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <button type="submit" class="btn btn-primary">Borrow this book</button>
                    </form>
                @else
                    <button type="button" class="btn btn-secondary" disabled>Out of stock</button>
                @endif

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('books.edit', $book) }}" class="btn btn-outline-secondary">Edit</a>
                    <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Delete this book?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">Delete</button>
                    </form>
                @endif
            @endguest
        </div>
    </div>
</div>
@endsection
