<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'title', 'slug', 'body'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);

        $existing = static::query()
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where('slug', 'like', $slug.'%')
            ->pluck('slug')
            ->flip();

        if (! $existing->has($slug)) {
            return $slug;
        }

        $suffix = 2;

        while ($existing->has($slug.'-'.$suffix)) {
            $suffix++;
        }

        return $slug.'-'.$suffix;
    }
}
