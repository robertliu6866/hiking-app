<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_registrations', function (Blueprint $table) {
            $table->text('dietary_restrictions')->nullable()->change();
            $table->text('health_notes')->nullable()->change();
            $table->text('special_requests')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('trip_registrations', function (Blueprint $table) {
            $table->string('dietary_restrictions')->nullable()->change();
            $table->string('health_notes')->nullable()->change();
            $table->string('special_requests')->nullable()->change();
            $table->string('notes')->nullable()->change();
        });
    }
};
