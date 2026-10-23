<?php

namespace Tests\Feature;

use Database\Seeders\PackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * De algemene homepage is voor iedereen toegankelijk en toont de vier pakketten uit de database.
 */
class HomepageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // De React-bestanden hoeven in de tests niet gebouwd te zijn.
        $this->withoutVite();

        $this->seed(PackageSeeder::class);
    }

    public function test_guest_sees_homepage_with_four_packages(): void
    {
        // Geen inlog nodig: de homepage is de uitzondering op de beveiliging (casus 8).
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('welcome')
                ->has('packages', 4)
                // De pakketten staan gesorteerd op prijs per persoon, van laag naar hoog:
                // eerst de losse duo-kiteles (€ 135,-), als laatste het duo-pakket van 5 lessen (€ 675,-).
                ->where('packages.0.name', 'Losse duo-kiteles')
                ->where('packages.0.price_cents', 13500)
                ->where('packages.3.name', 'Duo-pakket 5 lessen')
                ->where('packages.3.price_cents', 67500));
    }
}