<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); //sunday is 0, monday is 1 .... 
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->unique(['business_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_availabilities');
    }
};
