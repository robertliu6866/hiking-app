<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('phone_verified_at');
            $table->string('gender')->nullable()->after('age');
            $table->string('hiking_experience')->nullable()->after('gender');
            $table->string('address')->nullable()->after('hiking_experience');
            $table->string('blood_type', 10)->nullable()->after('address');
            $table->string('emergency_contact_name')->nullable()->after('blood_type');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->text('bio')->nullable()->after('emergency_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'age',
                'gender',
                'hiking_experience',
                'address',
                'blood_type',
                'emergency_contact_name',
                'emergency_contact_phone',
                'bio',
            ]);
        });
    }
};