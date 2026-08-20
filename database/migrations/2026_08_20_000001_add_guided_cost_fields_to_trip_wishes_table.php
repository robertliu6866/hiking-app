<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_wishes', function (Blueprint $table) {
            $table->unsignedTinyInteger('guided_days')->nullable()->after('homepage_group');
            $table->unsignedTinyInteger('expected_participants')->nullable()->after('guided_days');
        });
    }

    public function down(): void
    {
        Schema::table('trip_wishes', function (Blueprint $table) {
            $table->dropColumn(['guided_days', 'expected_participants']);
        });
    }
};
