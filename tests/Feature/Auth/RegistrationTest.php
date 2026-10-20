<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        // Dit wachtwoord voldoet aan de regels: 12 tekens, hoofdletter, cijfer en leesteken.
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Windkracht-12!Test',
            'password_confirmation' => 'Windkracht-12!Test',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_users_are_customers_and_activated_directly()
    {
        $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Windkracht-12!Test',
            'password_confirmation' => 'Windkracht-12!Test',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();

        // Casus: de standaardrol na registreren is klant.
        $this->assertSame(Role::Klant, $user->role);
        // Prototype: geen activatiemail, het account is meteen actief.
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_role_in_registration_form_is_ignored()
    {
        // Iemand probeert zichzelf eigenaar te maken via het formulier. Dat mag niet lukken.
        $this->post(route('register.store'), [
            'name' => 'Sluwe Sjaak',
            'email' => 'sjaak@example.com',
            'password' => 'Windkracht-12!Test',
            'password_confirmation' => 'Windkracht-12!Test',
            'role' => 'eigenaar',
        ]);

        $this->assertSame(Role::Klant, User::where('email', 'sjaak@example.com')->firstOrFail()->role);
    }
}
