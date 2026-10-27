@extends('layouts.app')
@section('title', 'Add book')
@section('content')
<h1 class="h3 mb-3">Add a book</h1>
<div class="panel">
    <form method="POST" action="{{ route('books.store') }}" enctype="multipart/form-data">
        @include('books._form', ['book' => null])
    </form>
</div>
@endsection
