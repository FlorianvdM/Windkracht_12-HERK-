<?php

use App\Http\Controllers\Klant\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Algemene homepage: voor iedereen toegankelijk.
Route::inertia('/', 'welcome')->name('home');

// Vanaf hier moet je ingelogd zijn.
Route::middleware(['auth', 'verified'])->group(function () {
    // /dashboard blijft bestaan, want Fortify stuurt iedereen hiernaartoe na het inloggen of registreren.
    // Wij sturen door naar de eigen homepage van de gebruikersrol.
    Route::get('dashboard', function (Request $request) {
        return redirect()->route($request->user()->role->homeRoute());
    })->name('dashboard');

    // Pagina's voor klanten.
    Route::middleware('role:klant')->prefix('klant')->name('klant.')->group(function () {
        Route::inertia('/', 'klant/home')->name('home');

        // Reserveren: formulier tonen en opslaan.
        Route::get('reserveren', [ReservationController::class, 'create'])->name('reservations.create');
        Route::post('reserveren', [ReservationController::class, 'store'])->name('reservations.store');

        // Eigen reserveringen: overzicht en bevestiging van één reservering.
        Route::get('reserveringen', [ReservationController::class, 'index'])->name('reservations.index');
        Route::get('reserveringen/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    });

    // Pagina's voor instructeurs.
    Route::middleware('role:instructeur')->prefix('instructeur')->name('instructeur.')->group(function () {
        Route::inertia('/', 'instructeur/home')->name('home');
    });

    // Pagina's voor de eigenaar.
    Route::middleware('role:eigenaar')->prefix('eigenaar')->name('eigenaar.')->group(function () {
        Route::inertia('/', 'eigenaar/home')->name('home');
    });
});

require __DIR__.'/settings.php';
