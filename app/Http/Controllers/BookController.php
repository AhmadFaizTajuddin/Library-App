<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookController extends Controller implements HasMiddleware
{
    /** Browsing is public; everything that changes data needs an admin. */
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['index', 'show']),
            new Middleware('admin', except: ['index', 'show']),
        ];
    }

    public function index(Request $request): View
    {
        $books = Book::with('category')
            ->when($request->query('q'), function ($query, $term) {
                // whereLike is case-insensitive on MySQL, SQLite and PostgreSQL (ILIKE).
                $query->where(function ($w) use ($term) {
                    $w->whereLike('title', "%{$term}%")
                        ->orWhereLike('author', "%{$term}%");
                });
            })
            ->when($request->query('category'), fn ($query, $id) => $query->where('category_id', $id))
            ->when($request->boolean('available'), fn ($query) => $query->where('stock', '>', 0))
            ->orderBy('title')
            ->paginate(8)
            ->withQueryString();

        return view('books.index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(Book $book): View
    {
        $book->load('category');

        $activeLoan = auth()->check()
            ? auth()->user()->loans()->active()->where('book_id', $book->id)->first()
            : null;

        return view('books.show', compact('book', 'activeLoan'));
    }

    public function create(): View
    {
        return view('books.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        unset($data['cover']);
        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        Book::create($data);

        return redirect()->route('books.index')->with('success', 'Book added to the catalogue.');
    }

    public function edit(Book $book): View
    {
        return view('books.edit', [
            'book' => $book,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate($this->rules($book));

        unset($data['cover']);
        if ($request->hasFile('cover')) {
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $book->update($data);

        return redirect()->route('books.show', $book)->with('success', 'Book updated.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        if ($book->loans()->exists()) {
            return back()->withErrors([
                'alert' => 'This book has loan records and cannot be deleted. Set its stock to 0 instead.',
            ]);
        }

        if ($book->cover) {
            Storage::disk('public')->delete($book->cover);
        }
        $book->delete();

        return redirect()->route('books.index')->with('success', 'Book deleted.');
    }

    private function rules(?Book $book = null): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'author' => ['required', 'string', 'max:150'],
            'isbn' => ['required', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book?->id)],
            'year' => ['required', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'stock' => ['required', 'integer', 'min:0', 'max:1000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
