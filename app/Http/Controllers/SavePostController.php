<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\SavedPost;
use App\Util\APIResponder;
use Illuminate\Http\JsonResponse;

final class SavePostController extends Controller
{
    use APIResponder;

    public function index(int $limit = 10): JsonResponse
    {
        $user = auth()->user();

        $posts = $user->savedPosts()
            ->with(['user', 'collaborators', 'tags', 'comments.user', 'comments.replies.user', 'comments.likes', 'comments.replies.likes', 'likes.user'])
            ->orderBy('saved_posts.created_at', 'desc')
            ->simplePaginate($limit);

        return $this->successResponse(PostResource::collection($posts), 'Saved posts');
    }

    public function store(Post $post): JsonResponse
    {
        $user = auth()->user();

        $alreadySaved = SavedPost::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->exists();

        if ($alreadySaved) {
            return $this->failedResponse('Post already saved');
        }

        SavedPost::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        return $this->successResponse($post, 'Post saved successfully');
    }

    public function destroy(Post $post): JsonResponse
    {
        $user = auth()->user();

        $saved = SavedPost::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if (! $saved) {
            return $this->failedResponse('Post not saved');
        }

        $saved->delete();

        return $this->successResponse(null, 'Post unsaved successfully');
    }
}
