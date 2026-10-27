<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $stats = [
                'Titles' => Book::count(),
                'Copies in stock' => (int) Book::sum('stock'),
                'Categories' => Category::count(),
                'Members' => User::where('role', 'member')->count(),
                'On loan' => Loan::active()->count(),
                'Overdue' => Loan::overdue()->count(),
                'Returned' => Loan::whereNotNull('returned_at')->count(),
                'Fines recorded (Rp)' => number_format((int) Loan::sum('fine'), 0, ',', '.'),
            ];

            return view('dashboard', [
                'stats' => $stats,
                'recentLoans' => Loan::with(['user', 'book'])->latest('id')->take(8)->get(),
                'popularBooks' => Book::has('loans')->withCount('loans')->orderByDesc('loans_count')->take(5)->get(),
            ]);
        }

        $loans = $user->loans()->with('book')->latest('id')->get();

        return view('dashboard', [
            'stats' => [
                'On loan' => $loans->whereNull('returned_at')->count(),
                'Overdue' => $loans->filter->isOverdue()->count(),
                'Books borrowed in total' => $loans->count(),
                'Late fines (Rp)' => number_format($loans->sum(fn (Loan $l) => $l->currentFine()), 0, ',', '.'),
            ],
            'activeLoans' => $loans->whereNull('returned_at'),
        ]);
    }
}
