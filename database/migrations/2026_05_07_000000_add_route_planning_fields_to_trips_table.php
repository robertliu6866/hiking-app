<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->string('route_mode')->default('custom')->after('category');
            $table->unsignedTinyInteger('difficulty')->default(1)->after('route_mode');
            $table->decimal('distance_km', 6, 2)->nullable()->after('difficulty');
            $table->unsignedInteger('elevation_gain_m')->nullable()->after('distance_km');
            $table->decimal('estimated_hours', 5, 2)->nullable()->after('elevation_gain_m');
            $table->json('route_details')->nullable()->after('estimated_hours');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'route_mode',
                'difficulty',
                'distance_km',
                'elevation_gain_m',
                'estimated_hours',
                'route_details',
            ]);
        });
    }
};
