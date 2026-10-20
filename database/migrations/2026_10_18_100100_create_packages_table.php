<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * De vier lespakketten van de kitesurfschool.
     * Bedragen staan in eurocenten (17500 = € 175,00), zodat er geen afrondfouten ontstaan.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('price_cents');          // prijs per persoon
            $table->decimal('hours', 4, 1);                  // totaal aantal lesuren, bijvoorbeeld 10,5
            $table->unsignedTinyInteger('lesson_days');      // aantal dagdelen (lesdagen) in het pakket
            $table->unsignedTinyInteger('max_persons');      // 1 bij privéles, 2 bij duo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
