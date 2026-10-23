<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * De NAW-gegevens van de duo-lesser (casus 2d): naam, adres en woonplaats.
     * Nullable, want bij een privéles is er geen tweede persoon.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('duo_name')->nullable()->after('total_cents');
            $table->string('duo_address')->nullable()->after('duo_name');
            $table->string('duo_city')->nullable()->after('duo_address');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['duo_name', 'duo_address', 'duo_city']);
        });
    }
};