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
    Schema::table('businesses', function (Blueprint $table) {
        $table->unsignedSmallInteger('booking_window_days')
            ->default(30)
            ->after('timezone');
    });
}

public function down(): void
{
    Schema::table('businesses', function (Blueprint $table) {
        $table->dropColumn('booking_window_days');
    });
}
};
