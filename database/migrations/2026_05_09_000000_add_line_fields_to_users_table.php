<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('line_user_id')->nullable()->unique()->after('avatar');
            $table->string('line_display_name')->nullable()->after('line_user_id');
            $table->string('line_picture_url')->nullable()->after('line_display_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'line_user_id',
                'line_display_name',
                'line_picture_url',
            ]);
        });
    }
};
