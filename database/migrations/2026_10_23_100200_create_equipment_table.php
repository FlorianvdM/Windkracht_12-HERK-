<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Het lesmateriaal van de kitesurfschool: kites, boards en wetsuits.
     * Per soort en maat houden we bij hoeveel stuks er zijn en hoeveel er kapot zijn.
     */
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // bijvoorbeeld "Kite 9 m²"
            $table->string('type')->index();                 // kite, board, wetsuit of overig
            $table->string('size')->nullable();              // bijvoorbeeld "9 m²" of "M"
            $table->unsignedSmallInteger('quantity');        // totaal aantal stuks
            $table->unsignedSmallInteger('defect')->default(0); // hoeveel daarvan kapot zijn
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};