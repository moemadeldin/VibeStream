<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserStatus;
use App\Events\UserVerificationRequested;
use App\Exceptions\PasswordException;
use App\Exceptions\UserStatusException;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Repositories\AuthRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class AuthService
{
    public function __construct(
        private readonly AuthRepository $authRepository,
        private readonly TokenManager $tokenManager
    ) {}

    public function register(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            try {

                $user = $this->authRepository->create($data);

                $user->profile()->create([
                    'profile_picture' => 'profile/pictures/default.png',
                ]);
                $user->stats()->create();

                event(new UserVerificationRequested($user));

                return $this->tokenManager->respondWithUserAndToken($user);

            } catch (Exception $e) {
                throw $e;
            }
        });
    }

    public function verify(array $data): array
    {
        $user = $this->authRepository->findUserByEmailOrMobileWithCode($data);

        $user->update([
            'status' => UserStatus::ACTIVE->value,
            'verification_code' => null,
        ]);

        return $this->tokenManager->respondWithUserAndToken($user);
    }

    public function login(array $data): array
    {
        return $this->tokenManager->respondWithUserAndToken(
            $this->validateUserCredentials(
                $data
            )
        );
    }

    public function logout(?User $user): void
    {
        $this->tokenManager->deleteAccessToken($user);
    }

    public function forgetPassword(array $data): array
    {
        $user = $this->authRepository->findUserByEmailOrMobileOrUsername($data);

        event(new UserVerificationRequested($user));

        return [
            'user' => $user,
            'token' => $this->tokenManager->generateAccessToken($user),
        ];
    }

    public function checkOTP(array $data): array
    {
        return $this->tokenManager->respondWithUserAndToken(
            $this->authRepository->findUserByEmailOrMobileWithCode(
                $data)
        );
    }

    public function resetPassword(array $data, User $user): array
    {

        if (Hash::check($data['password'], $user->password)) {
            throw PasswordException::sameAsCurrent();
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        $this->tokenManager->deleteAccessToken($user);

        $user->notify(new PasswordChangedNotification(config('app.admin_email')));

        return [
            'user' => $user,
        ];
    }

    private function validateUserCredentials(array $data): User
    {

        $user = $this->authRepository->findUserByEmailOrMobileOrUsername($data);

        $this->validatePassword($user, $data);

        $this->ensureUserIsActive($user);

        return $user;
    }

    private function validatePassword(User $user, array $data): void
    {
        if (! Hash::check($data['password'], $user->password)) {
            throw PasswordException::incorrect();
        }
    }

    private function ensureUserIsActive(User $user): void
    {
        if ($user->status !== UserStatus::ACTIVE->value) {
            throw UserStatusException::notActiveOrBlocked();
        }
    }
}
