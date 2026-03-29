<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\BlockResource;
use App\Http\Resources\UserProfileResource;
use App\Models\Block;
use App\Models\User;
use App\Util\APIResponder;
use Illuminate\Http\JsonResponse;

final class BlockController extends Controller
{
    use APIResponder;

    public function index(int $limit = 10): JsonResponse
    {
        
        $user = auth()->user();

        $blockedUsers = Block::where('user_id', $user->id)
            ->with('blockedUser.profile')
            ->simplePaginate($limit);

        return $this->successResponse(
            BlockResource::collection($blockedUsers),
            'Blocked users'
        );
    }

    public function store(User $user): JsonResponse
    {
        $authUser = auth()->user();

        if ($authUser->id === $user->id) {
            return $this->failedResponse('You cannot block yourself');
        }

        $alreadyBlocked = Block::where('user_id', $authUser->id)
            ->where('blocked_user_id', $user->id)
            ->exists();

        if ($alreadyBlocked) {
            return $this->failedResponse('User already blocked');
        }

        Block::create([
            'user_id' => $authUser->id,
            'blocked_user_id' => $user->id,
        ]);

        return $this->successResponse($user, 'User blocked successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        $authUser = auth()->user();

        $block = Block::where('user_id', $authUser->id)
            ->where('blocked_user_id', $user->id)
            ->first();

        if (! $block) {
            return $this->failedResponse('User is not blocked');
        }

        $block->delete();

        return $this->successResponse(null, 'User unblocked successfully');
    }
}
