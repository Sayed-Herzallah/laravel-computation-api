<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('operation_type', ['addition', 'subtraction', 'multiplication', 'division', 'mixed']);
            $table->integer('level');
            $table->integer('questions_count');  // عدد الأسئلة
            $table->integer('rows_count');        // عدد الصفوف
            $table->integer('num1')->nullable();
            $table->integer('num2')->nullable();
            $table->integer('num3')->nullable();
            $table->integer('student_answer')->nullable();   // إجابة الطالب
            $table->integer('correct_result')->nullable();   // الناتج الصحيح
            $table->boolean('is_correct')->nullable();       // صح أم غلط
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
