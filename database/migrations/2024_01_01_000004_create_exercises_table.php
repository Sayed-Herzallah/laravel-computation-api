<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            // نوع العملية: جمع، طرح، ضرب، قسمة، ميكس
            $table->enum('operation_type', ['addition', 'subtraction', 'multiplication', 'division', 'mixed']);
            $table->integer('level');           // المستوى 1-3
            $table->integer('rows_count');      // عدد الصفوف
            $table->integer('num1');            // الرقم الأول
            $table->integer('num2');            // الرقم الثاني
            $table->integer('num3')->nullable();// الرقم الثالث (للميكس)
            $table->integer('correct_result'); // الناتج الصحيح
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
