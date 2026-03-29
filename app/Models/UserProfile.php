<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

final class UserProfile extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'profile_picture',
        'gender',
        'bio',
        'website',
        'profile_status',
    ];

    protected $casts = [
        'gender' => Gender::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getProfilePictureUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->profile_picture);
    }

}
