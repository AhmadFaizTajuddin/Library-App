<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoanController extends Controller
{
    /** Admin: every loan, with a status filter. */
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $loans = Loan::with(['user', 'book'])
            ->when($status === 'active', fn ($q) => $q->active())
            ->when($status === 'overdue', fn ($q) => $q->overdue())
            ->when($status === 'returned', fn ($q) => $q->whereNotNull('returned_at'))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('loans.index', compact('loans', 'status'));
    }

    /** Any user: their own loan history. */
    public function mine(Request $request): View
    {
        $loans = $request->user()->loans()->with('book')->latest('id')->paginate(10);

        return view('loans.mine', compact('loans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['book_id' => ['required', 'exists:books,id']]);

        $user = $request->user();

        DB::transaction(function () use ($request, $user) {
            // Lock the row so two people cannot borrow the last copy at the same time.
            $book = Book::lockForUpdate()->findOrFail($request->integer('book_id'));

            if ($book->stock < 1) {
                throw ValidationException::withMessages(['alert' => 'This book is out of stock.']);
            }
            if ($user->loans()->active()->where('book_id', $book->id)->exists()) {
                throw ValidationException::withMessages(['alert' => 'You already have this book on loan.']);
            }
            if ($user->loans()->active()->count() >= Loan::MAX_ACTIVE) {
                throw ValidationException::withMessages([
                    'alert' => 'You can borrow at most '.Loan::MAX_ACTIVE.' books at once. Return one first.',
                ]);
            }

            $book->decrement('stock');

            Loan::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'borrowed_at' => today(),
                'due_at' => today()->addDays(Loan::LOAN_DAYS),
            ]);
        });

        return redirect()->route('loans.mine')->with(
            'success',
            'Book borrowed. Please return it by '.today()->addDays(Loan::LOAN_DAYS)->format('d M Y').'.'
        );
    }

    public function returnBook(Request $request, Loan $loan): RedirectResponse
    {
        abort_unless($request->user()->isAdmin() || $loan->user_id === $request->user()->id, 403);

        $fine = 0;

        DB::transaction(function () use ($loan, &$fine) {
            $loan = Loan::lockForUpdate()->findOrFail($loan->id);

            if ($loan->returned_at) {
                throw ValidationException::withMessages(['alert' => 'This loan has already been returned.']);
            }

            $fine = $loan->calculateFine();
            $loan->update(['returned_at' => today(), 'fine' => $fine]);
            Book::whereKey($loan->book_id)->increment('stock');
        });

        $message = 'Book returned.';
        if ($fine > 0) {
            $message .= ' Late fine: Rp '.number_format($fine, 0, ',', '.').'.';
        }

        return back()->with('success', $message);
    }

    /** Admin: remove a loan record (restores the stock if the book was never returned). */
    public function destroy(Loan $loan): RedirectResponse
    {
        DB::transaction(function () use ($loan) {
            if ($loan->returned_at === null) {
                Book::whereKey($loan->book_id)->increment('stock');
            }
            $loan->delete();
        });

        return back()->with('success', 'Loan record deleted.');
    }
}
