<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('competition_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('operation_type', ['addition', 'subtraction', 'multiplication', 'division', 'mixed']);
            $table->integer('level');
            $table->integer('num1');
            $table->integer('num2');
            $table->integer('num3')->nullable();
            $table->integer('correct_result');
            $table->integer('student_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->integer('time_taken_seconds')->nullable(); // الوقت اللي اخده
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_sessions');
    }
};
