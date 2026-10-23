<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * De lesdagen van een reservering. Een duo-pakket van 3 lessen heeft 3 regels in deze tabel.
     */
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            // Verdwijnt de reservering, dan verdwijnen de bijbehorende lessen mee.
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->restrictOnDelete();
            $table->date('lesson_date');
            $table->time('start_time')->default('10:00:00');
            $table->string('status')->default('gepland');    // gepland, geannuleerd
            $table->timestamps();

            // Een instructeur geeft maximaal één les per dag. De database dwingt dat zelf af.
            $table->unique(['instructor_id', 'lesson_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
