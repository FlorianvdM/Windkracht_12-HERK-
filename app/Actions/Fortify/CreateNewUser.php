<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        // De rol vullen we hier bewust niet in: de database geeft elke nieuwe gebruiker de rol 'klant'.
        // Zo kan niemand zichzelf via het registratieformulier eigenaar maken.
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        // In het ontwerp krijgt de klant een activatielink per e-mail. Dat is een Won't have
        // (geen echte e-mails in het prototype), dus het account is meteen geactiveerd.
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }
}
