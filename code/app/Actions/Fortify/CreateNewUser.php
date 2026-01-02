<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'region_id' => ['required', 'integer'],
            'province_id' => ['required', 'integer'],
            'comuni_id' => ['required', 'integer'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'region_id' => $input['region_id'],
            'province_id' => $input['province_id'],
            'comuni_id' => $input['comuni_id'],
            'password' => Hash::make($input['password']),
        ]);

        $user->assignRole('supplier');
        
        return $user;
    }
}
