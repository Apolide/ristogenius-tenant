<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $attributes = [
            'password' => Hash::make($input['password']),
        ];

        if ($user->invited_at && ! $user->activated_at) {
            $attributes['activated_at'] = now();
            $attributes['email_verified_at'] = $user->email_verified_at ?? now();
            $attributes['enabled'] = true;
        }

        $user->forceFill($attributes)->save();
    }
}
