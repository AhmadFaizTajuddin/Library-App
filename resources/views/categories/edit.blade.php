@extends('layouts.app')
@section('title', 'Edit category')
@section('content')
<h1 class="h3 mb-3">Edit category</h1>
<div class="panel" style="max-width: 520px">
    <form method="POST" action="{{ route('categories.update', $category) }}">
        @csrf @method('PUT')
        <x-field name="name" label="Category name" :value="$category->name" required autofocus />
        <button type="submit" class="btn btn-primary">Save changes</button>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
