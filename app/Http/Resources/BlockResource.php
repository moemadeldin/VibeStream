<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class BlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'username' => $this->blockedUser->username,
            'full_name' => $this->blockedUser->full_name,
            'profile_picture' => $this->blockedUser->profile?->profile_picture,
            'blocked_at' => $this->created_at,
        ];
    }
}
