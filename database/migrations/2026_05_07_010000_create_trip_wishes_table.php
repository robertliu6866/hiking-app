<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_wishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mountain');
            $table->date('wished_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('trip_wish_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_wish_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['trip_wish_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_wish_user');
        Schema::dropIfExists('trip_wishes');
    }
};
