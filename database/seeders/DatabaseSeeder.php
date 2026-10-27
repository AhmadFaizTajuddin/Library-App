<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data. ISBNs below are sample numbers for demonstration only.
     * Accounts: admin@library.test / member@library.test (password: "password").
     */
    public function run(): void
    {
        // Safe to call on every deploy: do nothing if the demo data already exists.
        if (User::query()->exists()) {
            return;
        }

        User::create(['name' => 'Library Admin', 'email' => 'admin@library.test', 'password' => 'password', 'role' => 'admin']);
        $member = User::create(['name' => 'Sari Member', 'email' => 'member@library.test', 'password' => 'password', 'role' => 'member']);
        $budi = User::create(['name' => 'Budi Santoso', 'email' => 'budi@library.test', 'password' => 'password', 'role' => 'member']);

        $categories = [];
        foreach (['Fiction', 'Science & Technology', 'History', 'Programming', 'Business', 'Self Development'] as $name) {
            $categories[$name] = Category::create(['name' => $name]);
        }

        $books = [
            ['Clean Code', 'Robert C. Martin', 2008, 4, 'Programming', 'A handbook of agile software craftsmanship.'],
            ['The Pragmatic Programmer', 'Andrew Hunt and David Thomas', 1999, 3, 'Programming', 'Practical advice for working programmers.'],
            ['Refactoring', 'Martin Fowler', 1999, 2, 'Programming', 'Improving the design of existing code.'],
            ['A Brief History of Time', 'Stephen Hawking', 1988, 3, 'Science & Technology', 'From the Big Bang to black holes.'],
            ['Sapiens', 'Yuval Noah Harari', 2011, 5, 'History', 'A brief history of humankind.'],
            ['Guns, Germs, and Steel', 'Jared Diamond', 1997, 2, 'History', 'The fates of human societies.'],
            ['Laskar Pelangi', 'Andrea Hirata', 2005, 4, 'Fiction', 'A story of ten schoolchildren on Belitung island.'],
            ['Bumi Manusia', 'Pramoedya Ananta Toer', 1980, 3, 'Fiction', 'The first novel of the Buru Quartet.'],
            ['The Lean Startup', 'Eric Ries', 2011, 3, 'Business', 'How constant innovation creates successful businesses.'],
            ['Good to Great', 'Jim Collins', 2001, 2, 'Business', 'Why some companies make the leap and others do not.'],
            ['Atomic Habits', 'James Clear', 2018, 6, 'Self Development', 'Tiny changes, remarkable results.'],
            ['Deep Work', 'Cal Newport', 2016, 1, 'Self Development', 'Rules for focused success in a distracted world.'],
        ];

        $created = [];
        foreach ($books as $i => [$title, $author, $year, $stock, $category, $description]) {
            $created[$title] = Book::create([
                'category_id' => $categories[$category]->id,
                'title' => $title,
                'author' => $author,
                'isbn' => '97800000000'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'year' => $year,
                'stock' => $stock,
                'description' => $description,
            ]);
        }

        // A few loans so the dashboard and loan pages have something to show.
        $this->loan($member, $created['Clean Code'], borrowedDaysAgo: 3);                      // on loan
        $this->loan($member, $created['Sapiens'], borrowedDaysAgo: 12);                        // overdue
        $this->loan($budi, $created['Atomic Habits'], borrowedDaysAgo: 20, returnedDaysAgo: 8); // returned late
        $this->loan($budi, $created['Deep Work'], borrowedDaysAgo: 2);                         // on loan
    }

    private function loan(User $user, Book $book, int $borrowedDaysAgo, ?int $returnedDaysAgo = null): void
    {
        $loan = new Loan([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrowed_at' => today()->subDays($borrowedDaysAgo),
            'due_at' => today()->subDays($borrowedDaysAgo)->addDays(Loan::LOAN_DAYS),
        ]);

        if ($returnedDaysAgo === null) {
            $book->decrement('stock');
        } else {
            $loan->returned_at = today()->subDays($returnedDaysAgo);
            $loan->fine = $loan->calculateFine();
        }

        $loan->save();
    }
}
