<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'author',
        'isbn',
        'year',
        'stock',
        'cover',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'stock' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function coverUrl(): ?string
    {
        return $this->cover ? asset('storage/'.$this->cover) : null;
    }

    /** A muted colour per category, used for the book "spine" in the UI. */
    public function spineColor(): string
    {
        return 'hsl('.(($this->category_id * 47) % 360).' 42% 30%)';
    }
}
