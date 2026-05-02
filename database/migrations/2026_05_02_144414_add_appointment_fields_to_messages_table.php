<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'is_appointment')) {
                $table->boolean('is_appointment')->default(false)->after('content');
            }
            if (!Schema::hasColumn('messages', 'appointment_id')) {
                $table->foreignId('appointment_id')->nullable()->after('is_appointment')->constrained('appointments')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'is_appointment')) {
                $table->dropColumn('is_appointment');
            }
            if (Schema::hasColumn('messages', 'appointment_id')) {
                $table->dropForeign(['appointment_id']);
                $table->dropColumn('appointment_id');
            }
        });
    }
};