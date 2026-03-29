<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\StoryViewerResource;
use App\Models\Story;
use App\Util\APIResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class StoryViewerController extends Controller
{
    use APIResponder;

    public function index(Story $story): JsonResponse
    {
        $user = auth()->user();

        if ($story->user_id !== $user->id) {
            return $this->failedResponse('You can only see viewers of your own stories', 403);
        }

        $viewers = $story->viewers()
            ->with('profile', 'profile')
            ->withPivot('created_at')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return $this->successResponse(
            StoryViewerResource::collection($viewers),
            'Story viewers'
        );
    }

    public function store(Story $story): JsonResponse
    {
        $user = auth()->user();

        DB::transaction(function () use ($story, $user): void {

            if ($story->user_id === $user->id || $story->viewers()->where('user_id', $user->id)->exists()) {
                return;
            }

            $story->viewers()->attach($user->id);
            $story->increment('viewers_count');
        });

        return $this->successResponse($story, 'Story viewed');
    }
}
