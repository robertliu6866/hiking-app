<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('name');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');

            $table->integer('free_trial_quota')->default(2)->after('remember_token');
            $table->string('membership_status')->default('trial')->after('free_trial_quota');
            $table->timestamp('membership_paid_at')->nullable()->after('membership_status');
            $table->timestamp('membership_expires_at')->nullable()->after('membership_paid_at');
            $table->timestamp('profile_completed_at')->nullable()->after('membership_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'free_trial_quota',
                'membership_status',
                'membership_paid_at',
                'membership_expires_at',
                'profile_completed_at',
            ]);
        });
    }
};