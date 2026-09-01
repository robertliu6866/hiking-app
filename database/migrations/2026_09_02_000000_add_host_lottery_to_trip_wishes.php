<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trip_wishes', function (Blueprint $table) {
            $table->foreignId('host_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('trip_wish_user', function (Blueprint $table) {
            $table->boolean('willing_to_host')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('trip_wish_user', function (Blueprint $table) {
            $table->dropColumn('willing_to_host');
        });

        Schema::table('trip_wishes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('host_user_id');
        });
    }
};
