<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->onDelete('cascade');
            $table->string('risk_level'); // low, medium, high
            $table->date('generated_date');
            $table->json('morning_activities');
            $table->json('afternoon_activities');
            $table->json('evening_activities');
            $table->json('communication_tips');
            $table->json('games_activities');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_plans');
    }
};