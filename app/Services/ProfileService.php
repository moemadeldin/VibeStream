<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\PasswordException;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\UserDeletedNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

final class ProfileService
{
    public function updateProfile(array $data, User $user): User
    {
        if (isset($data['profile_picture'])) {
            $this->handleProfilePictureUpdate($user, $data['profile_picture']);
        }
        if (isset($data['mobile'])) {
            $user->update(['mobile' => $data['mobile']]);
        }
        $user->profile()->update(Arr::except($data, ['current_password', 'password', 'password_confirmation', 'profile_picture', 'mobile']));

        if (isset($data['current_password']) && isset($data['password'])) {
            $this->changePassword($user, $data);
            $user->notify(new PasswordChangedNotification(config('app.admin_email')));
        }

        return $user;
    }

    public function deleteUser($data, $user)
    {

        if (! Hash::check($data['password'], $user->password)) {
            throw PasswordException::incorrect();
        }

        $user->delete();

        $user->notify(new UserDeletedNotification(config('app.admin_email')));

        return $user;
    }

    /**
     * Create a new class instance.
     */
    private function handleProfilePictureUpdate($user, $profilePicture)
    {
        $profilePicturePath = $profilePicture->store('profile/pictures', 'public');

        if ($user->profile->profile_picture) {
            Storage::disk('public')->delete($user->profile->profile_picture);
        }

        $user->profile()->update(['profile_picture' => $profilePicturePath]);
    }

    private function changePassword($user, $data)
    {
        if ($data['current_password'] === $data['password']) {
            throw PasswordException::sameAsCurrent();
        }

        if (! Hash::check($data['current_password'], $user->password)) {
            throw PasswordException::incorrect();
        }
        $user->update(['password' => Hash::make($data['password'])]);

    }
}
