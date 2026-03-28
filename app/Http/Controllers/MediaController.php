<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DeleteMediaRequest;
use App\Http\Requests\MediaRequest;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use App\Util\APIResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

final class MediaController extends Controller
{
    use APIResponder;

    public function store(MediaRequest $request, User $user, Post $post): JsonResponse
    {
        $path = $request->file('media')->store('media/posts', 'public');

        $media = $post->media()->create([
            'path' => $path,
            'type' => $request->validated()['media_type'],
            'order' => 1,
        ]);

        return $this->successResponse($media, 'Media uploaded successfully!');
    }

    public function destroy(DeleteMediaRequest $request, User $user, Post $post, Media $media): JsonResponse
    {
        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return $this->successResponse(null, 'Media deleted successfully!');

    }
}
