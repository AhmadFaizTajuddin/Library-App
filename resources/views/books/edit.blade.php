@extends('layouts.app')
@section('title', 'Edit book')
@section('content')
<h1 class="h3 mb-3">Edit "{{ $book->title }}"</h1>
<div class="panel">
    <form method="POST" action="{{ route('books.update', $book) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('books._form')
    </form>
</div>
@endsection
