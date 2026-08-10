<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('preferred_regions')->nullable()->after('hiking_experience');
            $table->json('available_days')->nullable()->after('preferred_regions');
            $table->json('transport_modes')->nullable()->after('available_days');
            $table->json('preferred_route_modes')->nullable()->after('transport_modes');
            $table->json('hiking_styles')->nullable()->after('preferred_route_modes');
            $table->unsignedTinyInteger('preferred_difficulty_min')->nullable()->after('hiking_styles');
            $table->unsignedTinyInteger('preferred_difficulty_max')->nullable()->after('preferred_difficulty_min');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_regions',
                'available_days',
                'transport_modes',
                'preferred_route_modes',
                'hiking_styles',
                'preferred_difficulty_min',
                'preferred_difficulty_max',
            ]);
        });
    }
};
