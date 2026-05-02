<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behavior_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // who logged
            $table->integer('focus_level')->default(3);
            $table->string('mood')->default('neutral');
            $table->decimal('sleep_hours', 3, 1)->default(8);
            $table->integer('social_interaction')->default(3);
            $table->text('note')->nullable();
            $table->date('log_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_logs');
    }
};