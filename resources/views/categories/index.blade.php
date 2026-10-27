@extends('layouts.app')
@section('title', 'Categories')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Categories</h1>
    <a href="{{ route('categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add category</a>
</div>

<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Name</th><th>Books</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($categories as $category)
            <tr>
                <td>{{ $category->name }}</td>
                <td>{{ $category->books_count }}</td>
                <td class="text-end">
                    <div class="d-inline-flex gap-2">
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-body-secondary">No categories yet. Add the first one to start building the catalogue.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $categories->links() }}
@endsection
