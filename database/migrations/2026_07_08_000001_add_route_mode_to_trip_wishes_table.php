<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_wishes', function (Blueprint $table) {
            $table->string('route_mode')->nullable()->after('wished_date');
        });
    }

    public function down(): void
    {
        Schema::table('trip_wishes', function (Blueprint $table) {
            $table->dropColumn('route_mode');
        });
    }
};
