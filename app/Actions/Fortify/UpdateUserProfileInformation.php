<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'mobile' => ['required', 'string', 'max:30'],
            'lecturer_headline' => ['nullable', 'string', 'max:120'],
            'lecturer_specialties' => ['nullable', 'string', 'max:255'],
            'lecturer_bio' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill($this->profilePayload($user, $input))->save();
        }
    }

    protected function updateVerifiedUser(User $user, array $input): void
    {
        $payload = $this->profilePayload($user, $input);
        $payload['email_verified_at'] = null;

        $user->forceFill($payload)->save();

        $user->sendEmailVerificationNotification();
    }

    protected function profilePayload(User $user, array $input): array
    {
        $payload = [
            'name' => $input['name'],
            'email' => $input['email'],
            'mobile' => $input['mobile'],
        ];

        if ($user->hasAnyRole(['admin', 'lecturer'])) {
            $payload['lecturer_headline'] = $input['lecturer_headline'] ?? null;
            $payload['lecturer_specialties'] = $input['lecturer_specialties'] ?? null;
            $payload['lecturer_bio'] = $input['lecturer_bio'] ?? null;
        }

        return $payload;
    }
}
