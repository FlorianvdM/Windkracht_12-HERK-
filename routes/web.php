<?php

use App\Http\Controllers\Eigenaar\ReservationController as EigenaarReservationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Instructeur\LessonController as InstructeurLessonController;
use App\Http\Controllers\Klant\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Algemene homepage: voor iedereen toegankelijk. Via een controller, zodat de pakketten uit de database komen.
Route::get('/', [HomeController::class, 'index'])->name('home');

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

        // De klant meldt dat hij het bedrag heeft betaald (casus 2g).
        Route::post('reserveringen/{reservation}/betaald', [ReservationController::class, 'reportPayment'])->name('reservations.report-payment');
    });

    // Pagina's voor instructeurs.
    Route::middleware('role:instructeur')->prefix('instructeur')->name('instructeur.')->group(function () {
        Route::inertia('/', 'instructeur/home')->name('home');

        // NIEUW: dag-, week- en maandoverzicht van de eigen lessen (casus 1f).
        // De weergave en datum staan in de adresbalk: ?weergave=week&datum=2026-10-22
        Route::get('lessen', [InstructeurLessonController::class, 'index'])->name('lessons.index');
    });

    // Pagina's voor de eigenaar.
    Route::middleware('role:eigenaar')->prefix('eigenaar')->name('eigenaar.')->group(function () {
        Route::inertia('/', 'eigenaar/home')->name('home');

        // Overzicht van alle reserveringen. Met ?filter=onbetaald zie je alleen de voorlopige.
        Route::get('reserveringen', [EigenaarReservationController::class, 'index'])->name('reservations.index');

        // Een reservering definitief maken: de pagina tonen en de status aanpassen.
        Route::get('reserveringen/{reservation}', [EigenaarReservationController::class, 'show'])->name('reservations.show');
        Route::post('reserveringen/{reservation}/definitief', [EigenaarReservationController::class, 'confirm'])->name('reservations.confirm');
    });
});

require __DIR__.'/settings.php';