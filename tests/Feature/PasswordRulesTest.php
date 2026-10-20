<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * De wachtwoordregels uit de casus: minimaal 12 tekens, een hoofdletter, een cijfer en een leesteken.
 */
class PasswordRulesTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{0: string}> */
    public static function zwakkeWachtwoorden(): array
    {
        return [
            'te kort (11 tekens)' => ['Kort-12!abc'],
            'geen hoofdletter' => ['geenhoofdletter-12!'],
            'geen cijfer' => ['GeenCijferHier-!!'],
            'geen leesteken' => ['GeenLeesteken1234'],
        ];
    }

    #[DataProvider('zwakkeWachtwoorden')]
    public function test_weak_passwords_are_rejected(string $wachtwoord): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => $wachtwoord,
            'password_confirmation' => $wachtwoord,
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_password_with_12_characters_uppercase_number_and_symbol_is_accepted(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Windkracht-12!Test',
            'password_confirmation' => 'Windkracht-12!Test',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }
}
