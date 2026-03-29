<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SearchUserRequest;
use App\Http\Resources\SearchResultResource;
use App\Models\User;
use App\Util\APIResponder;
use Illuminate\Http\JsonResponse;

final class SearchController extends Controller
{
    use APIResponder;

    public function __invoke(SearchUserRequest $request): JsonResponse
    {
        $users = User::with('profile')
            ->search($request->validated('query'))
            ->get();

        return $this->successResponse(
            SearchResultResource::collection($users),
            'Search results'
        );
    }
}
