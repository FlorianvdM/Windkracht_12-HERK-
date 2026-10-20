<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Een reservering van een klant: pakket + locatie + status.
     * Status 'voorlopig' tot de eigenaar de betaling heeft gecontroleerd, daarna 'definitief' (casus 3j).
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();              // bijvoorbeeld WK12-1007
            // restrictOnDelete: een klant of pakket met reserveringen kan niet zomaar worden verwijderd.
            $table->foreignId('user_id')->constrained()->restrictOnDelete();       // de aanmelder (klant)
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('voorlopig')->index();
            $table->boolean('payment_reported')->default(false); // klant meldt: ik heb betaald (casus 2g)
            $table->unsignedInteger('total_cents');          // totaal op de rekening (aanmelder betaalt alles)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
