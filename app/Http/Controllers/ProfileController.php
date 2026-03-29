<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\PasswordValidationRequest;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Services\ProfileService;
use App\Util\APIResponder;
use Illuminate\Http\JsonResponse;

final class ProfileController extends Controller
{
    use APIResponder;

    private ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function index(): JsonResponse
    {
        return $this->successResponse(new ProfileResource(auth()->user()->load('profile')), 'Your Profile');
    }

    public function update(ProfileRequest $request): JsonResponse
    {

        return $this->successResponse(
            $this->profileService->updateProfile($request->validated(), auth()->user()),
            'Profile updated successfully!'
        );
    }

    public function destroy(PasswordValidationRequest $request): JsonResponse
    {

        return $this->successResponse(
            $this->profileService->deleteUser($request->validated(), auth()->user()),
            'Your account has been deleted successfully!'
        );
    }
}
