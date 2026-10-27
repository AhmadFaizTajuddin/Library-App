<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    public const LOAN_DAYS = 7;       // how long a book may be kept
    public const MAX_ACTIVE = 3;      // max books on loan per user
    public const FINE_PER_DAY = 1000; // Rupiah per late day

    protected $fillable = [
        'user_id',
        'book_id',
        'borrowed_at',
        'due_at',
        'returned_at',
        'fine',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'date:Y-m-d',
            'due_at' => 'date:Y-m-d',
            'returned_at' => 'date:Y-m-d',
            'fine' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** Loans that have not been returned yet. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('returned_at');
    }

    /** Active loans whose due date is already in the past. */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNull('returned_at')
            ->whereDate('due_at', '<', today()->toDateString());
    }

    public function isOverdue(): bool
    {
        return $this->returned_at === null && $this->due_at->lt(today());
    }

    /** Number of days past the due date (until returned, or until today). */
    public function lateDays(): int
    {
        $end = $this->returned_at ?? today();

        return $end->gt($this->due_at) ? (int) $this->due_at->diffInDays($end) : 0;
    }

    public function calculateFine(): int
    {
        return $this->lateDays() * self::FINE_PER_DAY;
    }

    /** Stored fine for returned loans, running fine for active ones. */
    public function currentFine(): int
    {
        return $this->returned_at ? $this->fine : $this->calculateFine();
    }
}
