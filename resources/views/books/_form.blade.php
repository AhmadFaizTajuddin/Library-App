@csrf
@if($categories->isEmpty())
    <div class="alert alert-warning">There are no categories yet. <a href="{{ route('categories.create') }}">Add a category</a> before adding books.</div>
@endif

<div class="row">
    <div class="col-md-8"><x-field name="title" label="Title" :value="$book?->title" required /></div>
    <div class="col-md-4"><x-field name="isbn" label="ISBN" :value="$book?->isbn" required /></div>
</div>
<div class="row">
    <div class="col-md-6"><x-field name="author" label="Author" :value="$book?->author" required /></div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">Choose a category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $book?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6"><x-field name="year" label="Publication year" type="number" :value="$book?->year" min="1900" required /></div>
    <div class="col-md-6"><x-field name="stock" label="Copies in stock" type="number" :value="$book?->stock ?? 1" min="0" required /></div>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description (optional)</label>
    <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $book?->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-4">
    <label for="cover" class="form-label">Cover image (optional, up to 2 MB)</label>
    @if($book?->coverUrl())
        <div class="mb-2"><img src="{{ $book->coverUrl() }}" alt="Current cover" class="cover"></div>
    @endif
    <input type="file" id="cover" name="cover" accept="image/*" class="form-control @error('cover') is-invalid @enderror">
    @error('cover')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<button type="submit" class="btn btn-primary">{{ $book ? 'Save changes' : 'Add book' }}</button>
<a href="{{ $book ? route('books.show', $book) : route('books.index') }}" class="btn btn-outline-secondary">Cancel</a>
