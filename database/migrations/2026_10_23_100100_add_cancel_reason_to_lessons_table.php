<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * De reden van een annulering (casus 1e, 2e en 3g).
     * Bijvoorbeeld "Ziekte van de instructeur" of de reden die de klant zelf opgeeft.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('cancel_reason', 500)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('cancel_reason');
        });
    }
};