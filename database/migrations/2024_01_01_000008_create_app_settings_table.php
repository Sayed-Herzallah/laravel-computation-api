<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('operation_type', ['addition', 'subtraction', 'multiplication', 'division', 'mixed']);
            $table->integer('level');               // المستوى
            $table->integer('questions_count');     // عدد الأسئلة اللي هتظهر للطالب
            $table->integer('duration_minutes');    // الوقت المسموح به
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
