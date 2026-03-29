<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FollowStatus;
use App\Enums\ProfileStatus;
use App\Exceptions\FollowException;
use App\Http\Resources\UserPrivateProfileResource;
use App\Http\Resources\UserProfileResource;
use App\Models\Follower;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class UserProfileService
{
    /**
     * Create a new class instance.
     */
    private FollowService $followService;

    public function __construct(FollowService $followService)
    {
        $this->followService = $followService;
    }

    public function viewProfile($username)
    {
        $viewer = auth()->user();

        $user = $this->getUserByUsername($username);

        return $this->profileStatusChecker($user, $viewer);
    }

    public function follow(User $user, string $username): mixed
    {
        return DB::transaction(function () use ($user, $username): User {
            try {
                // getting user
                $userToFollow = $this->getUserByUsername($username)->load('stats');

                // validate if user is followed or not
                $this->followService->validateFollow($user, $userToFollow);

                // check if profile is private or public

                if ($userToFollow->profile->profile_status === ProfileStatus::PRIVATE->value) {
                    // Create a follow request
                    $this->followService->createFollowRequest($user, $userToFollow);
                } else {
                    // Directly follow if the profile is public
                    $user->following()->attach($userToFollow->id);
                    $user->stats->increment('following_count');
                    $userToFollow->stats->increment('followers_count');
                }
                DB::commit();

                return $userToFollow;
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });
    }

    public function unfollow(User $user, string $username): mixed
    {
        return DB::transaction(function () use ($user, $username): User {
            try {
                $userToUnFollow = $this->getUserByUsername($username);

                $this->followService->validateUnFollow($user, $userToUnFollow);
                $user->following()->detach($userToUnFollow->id);
                $user->stats->decrement('following_count');
                $userToUnFollow->stats->decrement('followers_count');

                DB::commit();

                return $userToUnFollow;
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });
    }

    public function accept(User $user, string $username): mixed
    {
        return DB::transaction(function () use ($user, $username): User {
            try {
                $userToAccept = $this->getUserByUsername($username);
                $followRequest = $this->followService->followRequestFinder($userToAccept, $user);

                if (! $followRequest) {
                    throw FollowException::followRequestNotFound();
                }
                $followRequest->update([
                    'status' => FollowStatus::ACCEPTED->value,
                ]);

                $user->following()->attach($userToAccept->id, [
                    'id' => Str::uuid(),
                    'status' => FollowStatus::ACCEPTED->value,
                ]);
                $user->stats->increment('following_count');
                $userToAccept->stats->increment('followers_count');
                DB::commit();

                return $userToAccept;
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });
    }

    public function decline($user, $username)
    {
        $followRequest = $this->followService->followRequestFinder($user, $username);

        if ($followRequest) {
            return $followRequest->delete();
        }

        return throw FollowException::followRequestNotFound();
    }

    public function getFollowRequestByUser(User $user): ?Follower
    {
        return Follower::where('user_id', $user->id)->first();
    }

    public function getPendingRequests(?User $user): Collection
    {
        return $this->followService->getPendingFollowRequests($user);
    }

    private function getUserByUsername($username)
    {
        return User::with(['profile', 'stats', 'followers', 'following', 'posts'])
            ->where('username', $username)
            ->firstOrFail();
    }

    private function profileStatusChecker($user, $viewer)
    {
        if ($user->profile->profile_status === ProfileStatus::PRIVATE->value) {
            if (! $viewer || $viewer->id !== $user->id) {
                if (! $viewer || ! $viewer->following()->where('user_id', $user->id)->exists()) {
                    return new UserPrivateProfileResource($user);
                }
            }
        }

        return new UserProfileResource($user);
    }
}
