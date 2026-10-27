@extends('layouts.app')
@section('title', 'Add category')
@section('content')
<h1 class="h3 mb-3">Add a category</h1>
<div class="panel" style="max-width: 520px">
    <form method="POST" action="{{ route('categories.store') }}">
        @csrf
        <x-field name="name" label="Category name" required autofocus />
        <button type="submit" class="btn btn-primary">Add category</button>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
