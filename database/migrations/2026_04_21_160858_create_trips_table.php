<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title'); // 活動標題
            $table->string('mountain')->nullable(); // 山名 / 百岳名稱
            $table->string('category')->nullable(); // 單攻、郊山、縱走、越野跑
            $table->string('location')->nullable(); // 台北、台中、花蓮...
            $table->dateTime('departure_time')->nullable(); // 出發時間
            $table->string('meeting_point')->nullable(); // 集合地點

            $table->integer('price')->default(0); // 費用
            $table->integer('quota')->default(1); // 名額

            $table->text('description')->nullable(); // 活動描述

            $table->string('status')->default('open'); 
            // open = 報名中
            // full = 已滿
            // closed = 已截止
            // completed = 已成團 / 已完成

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};