<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
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
        $validationRules = [
            'name' => ['required', 'string', 'max:255', 'unique:users'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:1'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'password_confirmation' => ['required', 'string', 'same:password'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ];
        

        // Add validation for profile photo if present
        if (isset($input['profile_photo_path'])) {
            $validationRules['profile_photo_path'] = ['nullable', 'image', 'max:2048']; // 2MB max
        }

        // Add validation for cover photo if present
        if (isset($input['cover_photo'])) {
            $validationRules['cover_photo'] = ['nullable', 'image', 'max:5120']; // 5MB max
        }

        Validator::make($input, $validationRules)->validate();

        // Handle file uploads
        $profilePhotoPath = null;
        if (isset($input['profile_photo_path'])) {
            $profilePhotoPath = $input['profile_photo_path']->store('profile-photos', 'public');
        }

        $coverPhotoPath = null;
        if (isset($input['cover_photo'])) {
            $coverPhotoPath = $input['cover_photo']->store('cover-photos', 'public');
        }

        return User::create([
            'name' => $input['name'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'middle_initial' => $input['middle_initial'] ?? null,
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'profile_photo_path' => $profilePhotoPath,
            'cover_photo' => $coverPhotoPath,
        ]);
    }
}