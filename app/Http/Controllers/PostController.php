<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\DeletePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Util\APIResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class PostController extends Controller
{
    use APIResponder;

    public function index(User $user): JsonResponse
    {
        $user = User::where('username', $user->username)->firstOrFail();

        $posts = $user->posts()
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse(PostResource::collection($posts), 'Posts');
    }

    public function store(CreatePostRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request): JsonResponse {
            $user = auth()->user();

            $post = $user->posts()->create($request->validated());

            $user->stats()->increment('posts_count');

            return $this->successResponse($post, 'Post created successfully');
        });

    }

    public function update(UpdatePostRequest $request, User $user, Post $post): JsonResponse
    {
        $user = User::where('username', $user->username)->firstOrFail();

        $post->update($request->validated());

        return $this->successResponse($post, 'Post updated successfully!');
    }

    public function destroy(DeletePostRequest $request, User $user, Post $post): JsonResponse
    {
        return DB::transaction(function () use ($request, $user, $post): JsonResponse {
            $user = User::where('username', $user->username)->firstOrFail();

            $post->delete();

            $user->stats()->decrement('posts_count');

            return $this->successResponse($post, 'Post deleted successfully!');
        });

    }
}
