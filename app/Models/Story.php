<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

final class Story extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'media_id',
        'content',
        'viewers_count',
        'likes_count',
        'expires_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likable');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'story_viewers', 'story_id', 'user_id')->withTimestamps();
    }
}
