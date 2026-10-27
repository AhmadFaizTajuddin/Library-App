<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryTest extends TestCase
{
    use RefreshDatabase;

    private int $isbn = 1000;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeBook(array $overrides = []): Book
    {
        $category = $overrides['category_id'] ?? Category::firstOrCreate(['name' => 'Test Category'])->id;
        unset($overrides['category_id']);

        return Book::create(array_merge([
            'category_id' => $category,
            'title' => 'Book '.$this->isbn,
            'author' => 'Some Author',
            'isbn' => (string) $this->isbn++,
            'year' => 2020,
            'stock' => 2,
        ], $overrides));
    }

    // ---------- Authentication ----------

    public function test_guest_can_browse_catalogue_but_not_dashboard(): void
    {
        $this->get(route('books.index'))->assertOk();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_user_can_register_and_is_always_a_member(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Member',
            'email' => 'new@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
            'role' => 'admin', // must be ignored
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'role' => 'member']);
    }

    public function test_login_fails_with_wrong_password_and_succeeds_with_correct_one(): void
    {
        $user = User::factory()->create(['password' => 'correct-pass']);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => $user->email, 'password' => 'correct-pass'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    // ---------- Authorization ----------

    public function test_member_cannot_reach_admin_pages(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('categories.index'))->assertForbidden();
        $this->actingAs($member)->get(route('loans.index'))->assertForbidden();
        $this->actingAs($member)->get(route('books.create'))->assertForbidden();
        $this->actingAs($member)->post(route('books.store'), [])->assertForbidden();
    }

    // ---------- Categories & books (CRUD) ----------

    public function test_admin_can_create_update_and_delete_a_category(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('categories.store'), ['name' => 'Poetry'])
            ->assertRedirect(route('categories.index'));
        $category = Category::where('name', 'Poetry')->firstOrFail();

        $this->actingAs($admin)->put(route('categories.update', $category), ['name' => 'Verse']);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Verse']);

        $this->actingAs($admin)->delete(route('categories.destroy', $category));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_books_cannot_be_deleted(): void
    {
        $book = $this->makeBook();

        $this->actingAs($this->admin())
            ->delete(route('categories.destroy', $book->category_id))
            ->assertSessionHasErrors('alert');

        $this->assertDatabaseHas('categories', ['id' => $book->category_id]);
    }

    public function test_admin_can_create_update_and_delete_a_book(): void
    {
        $admin = $this->admin();
        $category = Category::create(['name' => 'Fiction']);

        $this->actingAs($admin)->post(route('books.store'), [
            'category_id' => $category->id,
            'title' => 'Unique Test Title',
            'author' => 'A. Writer',
            'isbn' => '9781234567890',
            'year' => 2021,
            'stock' => 3,
        ])->assertRedirect(route('books.index'));

        $book = Book::where('isbn', '9781234567890')->firstOrFail();

        $this->actingAs($admin)->put(route('books.update', $book), [
            'category_id' => $category->id,
            'title' => 'Renamed Title',
            'author' => 'A. Writer',
            'isbn' => '9781234567890',
            'year' => 2021,
            'stock' => 5,
        ]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Renamed Title', 'stock' => 5]);

        $this->actingAs($admin)->delete(route('books.destroy', $book));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_book_validation_rejects_duplicate_isbn(): void
    {
        $existing = $this->makeBook(['isbn' => '555']);

        $this->actingAs($this->admin())->post(route('books.store'), [
            'category_id' => $existing->category_id,
            'title' => 'Another',
            'author' => 'X',
            'isbn' => '555',
            'year' => 2020,
            'stock' => 1,
        ])->assertSessionHasErrors('isbn');
    }

    public function test_search_filters_books_by_title(): void
    {
        $this->makeBook(['title' => 'Alpha Compendium']);
        $this->makeBook(['title' => 'Beta Digest']);

        $this->get(route('books.index', ['q' => 'Alpha']))
            ->assertSee('Alpha Compendium')
            ->assertDontSee('Beta Digest');
    }

    // ---------- Loans ----------

    public function test_borrowing_reduces_stock_and_sets_due_date(): void
    {
        $member = User::factory()->create();
        $book = $this->makeBook(['stock' => 2]);

        $this->actingAs($member)->post(route('loans.store'), ['book_id' => $book->id])
            ->assertRedirect(route('loans.mine'));

        $this->assertSame(1, $book->fresh()->stock);

        $loan = Loan::firstOrFail();
        $this->assertSame($member->id, $loan->user_id);
        $this->assertSame(today()->addDays(Loan::LOAN_DAYS)->toDateString(), $loan->due_at->toDateString());
        $this->assertNull($loan->returned_at);
    }

    public function test_cannot_borrow_out_of_stock_book(): void
    {
        $book = $this->makeBook(['stock' => 0]);

        $this->actingAs(User::factory()->create())
            ->post(route('loans.store'), ['book_id' => $book->id])
            ->assertSessionHasErrors('alert');

        $this->assertDatabaseCount('loans', 0);
    }

    public function test_cannot_borrow_same_book_twice_at_once(): void
    {
        $member = User::factory()->create();
        $book = $this->makeBook(['stock' => 5]);

        $this->actingAs($member)->post(route('loans.store'), ['book_id' => $book->id]);
        $this->actingAs($member)->post(route('loans.store'), ['book_id' => $book->id])
            ->assertSessionHasErrors('alert');

        $this->assertDatabaseCount('loans', 1);
        $this->assertSame(4, $book->fresh()->stock);
    }

    public function test_member_cannot_exceed_the_active_loan_limit(): void
    {
        $member = User::factory()->create();

        for ($i = 0; $i < Loan::MAX_ACTIVE; $i++) {
            $this->actingAs($member)->post(route('loans.store'), ['book_id' => $this->makeBook()->id]);
        }

        $this->actingAs($member)->post(route('loans.store'), ['book_id' => $this->makeBook()->id])
            ->assertSessionHasErrors('alert');

        $this->assertSame(Loan::MAX_ACTIVE, Loan::count());
    }

    public function test_returning_a_late_book_charges_a_fine_and_restores_stock(): void
    {
        $member = User::factory()->create();
        $book = $this->makeBook(['stock' => 1]);

        $loan = Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'borrowed_at' => today()->subDays(10),
            'due_at' => today()->subDays(3),
        ]);
        $book->decrement('stock');

        $this->actingAs($member)->patch(route('loans.return', $loan))->assertRedirect();

        $loan->refresh();
        $this->assertNotNull($loan->returned_at);
        $this->assertSame(3 * Loan::FINE_PER_DAY, $loan->fine);
        $this->assertSame(1, $book->fresh()->stock);
    }

    public function test_returning_on_time_has_no_fine(): void
    {
        $member = User::factory()->create();
        $book = $this->makeBook();
        $this->actingAs($member)->post(route('loans.store'), ['book_id' => $book->id]);

        $loan = Loan::firstOrFail();
        $this->actingAs($member)->patch(route('loans.return', $loan));

        $this->assertSame(0, $loan->fresh()->fine);
    }

    public function test_member_cannot_return_someone_elses_loan(): void
    {
        $owner = User::factory()->create();
        $book = $this->makeBook();
        $loan = Loan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'borrowed_at' => today(),
            'due_at' => today()->addDays(7),
        ]);

        $this->actingAs(User::factory()->create())
            ->patch(route('loans.return', $loan))
            ->assertForbidden();
    }

    public function test_dashboard_loads_for_admin_and_member(): void
    {
        $this->actingAs($this->admin())->get(route('dashboard'))->assertOk()->assertSee('Library overview');
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()->assertSee('On loan');
    }
}
