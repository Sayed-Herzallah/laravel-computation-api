<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('operation_type', ['addition', 'subtraction', 'multiplication', 'division', 'mixed']);
            $table->integer('level')->default(1);
            $table->integer('total_questions')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0); // نسبة النجاح %
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_levels');
    }
};
