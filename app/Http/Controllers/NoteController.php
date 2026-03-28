<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use App\Models\User;
use App\Util\APIResponder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class NoteController extends Controller
{
    use APIResponder;

    /**
     * Display a listing of the resource.
     */
    public function index(User $user): JsonResponse
    {
        $user = User::where('username', $user->username)->firstOrFail();

        $notes = $user->note()->get();

        return $this->successResponse(NoteResource::collection($notes), 'Note');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateNoteRequest $request, User $user): JsonResponse
    {
        $note = $user->note()->create([
            'content' => $request->safe()->content,
            'expires_at' => Carbon::now()->addHours(User::DEFAULT_EXPIRE_DATE),
        ]);

        return $this->successResponse($note, 'Note has been added successfully!');
    }

    public function update(CreateNoteRequest $request, User $user, Note $note): JsonResponse
    {
        $note->update($request->validated());

        return $this->successResponse($note, 'Note has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Note $note): JsonResponse
    {
        $note->delete();

        return $this->successResponse($note, 'Note has been deleted');
    }
}
